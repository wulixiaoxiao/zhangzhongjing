<?php

namespace App\Core;

/**
 * 配置管理类
 */
class Config
{
    /**
     * 配置数据存储
     */
    private static $config = [];
    
    /**
     * 环境变量存储
     */
    private static $env = [];

    /**
     * 加载配置文件
     */
    public static function load()
    {
        // 加载环境变量文件
        self::loadEnv();
        
        // 加载所有配置文件
        self::loadAllConfigs();
    }

    /**
     * 加载 .env 文件
     */
    private static function loadEnv()
    {
        $envFile = ROOT_PATH . '.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // 跳过注释行
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // 解析键值对
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // 移除引号
                    $value = trim($value, '"\'');
                    
                    // 处理布尔值
                    if (strtolower($value) === 'true') {
                        $value = true;
                    } elseif (strtolower($value) === 'false') {
                        $value = false;
                    }
                    
                    // 设置环境变量
                    self::$env[$key] = $value;
                    
                    // 检查 putenv 是否可用
                    if (function_exists('putenv') && !in_array('putenv', explode(',', ini_get('disable_functions')))) {
                        @putenv("$key=$value");
                    }
                    
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    /**
     * 加载所有配置文件
     */
    private static function loadAllConfigs()
    {
        $configFiles = glob(CONFIG_PATH . '*.php');
        
        foreach ($configFiles as $file) {
            $name = basename($file, '.php');
            // 跳过 env.example 和 routes
            if ($name === 'env' || $name === 'routes') {
                continue;
            }
            
            $config = require $file;
            
            if (is_array($config)) {
                self::$config[$name] = $config;
            }
        }
    }

    /**
     * 获取配置值
     * 
     * @param string $key 配置键，支持点号分隔的多级键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // 首先检查环境变量
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }
        
        // 然后检查配置数组
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }

    /**
     * 设置配置值
     * 
     * @param string $key 配置键
     * @param mixed $value 配置值
     */
    public static function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }
    
    /**
     * 获取环境变量
     * 
     * @param string $key 环境变量键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        if (array_key_exists($key, self::$env)) {
            return self::$env[$key];
        }
        
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        // 处理布尔值
        if (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        }
        
        return $value;
    }
    
    /**
     * 获取所有配置
     * 
     * @return array
     */
    public static function all()
    {
        return self::$config;
    }
    
    /**
     * 检查配置是否存在
     * 
     * @param string $key 配置键
     * @return bool
     */
    public static function has($key)
    {
        return self::get($key) !== null;
    }
} 