<?php

namespace App\Core;

/**
 * 控制器基类
 */
abstract class Controller
{
    /**
     * 加载视图
     * 
     * @param string $view 视图文件名
     * @param array $data 传递给视图的数据
     * @param bool $return 是否返回视图内容而不是直接输出
     * @return string|void
     */
    protected function view($view, $data = [], $return = false)
    {
        // 将数组键转换为变量
        extract($data);
        
        // 构建视图文件路径
        $viewFile = APP_PATH . 'Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file {$view} not found");
        }
        
        if ($return) {
            // 返回视图内容
            ob_start();
            require $viewFile;
            return ob_get_clean();
        } else {
            // 直接输出视图
            require $viewFile;
        }
    }

    /**
     * 加载模型
     * 
     * @param string $model 模型名称
     * @return object
     */
    protected function model($model)
    {
        $modelClass = "App\\Models\\{$model}";
        
        if (!class_exists($modelClass)) {
            throw new \Exception("Model {$modelClass} not found");
        }
        
        return new $modelClass();
    }

    /**
     * 重定向到指定 URL
     * 
     * @param string $url 目标 URL
     * @param int $statusCode HTTP 状态码
     */
    protected function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * 返回 JSON 响应
     * 
     * @param mixed $data 要返回的数据
     * @param int $statusCode HTTP 状态码
     */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 获取请求参数
     * 
     * @param string $key 参数键
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function input($key, $default = null)
    {
        if ($this->isPost()) {
            return $_POST[$key] ?? $default;
        }
        
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取所有请求参数
     * 
     * @return array
     */
    protected function all()
    {
        if ($this->isPost()) {
            return $_POST;
        }
        
        return $_GET;
    }

    /**
     * 检查是否是 POST 请求
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
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
     * 验证 CSRF 令牌
     * 
     * @return bool
     */
    protected function validateCsrf()
    {
        if (!$this->isPost()) {
            return true;
        }
        
        $token = $_POST['_csrf_token'] ?? '';
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        
        return $token !== '' && $token === $sessionToken;
    }

    /**
     * 生成 CSRF 令牌
     * 
     * @return string
     */
    protected function generateCsrf()
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_csrf_token'];
    }
} 