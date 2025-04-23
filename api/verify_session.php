<?php
/**
 * Session Verification API
 * 
 * Verifies if a user session is valid.
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

// Check for session cookies
if (!isset($_COOKIE['userSession']) || !isset($_COOKIE['username'])) {
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'error' => 'No valid session found'
    ]);
    exit();
}

$username = $_COOKIE['username'];
$sessionToken = $_COOKIE['userSession'];

// In a real application, you would validate the session token against a database
// For this simple implementation, we'll just check if the user exists

try {
    // Check if user exists in user database
    $usersFile = '../data/users.json';
    
    if (!file_exists($usersFile)) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'error' => 'User database not found'
        ]);
        exit();
    }
    
    $users = json_decode(file_get_contents($usersFile), true);
    
    // Check if username exists
    $userExists = false;
    foreach ($users['users'] as $user) {
        if ($user['username'] === $username) {
            $userExists = true;
            break;
        }
    }
    
    if (!$userExists) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'error' => 'User not found'
        ]);
        exit();
    }
    
    // In a real application, we would verify the session token here
    // For this simple implementation, we'll just assume it's valid if the user exists
    
    // Return success response
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'username' => $username
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 