<?php
/**
 * 生产环境配置
 */

return [
    // 错误显示设置
    'display_errors' => false,
    'error_reporting' => E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT,
    
    // 日志设置
    'log_errors' => true,
    'error_log' => STORAGE_PATH . 'logs/php_errors.log',
    
    // 会话设置
    'session' => [
        'save_path' => STORAGE_PATH . 'sessions',
        'cookie_httponly' => true,
        'cookie_secure' => true, // 如果使用HTTPS
        'use_strict_mode' => true,
    ],
    
    // 其他安全设置
    'expose_php' => false,
]; 