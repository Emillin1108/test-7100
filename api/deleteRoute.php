<?php
// 处理 OPTIONS 请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400'); // 24 hours
    exit(0);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => '无效的请求数据']);
    exit;
}

// 验证必要字段
if (!isset($data['district']) || !isset($data['subDistrict']) || !isset($data['routeName'])) {
    echo json_encode(['success' => false, 'error' => '缺少必要参数']);
    exit;
}

$district = $data['district'];
$subDistrict = $data['subDistrict'];
$routeName = $data['routeName'];

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

// 检查路线是否存在
if (!isset($routesData[$district]) || 
    !isset($routesData[$district]['subDistricts'][$subDistrict]) || 
    !isset($routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName])) {
    echo json_encode(['success' => false, 'error' => '路线不存在']);
    exit;
}

// 删除路线
unset($routesData[$district]['subDistricts'][$subDistrict]['routes'][$routeName]);

// 如果该分区没有路线了，删除分区
if (empty($routesData[$district]['subDistricts'][$subDistrict]['routes'])) {
    unset($routesData[$district]['subDistricts'][$subDistrict]);
    
    // 如果该地区没有分区了，删除地区
    if (empty($routesData[$district]['subDistricts'])) {
        unset($routesData[$district]);
    }
}

// 保存更新后的数据
if (file_put_contents($routesFile, json_encode($routesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => '无法保存更新后的数据']);
} 