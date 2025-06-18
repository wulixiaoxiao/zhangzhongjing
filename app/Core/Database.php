<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * 数据库连接管理类
 * 使用 PDO 实现数据库连接，支持连接池
 */
class Database
{
    /**
     * 数据库连接实例
     */
    private static $connections = [];
    
    /**
     * 默认连接名称
     */
    private static $defaultConnection = 'default';
    
    /**
     * 配置信息
     */
    private static $config = [];
    
    /**
     * 是否已初始化
     */
    private static $initialized = false;
    
    /**
     * PDO实例
     */
    private static $pdo = null;
    
    /**
     * 查询计数器
     */
    private static $queryCount = 0;
    
    /**
     * 查询执行时间记录
     */
    private static $queryTime = 0;
    
    /**
     * 慢查询日志阈值（毫秒）
     */
    private static $slowQueryThreshold = 1000;
    
    /**
     * 查询缓存
     */
    private static $queryCache = [];
    
    /**
     * 缓存启用状态
     */
    private static $cacheEnabled = true;
    
    /**
     * 初始化数据库配置
     */
    private static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        
        // 从配置文件加载数据库配置
        self::$config = [
            'default' => [
                'driver' => 'mysql',
                'host' => config('app.database.host', 'localhost'),
                'port' => config('app.database.port', '3306'),
                'database' => config('app.database.name', 'yisheng_db'),
                'username' => config('app.database.user', 'root'),
                'password' => config('app.database.pass', ''),
                'charset' => config('app.database.charset', 'utf8mb4'),
                'collation' => config('app.database.collation', 'utf8mb4_unicode_ci'),
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            ]
        ];
        
