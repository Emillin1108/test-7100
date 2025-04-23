<?php
/**
 * 页面认证检查脚本
 * 
 * 用于在PHP页面中检查用户是否已登录
 * 在需要认证的页面顶部引入此文件即可
 */

// 检查用户是否已登录
function isUserLoggedIn() {
    return isset($_COOKIE['userSession']) && isset($_COOKIE['username']);
}

// 获取当前登录用户名
function getCurrentUsername() {
    return isUserLoggedIn() ? $_COOKIE['username'] : null;
}

// 重定向到登录页面
function redirectToLogin($returnUrl = '') {
    $loginUrl = 'signin/signin.html';
    if (!empty($returnUrl)) {
        $loginUrl .= '?returnUrl=' . urlencode($returnUrl);
    }
    
    header('Location: ' . $loginUrl);
    exit();
}

// 执行登录检查
if (!isUserLoggedIn()) {
    // 获取当前页面URL
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                  '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // 重定向到登录页
    redirectToLogin($currentUrl);
}

// 如果用户已登录，获取用户名供页面使用
$currentUsername = getCurrentUsername();
?> 