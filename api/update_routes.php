<?php
/**
 * Update Routes API
 * 
 * 处理上传的GPX文件并更新路线数据库
 */

// 设置响应头
header('Content-Type: application/json');

// 允许跨域请求 (开发环境，生产环境需调整)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => '无效的请求方法。仅允许POST请求。'
    ]);
    exit();
}

// 验证用户是否已登录
if (!isset($_COOKIE['userSession']) || !isset($_COOKIE['username'])) {
    echo json_encode([
        'success' => false,
        'error' => '需要用户登录。'
    ]);
    exit();
}

// 检查必要的表单字段
if (!isset($_POST['district']) || 
    !isset($_POST['subDistrict']) || 
    !isset($_POST['routeName']) || 
    !isset($_FILES['gpxFile'])) {
    
    echo json_encode([
        'success' => false,
        'error' => '缺少必要的表单字段。'
    ]);
    exit();
}

$district = $_POST['district'];
$subDistrict = $_POST['subDistrict'];
$routeName = $_POST['routeName'];
$file = $_FILES['gpxFile'];

// 验证上传的文件
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'error' => '文件上传失败: ' . getUploadErrorMessage($file['error'])
    ]);
    exit();
}

// 检查文件类型
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'gpx') {
    echo json_encode([
        'success' => false,
        'error' => '只接受GPX格式的文件。'
    ]);
    exit();
}

try {
    // 读取GPX文件内容
    $gpxContent = file_get_contents($file['tmp_name']);
    
    // 解析GPX数据
    $coordinates = parseGPXCoordinates($gpxContent);
    
    if (count($coordinates) < 2) {
        echo json_encode([
            'success' => false,
            'error' => '路线必须包含至少2个有效坐标点。'
        ]);
        exit();
    }
    
    // 读取现有路线数据
    $routesFile = '../data/routes.json';
    $routesData = [];
    
    if (file_exists($routesFile)) {
        $routesJson = file_get_contents($routesFile);
        $routesData = json_decode($routesJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('解析现有路线数据时出错: ' . json_last_error_msg());
        }
    }
    
    // 确保数据结构存在
    if (!isset($routesData[$district])) {
        $routesData[$district] = [
            'subDistricts' => []
        ];
    }
    
    if (!isset($routesData[$district]['subDistricts'][$subDistrict])) {
        $routesData[$district]['subDistricts'][$subDistrict] = [
            'routes' => []
        ];
    }
    
    // 添加或更新路线
    $routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName] = $coordinates;
    
    // 保存更新后的路线数据
    if (!file_put_contents($routesFile, json_encode($routesData, JSON_PRETTY_PRINT))) {
        throw new Exception('保存路线数据时出错');
    }
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'message' => '路线上传成功。'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '服务器错误: ' . $e->getMessage()
    ]);
}

/**
 * 获取上传错误消息
 * 
 * @param int $errorCode 错误代码
 * @return string 错误消息
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return '上传的文件超过了php.ini中upload_max_filesize指令的限制。';
        case UPLOAD_ERR_FORM_SIZE:
            return '上传的文件超过了HTML表单中MAX_FILE_SIZE指令的限制。';
        case UPLOAD_ERR_PARTIAL:
            return '上传的文件只有部分被上传。';
        case UPLOAD_ERR_NO_FILE:
            return '没有文件被上传。';
        case UPLOAD_ERR_NO_TMP_DIR:
            return '缺少临时文件夹。';
        case UPLOAD_ERR_CANT_WRITE:
            return '无法写入文件到磁盘。';
        case UPLOAD_ERR_EXTENSION:
            return '文件上传因扩展程序而停止。';
        default:
            return '未知上传错误。';
    }
}

/**
 * 解析GPX文件中的坐标
 * 
 * @param string $gpxContent GPX文件内容
 * @return array 坐标数组
 */
function parseGPXCoordinates($gpxContent) {
    // 创建XML解析器
    $xml = new SimpleXMLElement($gpxContent);
    
    // 注册GPX命名空间
    $xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');
    
    // 获取所有轨迹点
    $trackPoints = $xml->xpath('//gpx:trkpt');
    
    // 如果没有找到轨迹点，尝试获取路线点
    if (empty($trackPoints)) {
        $trackPoints = $xml->xpath('//gpx:rtept');
    }
    
    // 如果仍然没有找到点，尝试不使用命名空间
    if (empty($trackPoints)) {
        $trackPoints = $xml->xpath('//trkpt') ?: $xml->xpath('//rtept');
    }
    
    $coordinates = [];
    
    // 提取坐标
    foreach ($trackPoints as $point) {
        $lat = (float)$point['lat'];
        $lon = (float)$point['lon'];
        
        // 验证坐标有效性
        if (is_numeric($lat) && is_numeric($lon) && $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
            $coordinates[] = [$lat, $lon]; // 注意：存储格式为 [lat, lon]
        }
    }
    
    return $coordinates;
}
?> 