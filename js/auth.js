/**
 * 用户认证辅助函数
 */

/**
 * 检查用户是否已登录
 * 
 * @returns {boolean} 用户是否已登录
 */
function isUserLoggedIn() {
    return document.cookie.includes('userSession=') && document.cookie.includes('username=');
}

/**
 * 获取当前用户名
 * 
 * @returns {string|null} 用户名或null（如果未登录）
 */
function getCurrentUsername() {
    if (!isUserLoggedIn()) return null;
    
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        cookie = cookie.trim();
        if (cookie.startsWith('username=')) {
            return cookie.substring(9); // "username=".length = 9
        }
    }
    return null;
}

/**
 * 重定向到登录页面
 * 
 * @param {string} returnUrl 登录后返回的URL
 */
function redirectToLogin(returnUrl = '') {
    const loginUrl = returnUrl 
        ? `../signin/signin.html?returnUrl=${encodeURIComponent(returnUrl)}`
        : '../signin/signin.html';
    
    window.location.href = loginUrl;
}

/**
 * 要求用户登录才能访问页面
 * 如果用户未登录，将重定向到登录页面
 * 
 * @param {string} returnUrl 登录后返回的URL
 */
function requireLogin(returnUrl = '') {
    if (!isUserLoggedIn()) {
        redirectToLogin(returnUrl || window.location.href);
    }
}

/**
 * 检查会话状态并根据需要执行操作
 * 
 * @param {Function} loggedInCallback 用户已登录时调用的回调函数
 * @param {boolean} requireAuth 是否要求登录（如果为false，未登录时不会重定向）
 * @param {string} returnUrl 登录后返回的URL
 */
function checkSession(loggedInCallback = null, requireAuth = true, returnUrl = '') {
    if (isUserLoggedIn()) {
        // 用户已登录
        if (loggedInCallback && typeof loggedInCallback === 'function') {
            const username = getCurrentUsername();
            loggedInCallback(username);
        }
    } else if (requireAuth) {
        // 用户未登录，需要登录
        redirectToLogin(returnUrl || window.location.href);
    }
}

/**
 * 退出登录
 * 
 * @param {boolean} redirect 是否重定向到登录页面
 */
function logout(redirect = true) {
    // 清除所有相关cookie
    document.cookie = 'userSession=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    document.cookie = 'username=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    
    // 清除sessionStorage
    sessionStorage.removeItem('currentUser');
    
    if (redirect) {
        window.location.href = '../signin/signin.html';
    }
}

// 页面加载时自动执行登录检查
document.addEventListener('DOMContentLoaded', function() {
    // 检查当前页面是否为登录页面
    const isLoginPage = window.location.href.includes('signin.html');
    
    // 如果不是登录页面，检查用户是否已登录
    if (!isLoginPage) {
        // 默认不要求登录，让各页面自行控制
        // 各页面应该在其脚本中调用 requireLogin() 或 checkSession()
    }
}); 