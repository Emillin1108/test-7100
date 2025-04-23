<?php
/**
 * Login API
 * 
 * Handles user authentication and session creation.
 * This is a simple implementation without passwords.
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

// Get input data
$data = json_decode(file_get_contents('php://input'), true);

// Validate username
if (!isset($data['username']) || empty(trim($data['username']))) {
    echo json_encode([
        'success' => false,
        'error' => 'Username is required.'
    ]);
    exit();
}

$username = trim($data['username']);

// Simple validation - username should be alphanumeric and reasonable length
if (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,30}$/u', $username)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid username format. Use only letters, numbers, underscores, or Chinese characters.'
    ]);
    exit();
}

try {
    // Check if user exists in user database
    $usersFile = '../data/users.json';
    $users = [];
    
    // Create users file if it doesn't exist
    if (!file_exists($usersFile)) {
        file_put_contents($usersFile, json_encode([
            'users' => []
        ]));
    } else {
        $users = json_decode(file_get_contents($usersFile), true);
    }
    
    // Check if user exists
    $userExists = false;
    foreach ($users['users'] as $user) {
        if ($user['username'] === $username) {
            $userExists = true;
            break;
        }
    }
    
    // If user doesn't exist, add them to the database
    if (!$userExists) {
        $users['users'][] = [
            'username' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        
        // Also create an empty completed routes file for the user
        $userRoutesFile = '../data/user_routes/' . $username . '.json';
        if (!is_dir('../data/user_routes')) {
            mkdir('../data/user_routes', 0755, true);
        }
        
        file_put_contents($userRoutesFile, json_encode([
            'completedRoutes' => []
        ], JSON_PRETTY_PRINT));
    } else {
        // Update last login time for existing user
        for ($i = 0; $i < count($users['users']); $i++) {
            if ($users['users'][$i]['username'] === $username) {
                $users['users'][$i]['last_login'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }
    
    // Generate a simple session token
    $sessionToken = bin2hex(random_bytes(32));
    
    // In a real app, you would store this in a secure session database
    // For this simple implementation, we'll just set a cookie
    
    // Set session cookie (30 days expiry)
    $expiryTime = time() + 60 * 60 * 24 * 30;
    setcookie('userSession', $sessionToken, $expiryTime, '/', '', false, true);
    setcookie('username', $username, $expiryTime, '/', '', false, false);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'username' => $username
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 