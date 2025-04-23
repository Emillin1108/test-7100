<?php
/**
 * Save Completed Route API
 * 
 * 保存用户已完成（点亮）的路线
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

// 获取用户名
$username = $_COOKIE['username'];

// 获取输入数据
$inputData = file_get_contents('php://input');
$routeData = json_decode($inputData, true);

// 验证路线数据
if (!isset($routeData['name']) || empty($routeData['name'])) {
    echo json_encode([
        'success' => false,
        'error' => '路线名称为必填项。'
    ]);
    exit();
}

if (!isset($routeData['points']) || !is_array($routeData['points']) || count($routeData['points']) < 2) {
    echo json_encode([
        'success' => false,
        'error' => '路线必须包含至少2个有效坐标点。'
    ]);
    exit();
}

try {
    // 创建数据目录 (如果不存在)
    $dataDir = '../data/user_completed_routes';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // 用户完成路线文件路径
    $userCompletedRoutesFile = $dataDir . '/' . $username . '_completed_routes.json';
    
    // 初始化用户完成路线数据
    $completedRoutes = [];
    
    // 如果文件存在，读取现有数据
    if (file_exists($userCompletedRoutesFile)) {
        $existingData = file_get_contents($userCompletedRoutesFile);
        $decodedData = json_decode($existingData, true);
        
        // 验证数据结构
        if (is_array($decodedData)) {
            $completedRoutes = $decodedData;
        }
    }
    
    // 检查路线是否已存在 (基于名称检查)
    $routeExists = false;
    foreach ($completedRoutes as $index => $existingRoute) {
        if ($existingRoute['name'] === $routeData['name']) {
            // 更新现有路线
            $completedRoutes[$index] = $routeData;
            $routeExists = true;
            break;
        }
    }
    
    // 如果路线不存在，添加新路线
    if (!$routeExists) {
        $completedRoutes[] = $routeData;
    }
    
    // 保存路线数据到文件
    file_put_contents($userCompletedRoutesFile, json_encode($completedRoutes));
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'message' => '路线保存成功。',
        'routeCount' => count($completedRoutes)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '服务器错误: ' . $e->getMessage()
    ]);
}
?> 