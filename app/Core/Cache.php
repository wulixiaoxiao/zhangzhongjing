<?php

namespace App\Core;

/**
 * 缓存管理类
 */
class Cache
{
    /**
     * 缓存目录
     */
    private static $cacheDir = STORAGE_PATH . 'cache/';
    
    /**
     * 默认缓存时间（秒）
     */
    private static $defaultTTL = 3600;
    
    /**
     * 设置缓存
     * 
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $ttl 缓存时间（秒）
     * @return bool
     */
    public static function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?: self::$defaultTTL;
        $filename = self::getCacheFile($key);
        
        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];
        
        // 确保缓存目录存在
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    /**
     * 获取缓存
     * 
     * @param string $key 缓存键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $filename = self::getCacheFile($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        // 检查是否过期
        if ($data['expires'] < time()) {
            unlink($filename);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * 删除缓存
     * 
     * @param string $key 缓存键
     * @return bool
     */
    public static function delete($key)
    {
        $filename = self::getCacheFile($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * 清空所有缓存
     * 
     * @return bool
     */
    public static function flush()
    {
        $files = glob(self::$cacheDir . '*/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * 检查缓存是否存在
     * 
     * @param string $key 缓存键
     * @return bool
     */
    public static function has($key)
    {
        return self::get($key) !== null;
    }
    
    /**
     * 记住数据
     * 如果缓存存在则返回缓存，否则执行回调并缓存结果
     * 
     * @param string $key 缓存键
     * @param callable $callback 回调函数
     * @param int $ttl 缓存时间
     * @return mixed
     */
    public static function remember($key, $callback, $ttl = null)
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = call_user_func($callback);
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * 获取缓存文件路径
     * 
     * @param string $key 缓存键
     * @return string
     */
    private static function getCacheFile($key)
    {
        $hash = md5($key);
        $dir = substr($hash, 0, 2);
        
        return self::$cacheDir . $dir . '/' . $hash . '.cache';
    }
    
    /**
     * 页面缓存开始
     * 
     * @param string $key 缓存键
     * @param int $ttl 缓存时间
     * @return bool 是否命中缓存
     */
    public static function startPage($key, $ttl = null)
    {
        $content = self::get($key);
        
        if ($content !== null) {
            echo $content;
            return true;
        }
        
        ob_start();
        return false;
    }
    
    /**
     * 页面缓存结束
     * 
     * @param string $key 缓存键
     * @param int $ttl 缓存时间
     */
    public static function endPage($key, $ttl = null)
    {
        $content = ob_get_contents();
        ob_end_flush();
        
        self::set($key, $content, $ttl);
    }
    
    /**
     * 清理过期缓存
     * 
     * @return int 清理的文件数
     */
    public static function cleanup()
    {
        $count = 0;
        $files = glob(self::$cacheDir . '*/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data['expires'] < time()) {
                    unlink($file);
                    $count++;
                }
            }
        }
        
        return $count;
    }
} 