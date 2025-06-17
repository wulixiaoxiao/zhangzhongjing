<?php
/**
 * 应用辅助函数
 */

use App\Core\Config;
use App\Core\Security;

if (!function_exists('config')) {
    /**
     * 获取配置值
     * 
     * @param string $key 配置键，支持点号分隔的多级键
     * @param mixed $default 默认值
     * @return mixed
     */
    function config($key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量
     * 
     * @param string $key 环境变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // 转换布尔值
        if (in_array(strtolower($value), ['true', 'false', '(true)', '(false)'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        // 转换 null
        if (strtolower($value) === 'null' || $value === '(null)') {
            return null;
        }
        
        // 转换空字符串
        if ($value === '(empty)') {
            return '';
        }
        
        return $value;
    }
}

if (!function_exists('app_path')) {
    /**
     * 获取应用目录路径
     * 
     * @param string $path 相对路径
     * @return string
     */
    function app_path($path = '')
    {
        $appPath = dirname(__DIR__) . '/app';
        return $path ? $appPath . '/' . ltrim($path, '/') : $appPath;
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
     * 获取存储目录路径
     * 
     * @param string $path 相对路径
     * @return string
     */
    function storage_path($path = '')
    {
        $storagePath = dirname(__DIR__) . '/storage';
        return $path ? $storagePath . '/' . ltrim($path, '/') : $storagePath;
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取公共目录路径
     * 
     * @param string $path 相对路径
     * @return string
     */
    function public_path($path = '')
    {
        $publicPath = dirname(__DIR__) . '/public';
        return $path ? $publicPath . '/' . ltrim($path, '/') : $publicPath;
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

if (!function_exists('e')) {
    /**
     * HTML 实体转义（防止 XSS）
     * 
     * @param string $string
     * @return string
     */
    function e($string)
    {
        return Security::escape($string);
    }
}

if (!function_exists('escape')) {
    /**
     * HTML 实体转义（e 函数的别名）
     * 
     * @param string $string
     * @return string
     */
    function escape($string)
    {
        return Security::escape($string);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * 获取 CSRF Token
     * 
     * @return string
     */
    function csrf_token()
    {
        return Security::getCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * 生成 CSRF 隐藏字段
     * 
     * @return string
     */
    function csrf_field()
    {
        $token = Security::getCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('clean_html')) {
    /**
     * 清理 HTML 内容
     * 
     * @param string $html
     * @param array $allowedTags
     * @return string
     */
    function clean_html($html, $allowedTags = null)
    {
        if ($allowedTags === null) {
            return Security::cleanHtml($html);
        }
        return Security::cleanHtml($html, $allowedTags);
    }
}

if (!function_exists('encrypt')) {
    /**
     * 加密数据
     * 
     * @param string $data
     * @return string
     */
    function encrypt($data)
    {
        return Security::encrypt($data);
    }
}

if (!function_exists('decrypt')) {
    /**
     * 解密数据
     * 
     * @param string $data
     * @return string|false
     */
    function decrypt($data)
    {
        return Security::decrypt($data);
    }
}

if (!function_exists('hash_password')) {
    /**
     * 哈希密码
     * 
     * @param string $password
     * @return string
     */
    function hash_password($password)
    {
        return Security::hashPassword($password);
    }
}

if (!function_exists('verify_password')) {
    /**
     * 验证密码
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    function verify_password($password, $hash)
    {
        return Security::verifyPassword($password, $hash);
    }
}

if (!function_exists('client_ip')) {
    /**
     * 获取客户端 IP
     * 
     * @return string
     */
    function client_ip()
    {
        return Security::getClientIp();
    }
}

if (!function_exists('random_string')) {
    /**
     * 生成随机字符串
     * 
     * @param int $length
     * @return string
     */
    function random_string($length = 32)
    {
        return Security::generateRandomString($length);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * 清理文件名
     * 
     * @param string $filename
     * @return string
     */
    function sanitize_filename($filename)
    {
        return Security::sanitizeFilename($filename);
    }
} 