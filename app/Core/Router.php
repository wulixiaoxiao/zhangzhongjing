<?php

namespace App\Core;

/**
 * 路由处理类
 */
class Router
{
    /**
     * 路由规则存储
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * 当前请求的 URL
     */
    private $url;

    /**
     * 当前请求方法
     */
    private $method;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->url = $this->getUrl();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 注册 GET 路由
     */
    public function get($pattern, $callback)
    {
        $this->addRoute('GET', $pattern, $callback);
    }

    /**
     * 注册 POST 路由
     */
    public function post($pattern, $callback)
    {
        $this->addRoute('POST', $pattern, $callback);
    }

    /**
     * 注册 PUT 路由
     */
    public function put($pattern, $callback)
    {
        $this->addRoute('PUT', $pattern, $callback);
    }

    /**
     * 注册 DELETE 路由
     */
    public function delete($pattern, $callback)
    {
        $this->addRoute('DELETE', $pattern, $callback);
    }

    /**
     * 添加路由规则
     */
    private function addRoute($method, $pattern, $callback)
    {
        // 将路由模式转换为正则表达式
        $pattern = $this->convertToRegex($pattern);
        
        $this->routes[$method][$pattern] = $callback;
    }

    /**
     * 将路由模式转换为正则表达式
     */
    private function convertToRegex($pattern)
    {
        // 移除开头的斜杠（如果有）
        $pattern = ltrim($pattern, '/');
        
        // 转义斜杠
        $pattern = str_replace('/', '\/', $pattern);
        
        // 将参数占位符转换为正则表达式
        // {id} => (?P<id>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        // 将可选参数转换为正则表达式
        // {id?} => (?P<id>[^/]*)?
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\?\}/', '(?P<$1>[^\/]*)?', $pattern);
        
        return '/^' . $pattern . '$/i';
    }

    /**
     * 分发请求
     */
    public function dispatch()
    {
        $url = $this->url ?: '';
        
        // 遍历当前请求方法的路由规则
        foreach ($this->routes[$this->method] as $pattern => $callback) {
            if (preg_match($pattern, $url, $matches)) {
                // 提取命名参数
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                // 调用回调函数
                return $this->call($callback, $params);
            }
        }
        
        // 没有匹配的路由，返回 404
        $this->notFound();
    }

    /**
     * 调用路由回调
     */
    private function call($callback, $params = [])
    {
        // 如果是字符串格式的控制器方法
        if (is_string($callback)) {
            list($controller, $method) = explode('@', $callback);
            
            // 构建控制器类名
            $controller = "App\\Controllers\\{$controller}";
            
            // 检查控制器类是否存在
            if (!class_exists($controller)) {
                throw new \Exception("Controller {$controller} not found");
            }
            
            // 实例化控制器
            $controllerInstance = new $controller();
            
            // 检查方法是否存在
            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("Method {$method} not found in controller {$controller}");
            }
            
            // 调用控制器方法
            return call_user_func_array([$controllerInstance, $method], $params);
        }
        
        // 如果是闭包函数
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        
        throw new \Exception("Invalid route callback");
    }

    /**
     * 获取当前请求的 URL
     */
    private function getUrl()
    {
        if (isset($_GET['url'])) {
            return rtrim($_GET['url'], '/');
        }
        
        return '';
    }

    /**
     * 404 页面
     */
    private function notFound()
    {
        http_response_code(404);
        require_once APP_PATH . 'Views/errors/404.php';
        exit;
    }
} 