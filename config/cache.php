<?php
/**
 * 缓存配置
 */

return [
    // 默认缓存驱动
    'default' => env('CACHE_DRIVER', 'file'),
    
    // 缓存存储配置
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_PATH', storage_path('cache')),
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],
    
    // 缓存前缀
    'prefix' => env('CACHE_PREFIX', 'yisheng_cache'),
    
    // 默认缓存时间（秒）
    'ttl' => 3600,
]; 