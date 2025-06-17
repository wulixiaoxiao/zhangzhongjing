<?php
/**
 * 中医智能问诊系统 - 入口文件
 * 
 * 所有请求都通过此文件进行路由分发
 */

// 定义应用根目录
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('STORAGE_PATH', ROOT_PATH . 'storage/');

// 开发环境错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载自动加载器
require_once APP_PATH . 'Core/Autoloader.php';

// 初始化自动加载
$autoloader = new \App\Core\Autoloader();
$autoloader->register();

// 加载配置
\App\Core\Config::load();

// 启动会话
session_start();

// 初始化路由
$router = new \App\Core\Router();

// 定义路由规则
require_once CONFIG_PATH . 'routes.php';

// 分发请求
$router->dispatch(); 