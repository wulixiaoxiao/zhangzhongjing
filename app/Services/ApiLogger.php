<?php

namespace App\Services;

/**
 * API 日志记录器
 * 记录所有 API 调用的详细信息
 */
class ApiLogger
{
    /**
     * 日志文件路径
     */
    private $logPath;
    
    /**
     * 是否启用日志
     */
    private $enabled;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logPath = storage_path('logs/api_calls.log');
        $this->enabled = config('app.debug', false);
        
        // 确保日志目录存在
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * 记录 API 请求
     * 
     * @param string $service 服务名称
     * @param array $request 请求数据
     * @param array $response 响应数据
     * @param float $duration 请求耗时（秒）
     * @param bool $success 是否成功
     */
    public function logApiCall($service, $request, $response, $duration, $success = true)
    {
        if (!$this->enabled) {
            return;
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => $service,
            'success' => $success,
            'duration' => round($duration, 3),
            'request' => $this->sanitizeData($request),
            'response' => $this->sanitizeData($response),
            'memory_usage' => $this->getMemoryUsage(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ];
        
        // 写入日志文件
        $this->writeLog($logEntry);
        
        // 如果是错误，记录到数据库
        if (!$success && function_exists('logToDatabase')) {
            $this->logToDatabase($logEntry);
        }
    }
    
    /**
     * 记录 API 错误
     * 
     * @param string $service 服务名称
     * @param string $error 错误信息
     * @param array $context 上下文信息
     */
    public function logError($service, $error, $context = [])
    {
        if (!$this->enabled) {
            return;
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => $service,
            'error' => $error,
            'context' => $this->sanitizeData($context),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ];
        
        // 写入错误日志
        $errorPath = storage_path('logs/api_errors.log');
        file_put_contents(
            $errorPath,
            json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
    
    /**
     * 清理敏感数据
     * 
     * @param mixed $data
     * @return mixed
     */
    private function sanitizeData($data)
    {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // 隐藏敏感字段
                if (in_array(strtolower($key), ['api_key', 'password', 'token', 'secret'])) {
                    $sanitized[$key] = '***HIDDEN***';
                } else {
                    $sanitized[$key] = $this->sanitizeData($value);
                }
            }
            return $sanitized;
        }
        
        return $data;
    }
    
    /**
     * 写入日志文件
     * 
     * @param array $entry
     */
    private function writeLog($entry)
    {
        $logLine = json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents(
            $this->logPath,
            $logLine,
            FILE_APPEND | LOCK_EX
        );
        
        // 日志文件大小管理（超过 10MB 自动归档）
        if (filesize($this->logPath) > 10 * 1024 * 1024) {
            $this->rotateLog();
        }
    }
    
    /**
     * 日志轮转
     */
    private function rotateLog()
    {
        $archivePath = storage_path('logs/api_calls_' . date('Y-m-d_H-i-s') . '.log');
        rename($this->logPath, $archivePath);
        
        // 压缩旧日志
        if (function_exists('gzopen')) {
            $gz = gzopen($archivePath . '.gz', 'w9');
            gzwrite($gz, file_get_contents($archivePath));
            gzclose($gz);
            unlink($archivePath);
        }
    }
    
    /**
     * 获取内存使用情况
     * 
     * @return string
     */
    private function getMemoryUsage()
    {
        $memory = memory_get_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($memory, 1024));
        
        return round($memory / pow(1024, $i), 2) . ' ' . $units[$i];
    }
    
    /**
     * 记录到数据库
     * 
     * @param array $entry
     */
    private function logToDatabase($entry)
    {
        // TODO: 实现数据库记录功能
        // 需要在实现数据库模型后完成
    }
    
    /**
     * 获取最近的 API 调用记录
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentCalls($limit = 50)
    {
        if (!file_exists($this->logPath)) {
            return [];
        }
        
        $lines = file($this->logPath);
        $calls = [];
        
        // 获取最后 N 行
        $startIndex = max(0, count($lines) - $limit);
        for ($i = $startIndex; $i < count($lines); $i++) {
            $entry = json_decode($lines[$i], true);
            if ($entry) {
                $calls[] = $entry;
            }
        }
        
        return array_reverse($calls);
    }
    
    /**
     * 获取 API 调用统计
     * 
     * @return array
     */
    public function getStatistics()
    {
        if (!file_exists($this->logPath)) {
            return [
                'total_calls' => 0,
                'success_rate' => 0,
                'average_duration' => 0,
                'services' => []
            ];
        }
        
        $stats = [
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_duration' => 0,
            'services' => []
        ];
        
        $handle = fopen($this->logPath, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry) {
                    $stats['total_calls']++;
                    
                    if ($entry['success']) {
                        $stats['successful_calls']++;
                    } else {
                        $stats['failed_calls']++;
                    }
                    
                    $stats['total_duration'] += $entry['duration'];
                    
                    // 按服务统计
                    $service = $entry['service'];
                    if (!isset($stats['services'][$service])) {
                        $stats['services'][$service] = [
                            'calls' => 0,
                            'success' => 0,
                            'failed' => 0,
                            'duration' => 0
                        ];
                    }
                    
                    $stats['services'][$service]['calls']++;
                    if ($entry['success']) {
                        $stats['services'][$service]['success']++;
                    } else {
                        $stats['services'][$service]['failed']++;
                    }
                    $stats['services'][$service]['duration'] += $entry['duration'];
                }
            }
            fclose($handle);
        }
        
        // 计算成功率和平均耗时
        $stats['success_rate'] = $stats['total_calls'] > 0 
            ? round(($stats['successful_calls'] / $stats['total_calls']) * 100, 2)
            : 0;
            
        $stats['average_duration'] = $stats['total_calls'] > 0
            ? round($stats['total_duration'] / $stats['total_calls'], 3)
            : 0;
        
        return $stats;
    }
} 