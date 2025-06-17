<?php
/**
 * DeepSeek API 测试
 */

// 加载应用
require_once __DIR__ . '/../src/helpers.php';

// 定义应用路径常量
define('ROOT_PATH', dirname(__DIR__) . '/');
define('APP_PATH', ROOT_PATH . 'app/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('STORAGE_PATH', ROOT_PATH . 'storage/');

// 加载自动加载器
require_once APP_PATH . 'Core/Autoloader.php';

// 初始化自动加载
$autoloader = new \App\Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('App\\Services', APP_PATH . 'Services');

// 加载配置
\App\Core\Config::load();

// 使用 Services 命名空间
use App\Services\DeepSeekAPI;
use App\Services\ApiLogger;

echo "<h1>DeepSeek API 测试</h1>";

// 检查 API 密钥配置
echo "<h2>1. 配置检查</h2>";
echo "<pre>";
$apiKey = config('app.deepseek.api_key');
echo "API Key: " . ($apiKey ? '已配置 (' . substr($apiKey, 0, 10) . '...)' : '未配置') . "\n";
echo "API URL: " . config('app.deepseek.api_url') . "\n";
echo "Model: " . config('app.deepseek.model') . "\n";
echo "Temperature: " . config('app.deepseek.temperature') . "\n";
echo "Max Tokens: " . config('app.deepseek.max_tokens') . "\n";
echo "</pre>";

if (!$apiKey || $apiKey === 'your_deepseek_api_key_here') {
    echo "<p style='color: red;'>⚠️ 请先在 .env 文件中配置 DEEPSEEK_API_KEY</p>";
    echo "<p>如果使用 OpenRouter，请将 API 密钥设置为您的 OpenRouter API Key</p>";
    echo "<hr><p><a href='/'>返回首页</a></p>";
    exit;
}

// 测试连接
echo "<h2>2. 连接测试</h2>";
try {
    $api = new DeepSeekAPI();
    echo "<p>✅ DeepSeek API 初始化成功</p>";
    
    echo "<p>正在测试 API 连接...</p>";
    if ($api->testConnection()) {
        echo "<p style='color: green;'>✅ API 连接成功</p>";
    } else {
        echo "<p style='color: red;'>❌ API 连接失败</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 错误：" . $e->getMessage() . "</p>";
}

// 模拟问诊数据测试
echo "<h2>3. 诊断测试</h2>";
echo "<p>使用模拟数据进行诊断测试...</p>";

$testData = [
    'patient_name' => '测试患者',
    'age' => 35,
    'gender' => '男',
    'height' => 175,
    'weight' => 70,
    'chief_complaint' => '失眠多梦，心烦易怒，口干口苦',
    'present_illness' => '患者近一个月来出现失眠多梦，每晚入睡困难，睡后易醒，醒后难以再次入睡。伴有心烦易怒，遇事急躁，口干口苦，大便偏干。',
    'past_history' => '既往体健，否认高血压、糖尿病等慢性病史',
    'family_history' => '父母体健',
    'complexion' => '面色偏红',
    'spirit' => '精神疲倦，烦躁',
    'body_shape' => '体型适中',
    'tongue_body' => '舌红',
    'tongue_coating' => '苔黄薄',
    'pulse' => '脉弦数',
    'voice' => '声音正常',
    'breath' => '呼吸平稳',
    'sleep' => '入睡困难，多梦易醒',
    'appetite' => '食欲一般，喜冷饮',
    'bowel' => '大便偏干，2-3日一行',
    'urine' => '小便黄'
];

try {
    echo "<pre>";
    echo "发送诊断请求...\n";
    $startTime = microtime(true);
    
    $result = $api->diagnose($testData);
    
    $duration = round(microtime(true) - $startTime, 2);
    echo "请求耗时：{$duration} 秒\n";
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h3 style='color: green;'>诊断成功</h3>";
        echo "<div style='background: #f0f0f0; padding: 20px; margin: 10px 0;'>";
        echo "<pre style='white-space: pre-wrap;'>" . htmlspecialchars($result['diagnosis']) . "</pre>";
        echo "</div>";
        
        if (isset($result['usage'])) {
            echo "<p>Token 使用情况：";
            echo "Prompt: {$result['usage']['prompt_tokens']}, ";
            echo "Completion: {$result['usage']['completion_tokens']}, ";
            echo "Total: {$result['usage']['total_tokens']}</p>";
        }
    } else {
        echo "<h3 style='color: red;'>诊断失败</h3>";
        echo "<p>错误信息：" . htmlspecialchars($result['error']) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 诊断过程出错：" . $e->getMessage() . "</p>";
}

// 显示 API 调用日志
echo "<h2>4. API 调用日志</h2>";
try {
    $logger = new ApiLogger();
    $recentCalls = $logger->getRecentCalls(5);
    
    if (empty($recentCalls)) {
        echo "<p>暂无 API 调用记录</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>时间</th><th>服务</th><th>状态</th><th>耗时</th><th>内存</th></tr>";
        
        foreach ($recentCalls as $call) {
            $status = $call['success'] ? '✅ 成功' : '❌ 失败';
            $statusColor = $call['success'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$call['timestamp']}</td>";
            echo "<td>{$call['service']}</td>";
            echo "<td style='color: {$statusColor};'>{$status}</td>";
            echo "<td>{$call['duration']}s</td>";
            echo "<td>{$call['memory_usage']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 显示统计信息
    $stats = $logger->getStatistics();
    echo "<h3>API 调用统计</h3>";
    echo "<pre>";
    echo "总调用次数：{$stats['total_calls']}\n";
    echo "成功次数：{$stats['successful_calls']}\n";
    echo "失败次数：{$stats['failed_calls']}\n";
    echo "成功率：{$stats['success_rate']}%\n";
    echo "平均耗时：{$stats['average_duration']}s\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p>无法读取日志：" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/'>返回首页</a> | <a href='/test-config.php'>配置测试</a></p>"; 