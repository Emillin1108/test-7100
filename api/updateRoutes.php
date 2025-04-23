<?php
// 处理 OPTIONS 请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400'); // 24 hours
    exit(0);
}

// 设置响应头
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 记录请求方法
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => '不支持的请求方法']);
    exit;
}

// 检查必要的表单字段
if (!isset($_POST['district']) || !isset($_POST['subDistrict']) || !isset($_POST['routeName']) || !isset($_FILES['gpxFile'])) {
    echo json_encode(['success' => false, 'error' => '缺少必要参数']);
    exit;
}

$district = $_POST['district'];
$subDistrict = $_POST['subDistrict'];
$routeName = $_POST['routeName'];
$gpxFile = $_FILES['gpxFile'];

// 验证文件上传
if ($gpxFile['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => '文件上传失败']);
    exit;
}

// 读取GPX文件内容
$gpxContent = file_get_contents($gpxFile['tmp_name']);
if (!$gpxContent) {
    echo json_encode(['success' => false, 'error' => '无法读取GPX文件']);
    exit;
}

// 解析GPX文件
$xml = simplexml_load_string($gpxContent);
if (!$xml) {
    echo json_encode(['success' => false, 'error' => '无效的GPX文件格式']);
    exit;
}

// 提取坐标点
$coordinates = [];
if (isset($xml->trk->trkseg)) {
    foreach ($xml->trk->trkseg->trkpt as $point) {
        $coordinates[] = [
            'lat' => (float)$point['lat'],
            'lng' => (float)$point['lon']
        ];
    }
}

// 读取现有路线数据
$routesFile = __DIR__ . '/../data/routes.json';
if (!file_exists($routesFile)) {
    echo json_encode(['success' => false, 'error' => '路线数据文件不存在']);
    exit;
}

$routesData = json_decode(file_get_contents($routesFile), true);
if (!$routesData) {
    echo json_encode(['success' => false, 'error' => '无法读取路线数据']);
    exit;
}

// 确保district和subDistricts存在
if (!isset($routesData[$district])) {
    $routesData[$district] = [
        'name' => $district,
        'subDistricts' => []
    ];
}

if (!isset($routesData[$district]['subDistricts'][$subDistrict])) {
    $routesData[$district]['subDistricts'][$subDistrict] = [
        'name' => $subDistrict,
        'routes' => []
    ];
}

// 添加新路线
$routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName] = [
    'name' => $routeName,
    'coordinates' => $coordinates
];

// 保存更新后的数据
if (file_put_contents($routesFile, json_encode($routesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => '无法保存更新后的数据']);
} 