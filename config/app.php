<?php
/**
 * 应用配置文件
 */

return [
    // 应用基本信息
    'name' => '中医智能问诊系统',
    'version' => '1.0.0',
    'timezone' => 'Asia/Shanghai',
    'charset' => 'UTF-8',
    
    // 调试模式
    'debug' => env('APP_DEBUG', false),
    
    // 应用 URL
    'url' => env('APP_URL', 'http://localhost'),
    
    // 数据库配置
    'database' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'yisheng_db'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => 'utf8mb4_unicode_ci'
    ],
    
    // DeepSeek API 配置
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
        'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 2000)
    ],
    
    // 会话配置
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'secure' => env('SESSION_SECURE_COOKIE', false),
        'httponly' => true,
        'samesite' => env('SESSION_SAME_SITE', 'Lax')
    ],
    
    // 上传配置
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']
    ],
    
    // 分页配置
    'pagination' => [
        'per_page' => 20
    ]
]; 