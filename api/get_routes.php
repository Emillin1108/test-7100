<?php
/**
 * Get Routes API
 * 
 * Retrieves all routes for a logged-in user.
 */

// Set headers
header('Content-Type: application/json');

// Allow CORS (adjust in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if this is a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Only GET is allowed.'
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

$username = $_COOKIE['username'];

try {
    $userRoutesFile = '../data/user_routes/' . $username . '_routes.json';
    
    // Check if user has any routes
    if (!file_exists($userRoutesFile)) {
        echo json_encode([
            'success' => true,
            'routes' => []
        ]);
        exit();
    }
    
    // Read the routes file
    $routesData = file_get_contents($userRoutesFile);
    $userRoutes = json_decode($routesData, true);
    
    // Validate the structure of the data
    if (!is_array($userRoutes) || !isset($userRoutes['routes']) || !is_array($userRoutes['routes'])) {
        echo json_encode([
            'success' => true,
            'routes' => []
        ]);
        exit();
    }
    
    // Return the routes
    echo json_encode([
        'success' => true,
        'routes' => $userRoutes['routes']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 