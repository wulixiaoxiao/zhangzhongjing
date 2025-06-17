<?php
/**
 * 环境变量设置脚本
 * 帮助用户快速设置 .env 文件
 */

echo "=== 中医智能问诊系统 - 环境配置 ===\n\n";

$rootPath = dirname(__DIR__);
$envExample = $rootPath . '/.env.example';
$envFile = $rootPath . '/.env';

// 检查 .env 文件是否已存在
if (file_exists($envFile)) {
    echo "警告：.env 文件已存在。是否要覆盖？(y/N): ";
    $answer = trim(fgets(STDIN));
    if (strtolower($answer) !== 'y') {
        echo "操作已取消。\n";
        exit(0);
    }
}

// 复制 .env.example 到 .env
if (!file_exists($envExample)) {
    echo "错误：找不到 .env.example 文件。\n";
    exit(1);
}

$content = file_get_contents($envExample);

echo "请配置以下参数（按回车使用默认值）：\n\n";

// 数据库配置
echo "数据库主机 [localhost]: ";
$dbHost = trim(fgets(STDIN)) ?: 'localhost';

echo "数据库端口 [3306]: ";
$dbPort = trim(fgets(STDIN)) ?: '3306';

echo "数据库名称 [yisheng_db]: ";
$dbName = trim(fgets(STDIN)) ?: 'yisheng_db';

echo "数据库用户 [root]: ";
$dbUser = trim(fgets(STDIN)) ?: 'root';

echo "数据库密码 []: ";
$dbPass = trim(fgets(STDIN));

// DeepSeek API 配置
echo "\nDeepSeek API 密钥（留空稍后配置）: ";
$deepseekKey = trim(fgets(STDIN));

// 替换配置值
$replacements = [
    'DB_HOST=localhost' => "DB_HOST=$dbHost",
    'DB_PORT=3306' => "DB_PORT=$dbPort",
    'DB_NAME=yisheng_db' => "DB_NAME=$dbName",
    'DB_USER=root' => "DB_USER=$dbUser",
    'DB_PASS=' => "DB_PASS=$dbPass",
    'DEEPSEEK_API_KEY=your_deepseek_api_key_here' => $deepseekKey ? "DEEPSEEK_API_KEY=$deepseekKey" : 'DEEPSEEK_API_KEY=',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// 写入 .env 文件
if (file_put_contents($envFile, $content)) {
    echo "\n✓ .env 文件已创建成功！\n";
    echo "  文件位置：$envFile\n";
    
    // 设置文件权限
    chmod($envFile, 0644);
    
    echo "\n提示：\n";
    echo "1. 请确保 DeepSeek API 密钥已正确配置\n";
    echo "2. 可以直接编辑 .env 文件修改配置\n";
    echo "3. 不要将 .env 文件提交到版本控制系统\n";
} else {
    echo "\n✗ 创建 .env 文件失败！\n";
    exit(1);
}

echo "\n"; 