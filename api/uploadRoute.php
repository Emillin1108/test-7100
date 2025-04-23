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

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// 验证必要字段
if (!isset($data['district']) || !isset($data['subDistrict']) || !isset($data['routeName']) || !isset($data['gpxData'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// 解析GPX数据
$gpxData = $data['gpxData'];
$xml = simplexml_load_string($gpxData);

if (!$xml) {
    echo json_encode(['success' => false, 'error' => 'Invalid GPX data']);
    exit;
}

// 提取坐标点
$coordinates = [];
foreach ($xml->trk->trkseg->trkpt as $point) {
    $coordinates[] = [
        'lat' => (float)$point['lat'],
        'lng' => (float)$point['lon']
    ];
}

// 准备路线数据
$routeData = [
    'district' => $data['district'],
    'subDistrict' => $data['subDistrict'],
    'routeName' => $data['routeName'],
    'coordinates' => $coordinates
];

// 读取现有的路线数据
$routesFile = '../data/routes.json';
$routes = [];
if (file_exists($routesFile)) {
    $routes = json_decode(file_get_contents($routesFile), true);
}

// 更新路线数据
if (!isset($routes[$data['district']])) {
    $routes[$data['district']] = [
        'name' => $data['district'],
        'subDistricts' => []
    ];
}

if (!isset($routes[$data['district']]['subDistricts'][$data['subDistrict']])) {
    $routes[$data['district']]['subDistricts'][$data['subDistrict']] = [
        'name' => $data['subDistrict'],
        'routes' => []
    ];
}

$routes[$data['district']]['subDistricts'][$data['subDistrict']]['routes'][$data['routeName']] = $coordinates;

// 保存更新后的数据
if (file_put_contents($routesFile, json_encode($routes, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save route data']);
} 