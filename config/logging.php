<?php
/**
 * 日志配置
 */

return [
    // 默认日志通道
    'default' => env('LOG_CHANNEL', 'file'),
    
    // 日志通道配置
    'channels' => [
        'file' => [
            'driver' => 'file',
            'path' => env('LOG_PATH', storage_path('logs/app.log')),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7, // 保留天数
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => env('LOG_PATH', storage_path('logs')),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],
        
        'error' => [
            'driver' => 'file',
            'path' => storage_path('logs/error.log'),
            'level' => 'error',
        ],
        
        'system' => [
            'driver' => 'database',
            'table' => 'system_logs',
            'level' => 'info',
        ]
    ],
    
    // 日志级别
    'levels' => [
        'emergency' => 800,
        'alert' => 700,
        'critical' => 600,
        'error' => 500,
        'warning' => 400,
        'notice' => 300,
        'info' => 200,
        'debug' => 100,
    ]
]; 