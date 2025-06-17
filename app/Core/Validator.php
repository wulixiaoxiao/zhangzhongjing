<?php

namespace App\Core;

/**
 * 数据验证器类
 * 提供通用的数据验证功能
 */
class Validator
{
    /**
     * 验证错误信息
     */
    private $errors = [];
    
    /**
     * 自定义错误消息
     */
    private $customMessages = [];
    
    /**
     * 默认错误消息模板
     */
    private $defaultMessages = [
        'required' => ':field 是必填项',
        'email' => ':field 必须是有效的邮箱地址',
        'numeric' => ':field 必须是数字',
        'integer' => ':field 必须是整数',
        'min' => ':field 最少需要 :min 个字符',
        'max' => ':field 最多只能有 :max 个字符',
        'between' => ':field 必须在 :min 和 :max 之间',
        'in' => ':field 必须是以下值之一: :values',
        'not_in' => ':field 不能是以下值: :values',
        'regex' => ':field 格式不正确',
        'alpha' => ':field 只能包含字母',
        'alpha_num' => ':field 只能包含字母和数字',
        'alpha_dash' => ':field 只能包含字母、数字、破折号和下划线',
        'date' => ':field 必须是有效的日期',
        'date_format' => ':field 必须符合日期格式 :format',
        'before' => ':field 必须早于 :date',
        'after' => ':field 必须晚于 :date',
        'phone' => ':field 必须是有效的手机号码',
        'id_card' => ':field 必须是有效的身份证号码',
        'chinese' => ':field 只能包含中文字符',
        'safe_text' => ':field 包含不允许的字符'
    ];
    
    /**
     * 字段显示名称
     */
    private $fieldNames = [];
    
    /**
     * 验证数据
     * 
     * @param array $data 要验证的数据
     * @param array $rules 验证规则
     * @param array $messages 自定义错误消息
     * @param array $fieldNames 字段显示名称
     * @return bool
     */
    public function validate($data, $rules, $messages = [], $fieldNames = [])
    {
        $this->errors = [];
        $this->customMessages = $messages;
        $this->fieldNames = $fieldNames;
        
        foreach ($rules as $field => $ruleString) {
            if (!isset($data[$field])) {
                $data[$field] = null;
            }
            
            $value = $data[$field];
            $fieldRules = is_string($ruleString) ? explode('|', $ruleString) : $ruleString;
            
            foreach ($fieldRules as $rule) {
                $this->validateRule($field, $value, $rule, $data);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * 验证单个规则
     * 
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param string $rule 规则
     * @param array $data 全部数据（用于某些需要比较的规则）
     */
    private function validateRule($field, $value, $rule, $data)
    {
        // 解析规则和参数
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameters) = explode(':', $rule, 2);
            $parameters = explode(',', $parameters);
        } else {
            $ruleName = $rule;
            $parameters = [];
        }
        
        // 如果值为空且不是必填规则，跳过验证
        if ($value === null && $ruleName !== 'required') {
            return;
        }
        
        $valid = true;
        
        switch ($ruleName) {
            case 'required':
                $valid = !empty($value) || $value === '0' || $value === 0;
                break;
                
            case 'email':
                $valid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                break;
                
            case 'numeric':
                $valid = is_numeric($value);
                break;
                
            case 'integer':
                $valid = filter_var($value, FILTER_VALIDATE_INT) !== false;
                break;
                
            case 'min':
                $valid = mb_strlen($value) >= $parameters[0];
                break;
                
            case 'max':
                $valid = mb_strlen($value) <= $parameters[0];
                break;
                
            case 'between':
                $length = mb_strlen($value);
                $valid = $length >= $parameters[0] && $length <= $parameters[1];
                break;
                
            case 'in':
                $valid = in_array($value, $parameters);
                break;
                
            case 'not_in':
                $valid = !in_array($value, $parameters);
                break;
                
            case 'regex':
                $valid = preg_match($parameters[0], $value);
                break;
                
            case 'alpha':
                $valid = ctype_alpha($value);
                break;
                
            case 'alpha_num':
                $valid = ctype_alnum($value);
                break;
                
            case 'alpha_dash':
                $valid = preg_match('/^[a-zA-Z0-9_-]+$/', $value);
                break;
                
            case 'date':
                $valid = strtotime($value) !== false;
                break;
                
            case 'date_format':
                $date = \DateTime::createFromFormat($parameters[0], $value);
                $valid = $date && $date->format($parameters[0]) === $value;
                break;
                
            case 'before':
                $valid = strtotime($value) < strtotime($parameters[0]);
                break;
                
            case 'after':
                $valid = strtotime($value) > strtotime($parameters[0]);
                break;
                
            case 'phone':
                $valid = preg_match('/^1[3-9]\d{9}$/', $value);
                break;
                
            case 'id_card':
                $valid = $this->validateIdCard($value);
                break;
                
            case 'chinese':
                $valid = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value);
                break;
                
            case 'safe_text':
                // 过滤危险字符，允许常见的中文标点
                $valid = !preg_match('/<script|<iframe|javascript:|onerror|onclick/i', $value);
                break;
        }
        
        if (!$valid) {
            $this->addError($field, $ruleName, $parameters);
        }
    }
    
