<?php
/**
 * 环境测试脚本 - 检查系统配置是否正确
 */

// 启动输出缓冲
ob_start();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>环境测试</title></head><body>\n";
echo "<h1>中医问诊系统 - 环境测试</h1>\n";

// 1. 检查 putenv 函数
echo "<h2>1. putenv() 函数检查</h2>\n";
if (function_exists('putenv')) {
    if (in_array('putenv', explode(',', ini_get('disable_functions')))) {
        echo "<p style='color: orange;'>⚠️ putenv() 函数被禁用，但系统已做兼容处理</p>\n";
    } else {
        echo "<p style='color: green;'>✅ putenv() 函数可用</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ putenv() 函数不存在</p>\n";
}

// 2. 检查会话
echo "<h2>2. 会话状态检查</h2>\n";
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ 会话已成功启动</p>\n";
    echo "<p>会话ID: " . session_id() . "</p>\n";
    echo "<p>会话保存路径: " . session_save_path() . "</p>\n";
} else {
    echo "<p style='color: red;'>❌ 会话启动失败</p>\n";
}

// 3. 检查目录权限
echo "<h2>3. 目录权限检查</h2>\n";
$dirs = [
    '../storage' => '存储目录',
    '../storage/logs' => '日志目录',
    '../storage/sessions' => '会话目录',
    '../storage/cache' => '缓存目录'
];

foreach ($dirs as $dir => $name) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✅ {$name} ({$dir}) - 可写</p>\n";
        } else {
            echo "<p style='color: red;'>❌ {$name} ({$dir}) - 不可写</p>\n";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ {$name} ({$dir}) - 不存在</p>\n";
    }
}

// 4. 检查错误日志设置
echo "<h2>4. 错误日志设置</h2>\n";
echo "<p>错误报告级别: " . error_reporting() . "</p>\n";
echo "<p>显示错误: " . (ini_get('display_errors') ? '是' : '否') . "</p>\n";
echo "<p>记录错误: " . (ini_get('log_errors') ? '是' : '否') . "</p>\n";
echo "<p>错误日志文件: " . ini_get('error_log') . "</p>\n";

// 5. 检查环境变量
echo "<h2>5. 环境变量检查</h2>\n";
$env = getenv('APP_ENV') ?: '未设置';
echo "<p>APP_ENV: {$env}</p>\n";
if ($env === 'production') {
    echo "<p style='color: green;'>✅ 生产环境配置已启用</p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ 当前不是生产环境</p>\n";
}

// 6. 输出缓冲状态
echo "<h2>6. 输出缓冲</h2>\n";
$ob_level = ob_get_level();
echo "<p>输出缓冲级别: {$ob_level}</p>\n";
if ($ob_level > 0) {
    echo "<p style='color: green;'>✅ 输出缓冲已启用</p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ 输出缓冲未启用</p>\n";
}

echo "</body></html>\n";

// 刷新输出缓冲
ob_end_flush(); 