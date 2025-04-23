<?php
/**
 * Delete Route API
 * 
 * Deletes a specific route for a logged-in user.
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
$requestData = json_decode($inputData, true);

// Validate route ID
if (!isset($requestData['routeId']) || empty($requestData['routeId'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Route ID is required.'
    ]);
    exit();
}

$routeId = $requestData['routeId'];

try {
    $userRoutesFile = '../data/user_routes/' . $username . '_routes.json';
    
    // Check if the user has any saved routes
    if (!file_exists($userRoutesFile)) {
        echo json_encode([
            'success' => false,
            'error' => 'No routes found for this user.'
        ]);
        exit();
    }
    
    // Read user routes
    $routesData = file_get_contents($userRoutesFile);
    $userRoutes = json_decode($routesData, true);
    
    // Validate routes data structure
    if (!is_array($userRoutes) || !isset($userRoutes['routes']) || !is_array($userRoutes['routes'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid routes data structure.'
        ]);
        exit();
    }
    
    // Find and remove the route with the matching ID
    $routeFound = false;
    foreach ($userRoutes['routes'] as $key => $route) {
        if (isset($route['id']) && $route['id'] === $routeId) {
            unset($userRoutes['routes'][$key]);
            $routeFound = true;
            break;
        }
    }
    
    if (!$routeFound) {
        echo json_encode([
            'success' => false,
            'error' => 'Route not found.'
        ]);
        exit();
    }
    
    // Reindex the array
    $userRoutes['routes'] = array_values($userRoutes['routes']);
    
    // Save updated routes back to file
    file_put_contents($userRoutesFile, json_encode($userRoutes));
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Route deleted successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 