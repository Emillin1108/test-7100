<?php
/**
 * Get Completed Routes API
 * 
 * 获取用户已完成（点亮）的路线
 */

// 设置响应头
header('Content-Type: application/json');

// 允许跨域请求 (开发环境，生产环境需调整)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'error' => '无效的请求方法。仅允许GET请求。'
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

try {
    // 用户完成路线文件路径
    $userCompletedRoutesFile = '../data/user_completed_routes/' . $username . '_completed_routes.json';
    
    // 检查文件是否存在
    if (!file_exists($userCompletedRoutesFile)) {
        // 用户尚未有已完成的路线
        echo json_encode([
            'success' => true,
            'routes' => []
        ]);
        exit();
    }
    
    // 读取路线数据
    $routesData = file_get_contents($userCompletedRoutesFile);
    $completedRoutes = json_decode($routesData, true);
    
    // 验证数据格式
    if (!is_array($completedRoutes)) {
        echo json_encode([
            'success' => true,
            'routes' => []
        ]);
        exit();
    }
    
    // 返回路线数据
    echo json_encode([
        'success' => true,
        'routes' => $completedRoutes
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '服务器错误: ' . $e->getMessage()
    ]);
}
?> 