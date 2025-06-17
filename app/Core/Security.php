<?php

namespace App\Core;

/**
 * 安全工具类
 * 提供 XSS 防护、CSRF 防护、数据加密等安全功能
 */
class Security
{
    /**
     * CSRF Token 的会话键名
     */
    const CSRF_TOKEN_KEY = '_csrf_token';
    
    /**
     * CSRF Token 的有效期（秒）
     */
    const CSRF_TOKEN_LIFETIME = 7200; // 2小时
    
    /**
     * 生成 CSRF Token
     * 
     * @return string
     */
    public static function generateCsrfToken()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_TOKEN_KEY] = [
            'token' => $token,
            'time' => time()
        ];
        
        return $token;
    }
    
    /**
     * 验证 CSRF Token
     * 
     * @param string $token
     * @return bool
     */
    public static function validateCsrfToken($token)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION[self::CSRF_TOKEN_KEY])) {
            return false;
        }
        
        $sessionData = $_SESSION[self::CSRF_TOKEN_KEY];
        
        // 检查 token 是否过期
        if (time() - $sessionData['time'] > self::CSRF_TOKEN_LIFETIME) {
            unset($_SESSION[self::CSRF_TOKEN_KEY]);
            return false;
        }
        
        // 使用时间恒定的比较防止时序攻击
        return hash_equals($sessionData['token'], $token);
    }
    
    /**
     * 获取当前 CSRF Token
     * 
     * @return string|null
     */
    public static function getCsrfToken()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (isset($_SESSION[self::CSRF_TOKEN_KEY])) {
            $sessionData = $_SESSION[self::CSRF_TOKEN_KEY];
            
            // 检查是否过期
            if (time() - $sessionData['time'] <= self::CSRF_TOKEN_LIFETIME) {
                return $sessionData['token'];
            }
        }
        
        // 如果不存在或已过期，生成新的
        return self::generateCsrfToken();
    }
    
    /**
     * HTML 实体转义（防止 XSS）
     * 
     * @param string $string
     * @param int $flags
     * @param string $encoding
     * @return string
     */
    public static function escape($string, $flags = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8')
    {
        return htmlspecialchars($string, $flags, $encoding);
    }
    
    /**
     * 批量转义数组中的值
     * 
     * @param array $data
     * @return array
     */
    public static function escapeArray($data)
    {
        return array_map(function($value) {
            if (is_string($value)) {
                return self::escape($value);
            } elseif (is_array($value)) {
                return self::escapeArray($value);
            }
            return $value;
        }, $data);
    }
    
    /**
     * 清理 HTML 内容（允许部分安全标签）
     * 
     * @param string $html
     * @param array $allowedTags
     * @return string
     */
    public static function cleanHtml($html, $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li'])
    {
        // 构建允许的标签字符串
        $allowed = '<' . implode('><', $allowedTags) . '>';
        
        // 清理 HTML
        $cleaned = strip_tags($html, $allowed);
        
        // 移除危险属性
        $cleaned = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
        $cleaned = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
        $cleaned = preg_replace('/\s*javascript\s*:/i', '', $cleaned);
        
        return $cleaned;
    }
    
    /**
     * 生成安全的随机字符串
     * 
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * 密码哈希
     * 
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * 验证密码
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 加密数据
     * 
     * @param string $data
     * @param string $key
     * @return string
     */
    public static function encrypt($data, $key = null)
    {
        $key = $key ?: config('app.encryption_key', self::getDefaultKey());
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        $ciphertext = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        return base64_encode($iv . $ciphertext);
    }
    
    /**
     * 解密数据
     * 
     * @param string $data
     * @param string $key
     * @return string|false
     */
    public static function decrypt($data, $key = null)
    {
        $key = $key ?: config('app.encryption_key', self::getDefaultKey());
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        
        $data = base64_decode($data);
        $iv = substr($data, 0, $ivlen);
        $ciphertext = substr($data, $ivlen);
        
        return openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    }
    
    /**
     * 获取默认加密密钥
     * 
     * @return string
     */
    private static function getDefaultKey()
    {
        // 从环境变量或配置文件获取
        $key = $_ENV['APP_KEY'] ?? null;
        
        if (!$key) {
            // 如果没有设置，生成一个并保存
            $keyFile = dirname(__DIR__, 2) . '/.app_key';
            
            if (file_exists($keyFile)) {
                $key = file_get_contents($keyFile);
            } else {
                $key = self::generateRandomString(64);
                file_put_contents($keyFile, $key);
                chmod($keyFile, 0600); // 只有所有者可读写
            }
        }
        
        return $key;
    }
    
    /**
     * 验证输入是否包含潜在的 XSS 攻击
     * 
     * @param string $input
     * @return bool
     */
    public static function hasXssRisk($input)
    {
        $dangerous_patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<embed[^>]*>/i',
            '/<object[^>]*>/i',
            '/expression\s*\(/i',
            '/vbscript:/i',
            '/data:text\/html/i'
        ];
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 清理文件名
     * 
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename($filename)
    {
        // 移除路径信息
        $filename = basename($filename);
        
        // 替换非法字符
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // 防止目录遍历
        $filename = str_replace(['..', './'], '', $filename);
        
        // 限制长度
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $name = substr($name, 0, 255 - strlen($ext) - 1);
            $filename = $name . '.' . $ext;
        }
        
        return $filename;
    }
    
    /**
     * 验证 API 密钥
     * 
     * @param string $apiKey
     * @param string $expectedKey
     * @return bool
     */
    public static function validateApiKey($apiKey, $expectedKey = null)
    {
        $expectedKey = $expectedKey ?: config('app.api_key');
        
        if (!$expectedKey) {
            return false;
        }
        
        // 使用时间恒定的比较
        return hash_equals($expectedKey, $apiKey);
    }
    
    /**
     * 获取客户端真实 IP
     * 
     * @return string
     */
    public static function getClientIp()
    {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * 检查是否是安全的 URL
     * 
     * @param string $url
     * @return bool
     */
    public static function isSafeUrl($url)
    {
        // 解析 URL
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['scheme'])) {
            return false;
        }
        
        // 只允许 http 和 https
        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }
        
        // 检查是否是本地 URL
        $host = $parsed['host'] ?? '';
        $local_host = $_SERVER['HTTP_HOST'] ?? '';
        
        return $host === $local_host;
    }
} 