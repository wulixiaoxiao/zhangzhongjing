<?php
/**
 * 中医智能问诊系统 - 入口文件
 * 
 * 所有请求都通过此文件进行路由分发
 */

// 启动输出缓冲（防止意外输出影响header和session）
ob_start();

// 定义应用根目录
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('STORAGE_PATH', ROOT_PATH . 'storage/');

// 检测环境（可以通过 .env 文件或服务器变量设置）
$environment = getenv('APP_ENV') ?: 'production';

// 根据环境设置错误显示
if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'storage/logs/php_errors.log');
}

// 启动会话（在任何输出之前）
if (session_status() === PHP_SESSION_NONE) {
    // 设置会话保存路径
    $sessionPath = ROOT_PATH . 'storage/sessions';
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    
    // 生产环境的会话安全设置
    if ($environment === 'production') {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        // 如果使用HTTPS，启用secure cookie
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', 1);
        }
    }
    
    session_start();
}

// 加载自动加载器
require_once APP_PATH . 'Core/Autoloader.php';

// 初始化自动加载
$autoloader = new \App\Core\Autoloader();
$autoloader->register();

// 加载配置
\App\Core\Config::load();

// 初始化路由
$router = new \App\Core\Router();

// 定义路由规则
require_once CONFIG_PATH . 'routes.php';

// 分发请求
$router->dispatch();

// 刷新输出缓冲
ob_end_flush(); 