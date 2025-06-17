<?php

namespace App\Services;

use Exception;

/**
 * DeepSeek API 集成类
 * 通过 OpenRouter 调用 DeepSeek 模型
 */
class DeepSeekAPI
{
    /**
     * API 配置
     */
    private $apiKey;
    private $apiUrl;
    private $model;
    private $temperature;
    private $maxTokens;
    
    /**
     * 重试配置
     */
    private $maxRetries = 3;
    private $retryDelay = 1; // 秒
    
    /**
     * 请求限流配置
     */
    private $requestInterval = 1; // 最小请求间隔（秒）
    private $lastRequestTime = 0;
    
    /**
     * 日志记录器
     */
    private $logger;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 从配置文件加载设置
        $this->apiKey = config('app.deepseek.api_key');
        $this->apiUrl = config('app.deepseek.api_url');
        $this->model = config('app.deepseek.model');
        $this->temperature = config('app.deepseek.temperature', 0.7);
        $this->maxTokens = config('app.deepseek.max_tokens', 2000);
        
        // 验证必要配置
        if (empty($this->apiKey)) {
            throw new Exception('DeepSeek API key not configured');
        }
        
        // 初始化日志记录器
        $this->logger = new ApiLogger();
    }
    
    /**
     * 调用 AI 进行中医诊断
     * 
     * @param array $consultationData 问诊数据
     * @return array AI 诊断结果
     */
    public function diagnose(array $consultationData)
    {
        // 格式化问诊数据为 AI 分析格式
        $prompt = $this->formatConsultationData($consultationData);
        
        // 构建请求消息
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        // 发送 API 请求
        return $this->sendRequest($messages);
    }
    
    /**
     * 格式化问诊数据
     * 
     * @param array $data 原始问诊数据
     * @return string 格式化后的文本
     */
    private function formatConsultationData(array $data)
    {
        $formatted = "## 患者基本信息\n";
        $formatted .= "- 姓名：{$data['patient_name']}\n";
        $formatted .= "- 年龄：{$data['age']}岁\n";
        $formatted .= "- 性别：{$data['gender']}\n";
        $formatted .= "- 身高：{$data['height']}cm\n";
        $formatted .= "- 体重：{$data['weight']}kg\n\n";
        
        $formatted .= "## 主诉\n";
        $formatted .= "{$data['chief_complaint']}\n\n";
        
        $formatted .= "## 现病史\n";
        $formatted .= "{$data['present_illness']}\n\n";
        
        if (!empty($data['past_history'])) {
            $formatted .= "## 既往史\n";
            $formatted .= "{$data['past_history']}\n\n";
        }
        
        if (!empty($data['family_history'])) {
            $formatted .= "## 家族史\n";
            $formatted .= "{$data['family_history']}\n\n";
        }
        
        // 中医四诊信息
        $formatted .= "## 中医四诊\n";
        
        // 望诊
        $formatted .= "### 望诊\n";
        if (!empty($data['complexion'])) {
            $formatted .= "- 面色：{$data['complexion']}\n";
        }
        if (!empty($data['spirit'])) {
            $formatted .= "- 精神状态：{$data['spirit']}\n";
        }
        if (!empty($data['body_shape'])) {
            $formatted .= "- 形体：{$data['body_shape']}\n";
        }
        
        // 舌诊
        if (!empty($data['tongue_body']) || !empty($data['tongue_coating'])) {
            $formatted .= "### 舌诊\n";
            if (!empty($data['tongue_body'])) {
                $formatted .= "- 舌质：{$data['tongue_body']}\n";
            }
            if (!empty($data['tongue_coating'])) {
                $formatted .= "- 舌苔：{$data['tongue_coating']}\n";
            }
        }
        
        // 脉诊
        if (!empty($data['pulse'])) {
            $formatted .= "### 脉诊\n";
            $formatted .= "- 脉象：{$data['pulse']}\n";
        }
        
        // 闻诊
        if (!empty($data['voice']) || !empty($data['breath'])) {
            $formatted .= "### 闻诊\n";
            if (!empty($data['voice'])) {
                $formatted .= "- 声音：{$data['voice']}\n";
            }
            if (!empty($data['breath'])) {
                $formatted .= "- 呼吸：{$data['breath']}\n";
            }
        }
        
        // 问诊
        $formatted .= "### 问诊\n";
        if (!empty($data['sleep'])) {
            $formatted .= "- 睡眠：{$data['sleep']}\n";
        }
        if (!empty($data['appetite'])) {
            $formatted .= "- 食欲：{$data['appetite']}\n";
        }
        if (!empty($data['bowel'])) {
            $formatted .= "- 大便：{$data['bowel']}\n";
        }
        if (!empty($data['urine'])) {
            $formatted .= "- 小便：{$data['urine']}\n";
        }
        
        return $formatted;
    }
    
    /**
     * 获取系统提示词
     * 
     * @return string
     */
    private function getSystemPrompt()
    {
        return "你是一位经验丰富的中医专家，擅长通过四诊（望、闻、问、切）进行诊断。
请根据提供的患者信息进行专业的中医诊断分析。

请按以下格式输出诊断结果：

## 证候分析
分析患者的主要证候，包括脏腑辨证、气血津液辨证等。

## 病机
说明疾病的发生发展机理。

## 治法
根据辨证结果提出治疗原则。

## 方剂推荐
推荐1-2个经典方剂，说明方剂组成和功效。

## 中药处方
开具具体的中药处方，包括：
- 药物名称和剂量
- 用法用量
- 注意事项

## 调护建议
提供生活起居、饮食、情志等方面的调理建议。

请确保诊断基于中医理论，语言专业准确。";
    }
    
    /**
     * 发送 API 请求
     * 
     * @param array $messages 消息数组
     * @return array 响应数据
     */
    private function sendRequest(array $messages)
    {
        // 请求限流
        $this->enforceRateLimit();
        
        $retries = 0;
        $lastError = null;
        $startTime = microtime(true);
        
        while ($retries < $this->maxRetries) {
            try {
                // 构建请求数据
                $requestData = [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens
                ];
                
                // 初始化 cURL
                $ch = curl_init($this->apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                
                // 设置请求头
                $headers = [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ];
                
                // 如果使用 OpenRouter，添加额外的头部
                if (strpos($this->apiUrl, 'openrouter.ai') !== false) {
                    $headers[] = 'HTTP-Referer: ' . config('app.url', 'http://localhost');
                    $headers[] = 'X-Title: 中医智能问诊系统';
                }
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                // 执行请求
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                // 记录请求时间
                $this->lastRequestTime = time();
                
                // 检查 cURL 错误
                if ($error) {
                    throw new Exception("cURL error: $error");
                }
                
                // 检查 HTTP 状态码
                if ($httpCode !== 200) {
                    $errorData = json_decode($response, true);
                    $errorMessage = $errorData['error']['message'] ?? "HTTP $httpCode error";
                    throw new Exception("API error: $errorMessage");
                }
                
                // 解析响应
                $responseData = json_decode($response, true);
                if (!$responseData) {
                    throw new Exception("Invalid JSON response");
                }
                
                // 提取诊断内容
                if (isset($responseData['choices'][0]['message']['content'])) {
                    $result = [
                        'success' => true,
                        'diagnosis' => $responseData['choices'][0]['message']['content'],
                        'usage' => $responseData['usage'] ?? null
                    ];
                    
                    // 记录成功的 API 调用
                    $duration = microtime(true) - $startTime;
                    $this->logger->logApiCall(
                        'DeepSeek',
                        ['messages' => $messages, 'model' => $this->model],
                        $result,
                        $duration,
                        true
                    );
                    
                    return $result;
                } else {
                    throw new Exception("Unexpected response format");
                }
                
            } catch (Exception $e) {
                $lastError = $e;
                $retries++;
                
                if ($retries < $this->maxRetries) {
                    // 重试前等待
                    sleep($this->retryDelay * $retries);
                }
            }
        }
        
        // 所有重试都失败
        $result = [
            'success' => false,
            'error' => $lastError->getMessage(),
            'diagnosis' => null
        ];
        
        // 记录失败的 API 调用
        $duration = microtime(true) - $startTime;
        $this->logger->logApiCall(
            'DeepSeek',
            ['messages' => $messages, 'model' => $this->model],
            $result,
            $duration,
            false
        );
        
        // 记录错误详情
        $this->logger->logError(
            'DeepSeek',
            $lastError->getMessage(),
            ['retries' => $retries, 'messages' => $messages]
        );
        
        return $result;
    }
    
    /**
     * 执行请求限流
     */
    private function enforceRateLimit()
    {
        $timeSinceLastRequest = time() - $this->lastRequestTime;
        
        if ($timeSinceLastRequest < $this->requestInterval) {
            $waitTime = $this->requestInterval - $timeSinceLastRequest;
            sleep($waitTime);
        }
    }
    
    /**
     * 验证 API 连接
     * 
     * @return bool
     */
    public function testConnection()
    {
        $messages = [
            [
                'role' => 'user',
                'content' => '请回复"连接成功"'
            ]
        ];
        
        $result = $this->sendRequest($messages);
        return $result['success'];
    }
} 