        self::$initialized = true;
    }
    
    /**
     * 获取数据库连接
     * 
     * @param string $name 连接名称
     * @return PDO
     * @throws Exception
     */
    public static function connection($name = null)
    {
        self::initialize();
        
        $name = $name ?: self::$defaultConnection;
        
        // 如果连接已存在且有效，直接返回
        if (isset(self::$connections[$name]) && self::isConnectionValid(self::$connections[$name])) {
            return self::$connections[$name];
        }
        
        // 创建新连接
        return self::$connections[$name] = self::createConnection($name);
    }
    
    /**
     * 创建数据库连接
     * 
     * @param string $name 连接名称
     * @return PDO
     * @throws Exception
     */
    private static function createConnection($name)
    {
        if (!isset(self::$config[$name])) {
            throw new Exception("Database connection [$name] not configured.");
        }
        
        $config = self::$config[$name];
        
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
            
            // 记录连接日志
            if (config('app.debug')) {
                error_log("Database connection established: $name");
            }
            
            return $pdo;
            
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * 检查连接是否有效
     * 
     * @param PDO $connection
     * @return bool
     */
    private static function isConnectionValid($connection)
    {
        try {
            $connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * 关闭指定连接
     * 
     * @param string $name 连接名称
     */
    public static function disconnect($name = null)
    {
        $name = $name ?: self::$defaultConnection;
        
        if (isset(self::$connections[$name])) {
            self::$connections[$name] = null;
            unset(self::$connections[$name]);
        }
    }
    
    /**
     * 关闭所有连接
     */
    public static function disconnectAll()
    {
        foreach (self::$connections as $name => $connection) {
            self::disconnect($name);
        }
    }
    
    /**
     * 开始事务
     * 
     * @param string $name 连接名称
     * @return bool
     */
    public static function beginTransaction($name = null)
    {
        // 清除缓存
        if (self::$cacheEnabled) {
            self::clearCache();
        }
        return self::connection($name)->beginTransaction();
    }
    
    /**
     * 提交事务
     * 
     * @param string $name 连接名称
     * @return bool
     */
    public static function commit($name = null)
    {
        // 清除缓存
        if (self::$cacheEnabled) {
            self::clearCache();
        }
        return self::connection($name)->commit();
    }
    
    /**
     * 回滚事务
     * 
     * @param string $name 连接名称
     * @return bool
     */
    public static function rollback($name = null)
    {
        return self::connection($name)->rollBack();
    }
    
    /**
     * 执行原始 SQL 查询（带性能监控）
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return \PDOStatement
     */
    public static function query($sql, $bindings = [], $name = null)
    {
        $startTime = microtime(true);
        
        // 生成缓存键
        $cacheKey = md5($sql . serialize($bindings));
        
        // 检查缓存（仅对SELECT查询）
        if (self::$cacheEnabled && stripos(trim($sql), 'SELECT') === 0) {
            if (isset(self::$queryCache[$cacheKey])) {
                return self::$queryCache[$cacheKey];
            }
        }
        
        $connection = self::connection($name);
        $statement = $connection->prepare($sql);
        
        // 绑定参数
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $statement->bindValue($key + 1, $value);
            } else {
                $statement->bindValue($key, $value);
            }
        }
        
        $statement->execute();
        
        // 记录查询统计
        $executionTime = (microtime(true) - $startTime) * 1000; // 转换为毫秒
        self::$queryCount++;
        self::$queryTime += $executionTime;
        
        // 记录慢查询
        if ($executionTime > self::$slowQueryThreshold) {
            self::logSlowQuery($sql, $bindings, $executionTime);
        }
        
        return $statement;
    }
    
    /**
     * 执行 SELECT 查询
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return array
     */
    public static function select($sql, $bindings = [], $name = null)
    {
        $statement = self::query($sql, $bindings, $name);
        return $statement->fetchAll();
    }
    
    /**
     * 执行 SELECT 查询并返回第一条记录
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return array|null
     */
    public static function selectOne($sql, $bindings = [], $name = null)
    {
        $statement = self::query($sql, $bindings, $name);
        return $statement->fetch() ?: null;
    }
    
    /**
     * 执行 INSERT 查询
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return int 最后插入的 ID
     */
    public static function insert($sql, $bindings = [], $name = null)
    {
        $connection = self::connection($name);
        self::query($sql, $bindings, $name);
        return $connection->lastInsertId();
    }
    
    /**
     * 执行 UPDATE 查询
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return int 影响的行数
     */
    public static function update($sql, $bindings = [], $name = null)
    {
        $statement = self::query($sql, $bindings, $name);
        return $statement->rowCount();
    }
    
    /**
     * 执行 DELETE 查询
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return int 影响的行数
     */
    public static function delete($sql, $bindings = [], $name = null)
    {
        $statement = self::query($sql, $bindings, $name);
        return $statement->rowCount();
    }
    
    /**
     * 执行语句（不返回结果）
     * 
     * @param string $sql SQL 语句
     * @param array $bindings 绑定参数
     * @param string $name 连接名称
     * @return bool
     */
    public static function statement($sql, $bindings = [], $name = null)
    {
        self::query($sql, $bindings, $name);
        return true;
    }
    
    /**
     * 获取表信息
     * 
     * @param string $table 表名
     * @param string $name 连接名称
     * @return array
     */
    public static function getTableInfo($table, $name = null)
    {
        $sql = "SHOW COLUMNS FROM `$table`";
        return self::select($sql, [], $name);
    }
    
    /**
     * 检查表是否存在
     * 
     * @param string $table 表名
     * @param string $name 连接名称
     * @return bool
     */
    public static function tableExists($table, $name = null)
    {
        $sql = "SHOW TABLES LIKE ?";
        $result = self::selectOne($sql, [$table], $name);
        return !empty($result);
    }



    /**
     * 记录慢查询
     */
    private static function logSlowQuery($sql, $params, $executionTime)
    {
        $logFile = STORAGE_PATH . 'logs/slow_queries.log';
        $logEntry = sprintf(
            "[%s] 执行时间: %.2fms | SQL: %s | 参数: %s\n",
            date('Y-m-d H:i:s'),
            $executionTime,
            $sql,
            json_encode($params)
        );
        
        error_log($logEntry, 3, $logFile);
    }

    /**
     * 清除查询缓存
     */
    public static function clearCache()
    {
        self::$queryCache = [];
    }

    /**
     * 设置缓存状态
     */
    public static function setCacheEnabled($enabled)
    {
        self::$cacheEnabled = (bool)$enabled;
    }

    /**
     * 获取查询统计信息
     */
    public static function getQueryStats()
    {
        return [
            'query_count' => self::$queryCount,
            'total_time' => round(self::$queryTime, 2),
            'average_time' => self::$queryCount > 0 ? round(self::$queryTime / self::$queryCount, 2) : 0,
            'cache_size' => count(self::$queryCache)
        ];
    }
    
    /**
     * 重置查询统计
     */
    public static function resetQueryStats()
    {
        self::$queryCount = 0;
        self::$queryTime = 0;
    }
    
    /**
     * 批量插入数据
     * 
     * @param string $table 表名
     * @param array $data 数据数组
     * @param string $name 连接名称
     * @return bool
     */
    public static function batchInsert($table, $data, $name = null)
    {
        if (empty($data)) {
            return false;
        }
        
        $columns = array_keys($data[0]);
        $columnStr = '`' . implode('`, `', $columns) . '`';
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        
        $values = [];
        $params = [];
        foreach ($data as $row) {
            $values[] = $placeholders;
            foreach ($columns as $column) {
                $params[] = $row[$column] ?? null;
            }
        }
        
        $sql = "INSERT INTO `{$table}` ({$columnStr}) VALUES " . implode(', ', $values);
        
        return self::statement($sql, $params, $name);
    }
} 