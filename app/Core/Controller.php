<?php

namespace App\Core;

/**
 * 控制器基类
 * 提供视图渲染、数据传递等基础功能
 */
abstract class Controller
{
    /**
     * 当前请求的数据
     */
    protected $request = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化请求数据
        $this->request = array_merge($_GET, $_POST);
        
        // 启动会话（如果还没有启动）
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * 渲染视图
     * 
     * @param string $view 视图文件路径（相对于 Views 目录）
     * @param array $data 传递给视图的数据
     * @return void
     */
    protected function view($view, $data = [])
    {
        // 将数据导出为变量
        extract($data);
        
        // 添加安全辅助函数到视图
        $escape = function($string) {
            return Security::escape($string);
        };
        
        $csrf_token = function() {
            return Security::getCsrfToken();
        };
        
        $csrf_field = function() {
            $token = Security::getCsrfToken();
            return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
        };
        
        // 构建视图文件路径
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        
        // 检查视图文件是否存在
        if (!file_exists($viewFile)) {
            $this->error404("View file not found: $view");
            return;
        }
        
        // 包含视图文件
        require $viewFile;
    }
    
    /**
     * 返回 JSON 响应
     * 
     * @param mixed $data 要返回的数据
     * @param int $statusCode HTTP 状态码
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        
        // 防止 JSON 注入攻击
        echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }
    
    /**
     * 重定向
     * 
     * @param string $url 目标 URL
     * @param int $statusCode HTTP 状态码
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        // 验证 URL 安全性
        if (!Security::isSafeUrl($url) && strpos($url, '/') !== 0) {
            $url = '/';
        }
        
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    /**
     * 显示 404 错误页面
     * 
     * @param string $message 错误信息
     * @return void
     */
    protected function error404($message = 'Page not found')
    {
        http_response_code(404);
        $this->view('errors/404', ['message' => $message]);
        exit;
    }
    
    /**
     * 获取请求参数
     * 
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function input($key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }
    
    /**
     * 获取所有请求参数
     * 
     * @return array
     */
    protected function all()
    {
        return $this->request;
    }
    
    /**
     * 检查请求方法
     * 
     * @param string $method 请求方法
     * @return bool
     */
    protected function isMethod($method)
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }
    
    /**
     * 检查是否是 POST 请求
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $this->isMethod('POST');
    }
    
    /**
     * 检查是否是 AJAX 请求
     * 
     * @return bool
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * 设置会话数据
     * 
     * @param string $key 键名
     * @param mixed $value 值
     * @return void
     */
    protected function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * 获取会话数据
     * 
     * @param string $key 键名
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function getSession($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * 删除会话数据
     * 
     * @param string $key 键名
     * @return void
     */
    protected function removeSession($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * 生成 CSRF Token
     * 
     * @return string
     */
    protected function generateCsrf()
    {
        return Security::generateCsrfToken();
    }
    
    /**
     * 验证 CSRF Token
     * 
     * @param string $token
     * @return bool
     */
    protected function validateCsrf($token = null)
    {
        $token = $token ?: $this->input('_csrf_token');
        return Security::validateCsrfToken($token);
    }
    
    /**
     * 要求 CSRF 验证
     * 
     * @return void
     */
    protected function requireCsrf()
    {
        if ($this->isPost() && !$this->validateCsrf()) {
            $this->json(['error' => 'CSRF token validation failed'], 403);
        }
    }
    
    /**
     * 验证 API 密钥
     * 
     * @return bool
     */
    protected function validateApiKey()
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $this->input('api_key');
        return Security::validateApiKey($apiKey);
    }
    
    /**
     * 要求 API 密钥验证
     * 
     * @return void
     */
    protected function requireApiKey()
    {
        if (!$this->validateApiKey()) {
            $this->json(['error' => 'Invalid API key'], 401);
        }
    }
    
    /**
     * 记录安全日志
     * 
     * @param string $event 事件类型
     * @param array $data 相关数据
     * @return void
     */
    protected function logSecurity($event, $data = [])
    {
        $logData = [
            'event' => $event,
            'ip' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'time' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        // 记录到安全日志文件
        $logFile = dirname(__DIR__, 2) . '/storage/logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
} 