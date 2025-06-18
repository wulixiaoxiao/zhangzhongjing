<?php
/**
 * 性能测试脚本
 */

// 定义根目录
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('STORAGE_PATH', ROOT_PATH . 'storage/');

// 加载自动加载器
require_once APP_PATH . 'Core/Autoloader.php';
$autoloader = new \App\Core\Autoloader();
$autoloader->register();

// 加载配置
\App\Core\Config::load();

use App\Core\Database;
use App\Core\Cache;

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>性能测试</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .test-section { 
            background: #f5f5f5; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px; 
        }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .metric { 
            display: inline-block; 
            margin: 5px 10px 5px 0;
            padding: 5px 10px;
            background: #fff;
            border-radius: 3px;
        }
        pre { 
            background: #333; 
            color: #fff; 
            padding: 10px; 
            overflow-x: auto; 
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>中医问诊系统 - 性能测试</h1>
    
    <?php
    // 记录开始时间
    $startTime = microtime(true);
    $memoryStart = memory_get_usage();
    ?>
    
    <div class="test-section">
        <h2>1. 数据库查询性能测试</h2>
        <?php
        try {
            // 重置查询统计
            Database::resetQueryStats();
            
            // 测试简单查询
            $start = microtime(true);
            $patients = Database::select("SELECT * FROM patients LIMIT 10");
            $queryTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>简单查询耗时: <strong>" . round($queryTime, 2) . "ms</strong></div><br>";
            
            // 测试复杂查询
            $start = microtime(true);
            $complexQuery = Database::select("
                SELECT p.*, COUNT(c.id) as consultation_count 
                FROM patients p 
                LEFT JOIN consultations c ON p.id = c.patient_id 
                GROUP BY p.id 
                LIMIT 10
            ");
            $complexQueryTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>复杂查询耗时: <strong>" . round($complexQueryTime, 2) . "ms</strong></div><br>";
            
            // 测试缓存查询
            $start = microtime(true);
            $cachedPatients = Database::select("SELECT * FROM patients LIMIT 10");
            $cachedQueryTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>缓存查询耗时: <strong>" . round($cachedQueryTime, 2) . "ms</strong></div><br>";
            
            // 显示查询统计
            $stats = Database::getQueryStats();
            echo "<h3>查询统计：</h3>";
            echo "<div class='metric'>总查询数: <strong>{$stats['query_count']}</strong></div>";
            echo "<div class='metric'>总耗时: <strong>{$stats['total_time']}ms</strong></div>";
            echo "<div class='metric'>平均耗时: <strong>{$stats['average_time']}ms</strong></div>";
            echo "<div class='metric'>缓存数量: <strong>{$stats['cache_size']}</strong></div>";
            
            echo "<p class='success'>✓ 数据库性能测试完成</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ 数据库测试失败: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>2. 缓存性能测试</h2>
        <?php
        try {
            // 测试缓存写入
            $testData = ['name' => '测试数据', 'items' => range(1, 1000)];
            $start = microtime(true);
            Cache::set('test_data', $testData, 60);
            $writeTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>缓存写入耗时: <strong>" . round($writeTime, 2) . "ms</strong></div><br>";
            
            // 测试缓存读取
            $start = microtime(true);
            $cachedData = Cache::get('test_data');
            $readTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>缓存读取耗时: <strong>" . round($readTime, 2) . "ms</strong></div><br>";
            
            // 测试remember方法
            $start = microtime(true);
            $result = Cache::remember('expensive_operation', function() {
                // 模拟耗时操作
                usleep(50000); // 50ms
                return "计算结果";
            }, 300);
            $rememberTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>Remember首次耗时: <strong>" . round($rememberTime, 2) . "ms</strong></div><br>";
            
            // 第二次调用（应该从缓存读取）
            $start = microtime(true);
            $result2 = Cache::remember('expensive_operation', function() {
                usleep(50000);
                return "计算结果";
            }, 300);
            $rememberCachedTime = (microtime(true) - $start) * 1000;
            
            echo "<div class='metric'>Remember缓存耗时: <strong>" . round($rememberCachedTime, 2) . "ms</strong></div><br>";
            
            // 清理测试缓存
            Cache::delete('test_data');
            Cache::delete('expensive_operation');
            
            echo "<p class='success'>✓ 缓存性能测试完成</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ 缓存测试失败: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>3. 内存使用分析</h2>
        <?php
        $memoryEnd = memory_get_usage();
        $memoryPeak = memory_get_peak_usage();
        
        echo "<div class='metric'>当前内存使用: <strong>" . round($memoryEnd / 1024 / 1024, 2) . " MB</strong></div>";
        echo "<div class='metric'>峰值内存使用: <strong>" . round($memoryPeak / 1024 / 1024, 2) . " MB</strong></div>";
        echo "<div class='metric'>本次测试内存增长: <strong>" . round(($memoryEnd - $memoryStart) / 1024, 2) . " KB</strong></div>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>4. 页面加载性能</h2>
        <?php
        $totalTime = (microtime(true) - $startTime) * 1000;
        echo "<div class='metric'>页面总加载时间: <strong>" . round($totalTime, 2) . "ms</strong></div><br>";
        
        // 性能建议
        echo "<h3>性能优化建议：</h3>";
        echo "<ul>";
        
        if ($totalTime > 1000) {
            echo "<li class='warning'>页面加载时间超过1秒，建议优化数据库查询</li>";
        } else {
            echo "<li class='success'>页面加载时间良好</li>";
        }
        
        if ($stats['average_time'] > 10) {
            echo "<li class='warning'>平均查询时间较高，考虑添加索引</li>";
        } else {
            echo "<li class='success'>数据库查询性能良好</li>";
        }
        
        if ($memoryPeak > 32 * 1024 * 1024) {
            echo "<li class='warning'>内存使用较高，考虑优化数据处理逻辑</li>";
        } else {
            echo "<li class='success'>内存使用合理</li>";
        }
        
        echo "</ul>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>5. 慢查询日志</h2>
        <?php
        $slowQueryLog = STORAGE_PATH . 'logs/slow_queries.log';
        if (file_exists($slowQueryLog)) {
            $logs = file_get_contents($slowQueryLog);
            if (!empty($logs)) {
                echo "<pre>" . htmlspecialchars(substr($logs, -1000)) . "</pre>";
            } else {
                echo "<p class='success'>没有慢查询记录</p>";
            }
        } else {
            echo "<p>慢查询日志文件不存在</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>6. 系统信息</h2>
        <?php
        echo "<div class='metric'>PHP版本: <strong>" . PHP_VERSION . "</strong></div>";
        echo "<div class='metric'>操作系统: <strong>" . PHP_OS . "</strong></div>";
        echo "<div class='metric'>服务器软件: <strong>" . $_SERVER['SERVER_SOFTWARE'] . "</strong></div>";
        echo "<div class='metric'>最大执行时间: <strong>" . ini_get('max_execution_time') . "秒</strong></div>";
        echo "<div class='metric'>内存限制: <strong>" . ini_get('memory_limit') . "</strong></div>";
        ?>
    </div>
</body>
</html> 