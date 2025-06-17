<?php
/**
 * 数据库初始化脚本
 * 
 * 使用方法：php database/init.php
 */

// 定义根目录
define('ROOT_PATH', dirname(__DIR__) . '/');

// 加载环境变量
if (file_exists(ROOT_PATH . '.env')) {
    $lines = file(ROOT_PATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// 数据库配置
$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4'
];

echo "中医智能问诊系统 - 数据库初始化\n";
echo "================================\n\n";

// 检查配置
echo "数据库配置：\n";
echo "- 主机: {$config['host']}:{$config['port']}\n";
echo "- 用户: {$config['user']}\n";
echo "- 密码: " . (empty($config['pass']) ? '(空)' : '******') . "\n\n";

// 连接MySQL
try {
    echo "正在连接 MySQL 服务器...\n";
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ 连接成功\n\n";
} catch (PDOException $e) {
    die("✗ 连接失败: " . $e->getMessage() . "\n");
}

// 读取SQL文件
$sqlFile = ROOT_PATH . 'database/schema.sql';
if (!file_exists($sqlFile)) {
    die("✗ SQL文件不存在: {$sqlFile}\n");
}

echo "正在读取 SQL 文件...\n";
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die("✗ 无法读取 SQL 文件\n");
}
echo "✓ 读取成功\n\n";

// 分割SQL语句
$statements = array_filter(array_map('trim', explode(';', $sql)));
$totalStatements = count($statements);
echo "找到 {$totalStatements} 条 SQL 语句\n\n";

// 执行SQL语句
$successCount = 0;
$errorCount = 0;

foreach ($statements as $index => $statement) {
    if (empty($statement)) continue;
    
    // 显示正在执行的语句类型
    $statementType = '';
    if (preg_match('/^(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|USE)\s+/i', $statement, $matches)) {
        $statementType = strtoupper($matches[1]);
    }
    
    $progress = $index + 1;
    echo "[{$progress}/{$totalStatements}] 执行 {$statementType} 语句... ";
    
    try {
        $pdo->exec($statement . ';');
        echo "✓\n";
        $successCount++;
        
        // 如果是CREATE TABLE语句，显示表名
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
            echo "    └─ 创建表: {$matches[1]}\n";
        }
    } catch (PDOException $e) {
        echo "✗\n";
        echo "    └─ 错误: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n================================\n";
echo "执行完成！\n";
echo "- 成功: {$successCount} 条\n";
echo "- 失败: {$errorCount} 条\n\n";

// 验证数据库和表
try {
    echo "验证数据库结构...\n";
    
    // 检查数据库
    $result = $pdo->query("SELECT DATABASE()");
    $currentDb = $result->fetchColumn();
    echo "- 当前数据库: {$currentDb}\n";
    
    // 检查表
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "- 已创建的表 (" . count($tables) . " 个):\n";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo "  └─ {$table} ({$count} 条记录)\n";
    }
    
    echo "\n✓ 数据库初始化成功！\n";
} catch (PDOException $e) {
    echo "✗ 验证失败: " . $e->getMessage() . "\n";
}

// 创建测试数据（可选）
echo "\n是否要插入测试数据？(y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) === 'y' || trim($line) === 'Y') {
    echo "\n正在插入测试数据...\n";
    
    try {
        // 插入测试患者
        $stmt = $pdo->prepare("INSERT INTO patients (name, gender, age, phone, occupation) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['张三', '男', 35, '13800138000', '教师']);
        $patientId = $pdo->lastInsertId();
        echo "✓ 创建测试患者 (ID: {$patientId})\n";
        
        // 插入测试问诊记录
        $consultationNo = 'C' . date('YmdHis');
        $stmt = $pdo->prepare("INSERT INTO consultations (patient_id, consultation_no, chief_complaint, tongue_body, tongue_coating, pulse_diagnosis) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patientId, $consultationNo, '头晕乏力2周，伴食欲不振', '淡红', '薄白', '弦细']);
        $consultationId = $pdo->lastInsertId();
        echo "✓ 创建测试问诊记录 (ID: {$consultationId})\n";
        
        echo "\n测试数据插入成功！\n";
    } catch (PDOException $e) {
        echo "✗ 插入测试数据失败: " . $e->getMessage() . "\n";
    }
}

echo "\n初始化脚本执行完毕。\n"; 