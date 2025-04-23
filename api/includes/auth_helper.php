<?php
/**
 * 认证辅助函数
 * 
 * 包含验证用户登录和会话的通用函数
 */

/**
 * 检查用户是否已登录
 * 
 * @return bool 用户是否已登录
 */
function isUserLoggedIn() {
    return isset($_COOKIE['userSession']) && isset($_COOKIE['username']);
}

/**
 * 获取当前登录用户的用户名
 * 
 * @return string|null 用户名或null（如果未登录）
 */
function getCurrentUsername() {
    return isUserLoggedIn() ? $_COOKIE['username'] : null;
}

/**
 * 重定向到登录页面
 * 
 * @param string $returnUrl 登录后返回的URL
 */
function redirectToLogin($returnUrl = '') {
    // 如果提供了返回URL，将其添加为查询参数
    $loginUrl = '../signin/signin.html';
    if (!empty($returnUrl)) {
        $loginUrl .= '?returnUrl=' . urlencode($returnUrl);
    }
    
    // 重定向到登录页面
    header('Location: ' . $loginUrl);
    exit();
}

/**
 * 要求用户登录才能访问页面
 * 
 * 如果用户未登录，将重定向到登录页面
 * 
 * @param string $returnUrl 登录后返回的URL
 * @return void
 */
function requireLogin($returnUrl = '') {
    if (!isUserLoggedIn()) {
        redirectToLogin($returnUrl);
    }
}
?> 