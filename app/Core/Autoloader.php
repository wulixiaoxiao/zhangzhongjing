<?php

namespace App\Core;

/**
 * PSR-4 自动加载器
 */
class Autoloader
{
    /**
     * 命名空间映射
     */
    private $namespaces = [];

    /**
     * 注册自动加载器
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
        
        // 注册默认命名空间
        $this->addNamespace('App', APP_PATH);
        
        // 加载辅助函数
        $helpersFile = dirname(APP_PATH) . '/src/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    /**
     * 添加命名空间映射
     */
    public function addNamespace($prefix, $baseDir)
    {
        // 标准化命名空间前缀
        $prefix = trim($prefix, '\\') . '\\';
        
        // 标准化基础目录
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        
        // 初始化命名空间数组
        if (isset($this->namespaces[$prefix]) === false) {
            $this->namespaces[$prefix] = [];
        }
        
        // 添加基础目录
        array_push($this->namespaces[$prefix], $baseDir);
    }

    /**
     * 加载类文件
     */
    protected function loadClass($class)
    {
        // 遍历命名空间映射
        foreach ($this->namespaces as $prefix => $baseDirs) {
            // 检查类是否使用这个命名空间前缀
            if (strpos($class, $prefix) === 0) {
                // 获取相对类名
                $relativeClass = substr($class, strlen($prefix));
                
                // 尝试从每个基础目录加载文件
                foreach ($baseDirs as $baseDir) {
                    $file = $this->loadMappedFile($baseDir, $relativeClass);
                    if ($file) {
                        return $file;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * 加载映射的文件
     */
    protected function loadMappedFile($baseDir, $relativeClass)
    {
        // 将命名空间分隔符替换为目录分隔符
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        // 如果文件存在则加载
        if (file_exists($file)) {
            require $file;
            return $file;
        }
        
        return false;
    }
} 