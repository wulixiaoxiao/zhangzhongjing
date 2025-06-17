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
    
    // 安全配置
    'encryption_key' => env('APP_KEY'),
    'api_key' => env('APP_API_KEY'),
    
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
        'api_key' => env('DEEPSEEK_API_KEY', env('OPENROUTER_API_KEY')),
        'api_url' => env('DEEPSEEK_API_URL', 'https://openrouter.ai/api/v1/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek/deepseek-chat'),
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
    ],
    
    // 安全策略配置
    'security' => [
        // CSRF 保护
        'csrf' => [
            'enabled' => true,
            'token_lifetime' => 7200, // 2小时
            'exclude_routes' => [
                'api/*', // API 路由使用 API Key 验证
            ]
        ],
        
        // 密码策略
        'password' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => false,
        ],
        
        // 速率限制
        'rate_limit' => [
            'api' => [
                'attempts' => 60,
                'decay' => 60, // 秒
            ],
            'login' => [
                'attempts' => 5,
                'decay' => 300, // 5分钟
            ],
        ],
        
        // 内容安全策略
        'csp' => [
            'enabled' => true,
            'policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;"
        ],
        
        // 安全头
        'headers' => [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Feature-Policy' => "camera 'none'; microphone 'none'; geolocation 'self';"
        ]
    ]
]; 