    /**
     * 验证身份证号码
     * 
     * @param string $idCard
     * @return bool
     */
    private function validateIdCard($idCard)
    {
        if (strlen($idCard) != 18) {
            return false;
        }
        
        // 验证格式
        if (!preg_match('/^\d{17}[\dX]$/i', $idCard)) {
            return false;
        }
        
        // 验证地区码
        $areaCodes = ['11', '12', '13', '14', '15', '21', '22', '23', '31', '32', '33', '34', '35', '36', '37', '41', '42', '43', '44', '45', '46', '50', '51', '52', '53', '54', '61', '62', '63', '64', '65'];
        if (!in_array(substr($idCard, 0, 2), $areaCodes)) {
            return false;
        }
        
        // 验证出生日期
        $birthDate = substr($idCard, 6, 8);
        $date = \DateTime::createFromFormat('Ymd', $birthDate);
        if (!$date || $date->format('Ymd') !== $birthDate) {
            return false;
        }
        
        // 验证校验码
        $checkSum = 0;
        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $checkCodes = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        
        for ($i = 0; $i < 17; $i++) {
            $checkSum += $idCard[$i] * $weights[$i];
        }
        
        $checkCode = $checkCodes[$checkSum % 11];
        return strtoupper($idCard[17]) === $checkCode;
    }
    
    /**
     * 添加错误信息
     * 
     * @param string $field 字段名
     * @param string $rule 规则名
     * @param array $parameters 参数
     */
    private function addError($field, $rule, $parameters = [])
    {
        $fieldName = isset($this->fieldNames[$field]) ? $this->fieldNames[$field] : $field;
        
        // 检查自定义消息
        $customKey = $field . '.' . $rule;
        if (isset($this->customMessages[$customKey])) {
            $message = $this->customMessages[$customKey];
        } elseif (isset($this->customMessages[$rule])) {
            $message = $this->customMessages[$rule];
        } else {
            $message = isset($this->defaultMessages[$rule]) ? $this->defaultMessages[$rule] : ':field 验证失败';
        }
        
        // 替换占位符
        $message = str_replace(':field', $fieldName, $message);
        
        if (!empty($parameters)) {
            switch ($rule) {
                case 'min':
                    $message = str_replace(':min', $parameters[0], $message);
                    break;
                case 'max':
                    $message = str_replace(':max', $parameters[0], $message);
                    break;
                case 'between':
                    $message = str_replace(':min', $parameters[0], $message);
                    $message = str_replace(':max', $parameters[1], $message);
                    break;
                case 'in':
                case 'not_in':
                    $message = str_replace(':values', implode(', ', $parameters), $message);
                    break;
                case 'date_format':
                    $message = str_replace(':format', $parameters[0], $message);
                    break;
                case 'before':
                case 'after':
                    $message = str_replace(':date', $parameters[0], $message);
                    break;
            }
        }
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * 获取所有错误
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * 获取第一个错误
     * 
     * @return string|null
     */
    public function getFirstError()
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
    
    /**
     * 获取指定字段的错误
     * 
     * @param string $field
     * @return array
     */
    public function getFieldErrors($field)
    {
        return isset($this->errors[$field]) ? $this->errors[$field] : [];
    }
    
    /**
     * 是否有错误
     * 
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }
    
    /**
     * 清空错误
     */
    public function clearErrors()
    {
        $this->errors = [];
    }
    
    /**
     * 过滤和清理数据
     * 
     * @param array $data
     * @param array $filters
     * @return array
     */
    public static function sanitize($data, $filters = [])
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (isset($filters[$key])) {
                $sanitized[$key] = self::applyFilter($value, $filters[$key]);
            } else {
                // 默认过滤
                $sanitized[$key] = self::applyFilter($value, 'safe');
            }
        }
        
        return $sanitized;
    }
    
    /**
     * 应用过滤器
     * 
     * @param mixed $value
     * @param string $filter
     * @return mixed
     */
    private static function applyFilter($value, $filter)
    {
        if (is_array($value)) {
            return array_map(function($v) use ($filter) {
                return self::applyFilter($v, $filter);
            }, $value);
        }
        
        switch ($filter) {
            case 'int':
                return (int) $value;
                
            case 'float':
                return (float) $value;
                
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
                
            case 'alpha':
                return preg_replace('/[^a-zA-Z]/', '', $value);
                
            case 'alpha_num':
                return preg_replace('/[^a-zA-Z0-9]/', '', $value);
                
            case 'trim':
                return trim($value);
                
            case 'lower':
                return mb_strtolower($value);
                
            case 'upper':
                return mb_strtoupper($value);
                
            case 'safe':
            default:
                // 基础 XSS 防护
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
} 