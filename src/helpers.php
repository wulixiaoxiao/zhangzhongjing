<?php
/**
 * 应用辅助函数
 */

use App\Core\Config;

if (!function_exists('config')) {
    /**
     * 获取/设置配置值
     * 
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return Config::all();
        }
        
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Config::set($k, $v);
            }
            return null;
        }
        
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return Config::env($key, $default);
    }
}

if (!function_exists('app_path')) {
    /**
     * 获取应用路径
     * 
     * @param string $path
     * @return string
     */
    function app_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * 获取配置文件路径
     * 
     * @param string $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return app_path('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取存储路径
     * 
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return app_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取公共目录路径
     * 
     * @param string $path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return app_path('public' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('dd')) {
    /**
     * 调试输出并终止
     * 
     * @param mixed ...$vars
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * 调试输出
     * 
     * @param mixed ...$vars
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
} 