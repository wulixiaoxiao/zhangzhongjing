<?php
/**
 * 配置管理系统测试
 */

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

// 加载配置
\App\Core\Config::load();

// 测试配置管理器
echo "<h1>配置管理系统测试</h1>";

// 1. 测试环境变量加载
echo "<h2>1. 环境变量测试</h2>";
echo "<pre>";
echo "APP_NAME: " . env('APP_NAME', '未设置') . "\n";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "DB_HOST: " . env('DB_HOST', 'localhost') . "\n";
echo "DB_NAME: " . env('DB_NAME', 'yisheng_db') . "\n";
echo "DEEPSEEK_API_KEY: " . (env('DEEPSEEK_API_KEY') ? '已设置' : '未设置') . "\n";
echo "</pre>";

// 2. 测试配置文件加载
echo "<h2>2. 配置文件测试</h2>";
echo "<pre>";
echo "应用名称: " . config('app.name') . "\n";
echo "调试模式: " . (config('app.debug') ? '开启' : '关闭') . "\n";
echo "数据库主机: " . config('app.database.host') . "\n";
echo "数据库名称: " . config('app.database.name') . "\n";
echo "DeepSeek API: " . (config('app.deepseek.api_key') ? '已配置' : '未配置') . "\n";
echo "</pre>";

// 3. 测试日志配置
echo "<h2>3. 日志配置测试</h2>";
echo "<pre>";
echo "默认日志通道: " . config('logging.default') . "\n";
echo "日志级别: " . config('logging.channels.file.level') . "\n";
echo "日志路径: " . config('logging.channels.file.path') . "\n";
echo "</pre>";

// 4. 测试缓存配置  
echo "<h2>4. 缓存配置测试</h2>";
echo "<pre>";
echo "默认缓存驱动: " . config('cache.default') . "\n";
echo "缓存路径: " . config('cache.stores.file.path') . "\n";
echo "缓存前缀: " . config('cache.prefix') . "\n";
echo "</pre>";

// 5. 测试辅助函数
echo "<h2>5. 辅助函数测试</h2>";
echo "<pre>";
echo "应用路径: " . app_path() . "\n";
echo "配置路径: " . config_path() . "\n";
echo "存储路径: " . storage_path() . "\n";
echo "公共路径: " . public_path() . "\n";
echo "</pre>";

// 6. 测试动态配置设置
echo "<h2>6. 动态配置测试</h2>";
echo "<pre>";
config(['test.key' => 'test value']);
echo "设置 test.key = 'test value'\n";
echo "读取 test.key = " . config('test.key') . "\n";
echo "</pre>";

// 7. 检查 .env 文件状态
echo "<h2>7. .env 文件状态</h2>";
echo "<pre>";
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    echo ".env 文件存在\n";
    echo "文件大小: " . filesize($envFile) . " 字节\n";
    echo "最后修改: " . date('Y-m-d H:i:s', filemtime($envFile)) . "\n";
} else {
    echo ".env 文件不存在\n";
    echo "请复制 config/env.example 到项目根目录并重命名为 .env\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='/'>返回首页</a></p>"; 