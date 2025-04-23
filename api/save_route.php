<?php
/**
 * Save Route API
 * 
 * Saves a new route for a logged-in user.
 */

// Set headers
header('Content-Type: application/json');

// Allow CORS (adjust in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Only POST is allowed.'
    ]);
    exit();
}

// Verify session (user is logged in)
if (!isset($_COOKIE['userSession']) || !isset($_COOKIE['username'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required. Please log in.'
    ]);
    exit();
}

// Get username
$username = $_COOKIE['username'];

// Get input data (from POST JSON)
$inputData = file_get_contents('php://input');
$routeData = json_decode($inputData, true);

// Validate route data
if (!isset($routeData['name']) || empty($routeData['name'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Route name is required.'
    ]);
    exit();
}

if (!isset($routeData['coordinates']) || !is_array($routeData['coordinates']) || count($routeData['coordinates']) < 2) {
    echo json_encode([
        'success' => false,
        'error' => 'Route must contain at least 2 valid coordinates.'
    ]);
    exit();
}

try {
    // Generate unique route ID
    $routeId = uniqid('route_', true);
    
    // Add metadata to the route
    $route = [
        'id' => $routeId,
        'name' => $routeData['name'],
        'coordinates' => $routeData['coordinates'],
        'color' => $routeData['color'] ?? '#' . dechex(rand(0, 16777215)), // Random color if not provided
        'created' => date('Y-m-d H:i:s')
    ];
    
    // Create data directory if it doesn't exist
    $dataDir = '../data/user_routes';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $userRoutesFile = $dataDir . '/' . $username . '_routes.json';
    $userRoutes = ['routes' => []];
    
    // Load existing routes if file exists
    if (file_exists($userRoutesFile)) {
        $existingData = file_get_contents($userRoutesFile);
        $decodedData = json_decode($existingData, true);
        
        // Validate existing data structure
        if (is_array($decodedData) && isset($decodedData['routes']) && is_array($decodedData['routes'])) {
            $userRoutes = $decodedData;
        }
    }
    
    // Add the new route
    $userRoutes['routes'][] = $route;
    
    // Save routes to file
    file_put_contents($userRoutesFile, json_encode($userRoutes));
    
    // Return success with the new route ID
    echo json_encode([
        'success' => true,
        'routeId' => $routeId,
        'message' => 'Route saved successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 