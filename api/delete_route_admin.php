<?php
/**
 * Delete Route Admin API
 * 
 * 删除routes.json中的路线数据
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

// 获取请求体数据
$requestData = json_decode(file_get_contents('php://input'), true);

// 验证参数
if (!isset($requestData['district']) || 
    !isset($requestData['subDistrict']) || 
    !isset($requestData['routeName'])) {
    
    echo json_encode([
        'success' => false,
        'error' => '缺少必要的参数。'
    ]);
    exit();
}

$district = $requestData['district'];
$subDistrict = $requestData['subDistrict'];
$routeName = $requestData['routeName'];

try {
    // 读取路线数据
    $routesFile = '../data/routes.json';
    
    if (!file_exists($routesFile)) {
        echo json_encode([
            'success' => false,
            'error' => '路线数据文件不存在。'
        ]);
        exit();
    }
    
    $routesJson = file_get_contents($routesFile);
    $routesData = json_decode($routesJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('解析路线数据时出错: ' . json_last_error_msg());
    }
    
    // 检查路线是否存在
    if (!isset($routesData[$district]) || 
        !isset($routesData[$district]['subDistricts'][$subDistrict]) || 
        !isset($routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName])) {
        
        echo json_encode([
            'success' => false,
            'error' => '指定的路线不存在。'
        ]);
        exit();
    }
    
    // 删除路线
    unset($routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName]);
    
    // 清理空集合
    if (empty($routesData[$district]['subDistricts'][$subDistrict]['routes'])) {
        unset($routesData[$district]['subDistricts'][$subDistrict]);
        
        if (empty($routesData[$district]['subDistricts'])) {
            unset($routesData[$district]);
        }
    }
    
    // 保存更新后的数据
    if (!file_put_contents($routesFile, json_encode($routesData, JSON_PRETTY_PRINT))) {
        throw new Exception('保存路线数据时出错');
    }
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'message' => '路线删除成功。'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '服务器错误: ' . $e->getMessage()
    ]);
}
?> 