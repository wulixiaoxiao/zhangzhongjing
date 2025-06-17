<?php

namespace App\Core;

use Exception;
use PDO;

/**
 * 基础模型类
 * 提供 CRUD 操作和数据验证功能
 */
abstract class Model
{
    /**
     * 表名
     */
    protected $table;
    
    /**
     * 主键字段名
     */
    protected $primaryKey = 'id';
    
    /**
     * 是否自动管理时间戳
     */
    protected $timestamps = true;
    
    /**
     * 创建时间字段
     */
    protected $createdField = 'created_at';
    
    /**
     * 更新时间字段
     */
    protected $updatedField = 'updated_at';
    
    /**
     * 可批量赋值的字段
     */
    protected $fillable = [];
    
    /**
     * 不可批量赋值的字段
     */
    protected $guarded = ['id'];
    
    /**
     * 隐藏字段（在转换为数组/JSON时）
     */
    protected $hidden = [];
    
    /**
     * 类型转换
     */
    protected $casts = [];
    
    /**
     * 验证规则
     */
    protected $rules = [];
    
    /**
     * 验证错误信息
     */
    protected $errors = [];
    
    /**
     * 模型属性
     */
    protected $attributes = [];
    
    /**
     * 原始属性（用于检测变化）
     */
    protected $original = [];
    
    /**
     * 是否为新记录
     */
    protected $exists = false;
    
    /**
     * 构造函数
     * 
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->syncOriginal();
    }
    
    /**
     * 创建新实例
     * 
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
    
    /**
     * 查找所有记录
     * 
     * @param array $conditions
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function all($conditions = [], $orderBy = [], $limit = null, $offset = null)
    {
        $instance = new static;
        $query = "SELECT * FROM `{$instance->table}`";
        $bindings = [];
        
        // 添加条件
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $placeholders = array_fill(0, count($value), '?');
                    $where[] = "`$field` IN (" . implode(',', $placeholders) . ")";
                    $bindings = array_merge($bindings, $value);
                } else {
                    $where[] = "`$field` = ?";
                    $bindings[] = $value;
                }
            }
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        // 添加排序
        if (!empty($orderBy)) {
            $order = [];
            foreach ($orderBy as $field => $direction) {
                $order[] = "`$field` $direction";
            }
            $query .= " ORDER BY " . implode(", ", $order);
        }
        
        // 添加限制
        if ($limit !== null) {
            $query .= " LIMIT $limit";
            if ($offset !== null) {
                $query .= " OFFSET $offset";
            }
        }
        
        $results = Database::select($query, $bindings);
        
        // 转换为模型实例
        $models = [];
        foreach ($results as $result) {
            $model = new static;
            $model->setRawAttributes($result, true);
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * 根据主键查找
     * 
     * @param mixed $id
     * @return static|null
     */
    public static function find($id)
    {
        $instance = new static;
        $query = "SELECT * FROM `{$instance->table}` WHERE `{$instance->primaryKey}` = ? LIMIT 1";
        $result = Database::selectOne($query, [$id]);
        
        if ($result) {
            $instance->setRawAttributes($result, true);
            return $instance;
        }
        
        return null;
    }
    
    /**
     * 根据条件查找第一条记录
     * 
     * @param array $conditions
     * @return static|null
     */
    public static function findWhere(array $conditions)
    {
        $results = static::all($conditions, [], 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * 保存模型
     * 
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        
        if ($this->exists) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }
    
    /**
     * 删除模型
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->exists) {
            return false;
        }
        
        $query = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $affected = Database::delete($query, [$this->getAttribute($this->primaryKey)]);
        
        if ($affected > 0) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * 执行插入操作
     * 
     * @return bool
     */
    protected function performInsert()
    {
        // 添加时间戳
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $this->setAttribute($this->createdField, $now);
            $this->setAttribute($this->updatedField, $now);
        }
        
        $attributes = $this->getAttributesForInsert();
        
        if (empty($attributes)) {
            return true;
        }
        
        $columns = array_keys($attributes);
        $values = array_values($attributes);
        
        $query = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $this->table,
            implode('`, `', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );
        
        $id = Database::insert($query, $values);
        
        if ($id) {
            $this->setAttribute($this->primaryKey, $id);
            $this->exists = true;
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * 执行更新操作
     * 
     * @return bool
     */
    protected function performUpdate()
    {
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true;
        }
        
        // 添加更新时间戳
        if ($this->timestamps) {
            $this->setAttribute($this->updatedField, date('Y-m-d H:i:s'));
            $dirty = $this->getDirty();
        }
        
        $set = [];
        $bindings = [];
        
        foreach ($dirty as $column => $value) {
            $set[] = "`$column` = ?";
            $bindings[] = $value;
        }
        
        $bindings[] = $this->getAttribute($this->primaryKey);
        
        $query = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $this->table,
            implode(', ', $set),
            $this->primaryKey
        );
        
        $affected = Database::update($query, $bindings);
        
        if ($affected !== false) {
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }
    
    /**
     * 填充属性
     * 
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * 检查字段是否可填充
     * 
     * @param string $key
     * @return bool
     */
    protected function isFillable($key)
    {
        // 如果在 guarded 中，不可填充
        if (in_array($key, $this->guarded)) {
            return false;
        }
        
        // 如果 fillable 为空，允许所有非 guarded 字段
        if (empty($this->fillable)) {
            return true;
        }
        
        // 检查是否在 fillable 中
        return in_array($key, $this->fillable);
    }
    
    /**
     * 获取属性值
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castAttribute($key, $this->attributes[$key]);
        }
        
        return null;
    }
    
    /**
     * 设置属性值
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * 类型转换
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }
        
        switch ($this->casts[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : (array) $value;
            case 'json':
                return is_string($value) ? json_decode($value) : $value;
            case 'datetime':
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
            case 'date':
                return $value ? date('Y-m-d', strtotime($value)) : null;
            default:
                return $value;
        }
    }
    
    /**
     * 获取要插入的属性
     * 
     * @return array
     */
    protected function getAttributesForInsert()
    {
        $attributes = $this->attributes;
        
        // 移除主键（如果是自增）
        if (isset($attributes[$this->primaryKey]) && !$attributes[$this->primaryKey]) {
            unset($attributes[$this->primaryKey]);
        }
        
        // 处理 JSON 类型
        foreach ($attributes as $key => $value) {
            if (isset($this->casts[$key]) && in_array($this->casts[$key], ['array', 'json'])) {
                $attributes[$key] = json_encode($value);
            }
        }
        
        return $attributes;
    }
    
    /**
     * 获取修改的属性
     * 
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * 设置原始属性
     * 
     * @param array $attributes
     * @param bool $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;
        
        if ($sync) {
            $this->syncOriginal();
            $this->exists = true;
        }
        
        return $this;
    }
    
    /**
     * 同步原始属性
     * 
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;
        return $this;
    }
    
    /**
     * 验证数据
     * 
     * @return bool
     */
    public function validate()
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = $this->getAttribute($field);
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($fieldRules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    break;
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * 验证单个规则
     * 
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    protected function validateRule($field, $value, $rule)
    {
        // 解析规则参数
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "{$field} 是必填项";
                    return false;
                }
                break;
                
            case 'string':
                if (!is_string($value) && !is_null($value)) {
                    $this->errors[$field][] = "{$field} 必须是字符串";
                    return false;
                }
                break;
                
            case 'numeric':
                if (!is_numeric($value) && !is_null($value)) {
                    $this->errors[$field][] = "{$field} 必须是数字";
                    return false;
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$field} 必须是有效的邮箱地址";
                    return false;
                }
                break;
                
            case 'min':
                if ($value !== null && strlen($value) < $parameter) {
                    $this->errors[$field][] = "{$field} 最少需要 {$parameter} 个字符";
                    return false;
                }
                break;
                
            case 'max':
                if ($value !== null && strlen($value) > $parameter) {
                    $this->errors[$field][] = "{$field} 最多只能有 {$parameter} 个字符";
                    return false;
                }
                break;
                
            case 'in':
                $options = explode(',', $parameter);
                if ($value !== null && !in_array($value, $options)) {
                    $this->errors[$field][] = "{$field} 必须是以下值之一: " . $parameter;
                    return false;
                }
                break;
                
            case 'unique':
                if ($value !== null && $this->checkUnique($field, $value)) {
                    $this->errors[$field][] = "{$field} 已经存在";
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    /**
     * 检查唯一性
     * 
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function checkUnique($field, $value)
    {
        $query = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `{$field}` = ?";
        $bindings = [$value];
        
        // 如果是更新，排除当前记录
        if ($this->exists) {
            $query .= " AND `{$this->primaryKey}` != ?";
            $bindings[] = $this->getAttribute($this->primaryKey);
        }
        
        $result = Database::selectOne($query, $bindings);
        return $result['count'] > 0;
    }
    
    /**
     * 获取验证错误
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * 转换为数组
     * 
     * @return array
     */
    public function toArray()
    {
        $array = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $array[$key] = $this->getAttribute($key);
            }
        }
        
        return $array;
    }
    
    /**
     * 转换为 JSON
     * 
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
    
    /**
     * 魔术方法：获取属性
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * 魔术方法：设置属性
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * 魔术方法：检查属性是否存在
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * 魔术方法：删除属性
     * 
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
    
    /**
     * 更新模型数据
     * 
     * @param array $attributes
     * @return bool
     */
    public function update(array $attributes)
    {
        $this->fill($attributes);
        return $this->save();
    }
} 