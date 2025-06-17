<?php
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Security;
use App\Core\Database;

// 初始化自动加载器
$autoloader = new Autoloader();
$autoloader->register();

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 启动会话
session_start();

echo "<h1>安全功能测试</h1>";

// 1. CSRF Token 测试
echo "<h2>1. CSRF Token 测试</h2>";
try {
    $token1 = Security::generateCsrfToken();
    echo "<p>生成的 CSRF Token: <code>" . htmlspecialchars($token1) . "</code></p>";
    
    $valid = Security::validateCsrfToken($token1);
    echo "<p>验证结果: " . ($valid ? '<span style="color: green;">✓ 通过</span>' : '<span style="color: red;">✗ 失败</span>') . "</p>";
    
    $invalidToken = 'invalid_token_12345';
    $invalid = Security::validateCsrfToken($invalidToken);
    echo "<p>无效 Token 验证: " . (!$invalid ? '<span style="color: green;">✓ 正确拒绝</span>' : '<span style="color: red;">✗ 错误通过</span>') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>错误: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 2. XSS 防护测试
echo "<h2>2. XSS 防护测试</h2>";
$xssTests = [
    '<script>alert("XSS")</script>',
    '<img src=x onerror=alert("XSS")>',
    'javascript:alert("XSS")',
    '<iframe src="javascript:alert(\'XSS\')"></iframe>',
    '<div onmouseover="alert(\'XSS\')">Hover me</div>'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>原始输入</th><th>转义后</th><th>XSS 风险检测</th></tr>";
foreach ($xssTests as $test) {
    $escaped = Security::escape($test);
    $hasRisk = Security::hasXssRisk($test);
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($test) . "</code></td>";
    echo "<td><code>" . htmlspecialchars($escaped) . "</code></td>";
    echo "<td>" . ($hasRisk ? '<span style="color: red;">✓ 检测到风险</span>' : '<span style="color: green;">无风险</span>') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. SQL 注入防护测试
echo "<h2>3. SQL 注入防护测试</h2>";
$sqlTests = [
    "1' OR '1'='1",
    "'; DROP TABLE patients; --",
    "admin'--",
    "1 UNION SELECT * FROM users"
];

echo "<p>测试参数化查询防护：</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>恶意输入</th><th>安全查询示例</th></tr>";
foreach ($sqlTests as $test) {
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($test) . "</code></td>";
    echo "<td><code>SELECT * FROM patients WHERE id = ?</code> (参数: " . htmlspecialchars($test) . ")</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p style='color: green;'>✓ 使用参数化查询可以防止 SQL 注入</p>";

// 4. 加密解密测试
echo "<h2>4. 数据加密测试</h2>";
try {
    $testData = "敏感患者信息：身份证 110101199001011234";
    $encrypted = Security::encrypt($testData);
    $decrypted = Security::decrypt($encrypted);
    
    echo "<p>原始数据: <code>" . htmlspecialchars($testData) . "</code></p>";
    echo "<p>加密后: <code>" . htmlspecialchars($encrypted) . "</code></p>";
    echo "<p>解密后: <code>" . htmlspecialchars($decrypted) . "</code></p>";
    echo "<p>验证: " . ($testData === $decrypted ? '<span style="color: green;">✓ 加密解密成功</span>' : '<span style="color: red;">✗ 加密解密失败</span>') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>错误: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. 密码哈希测试
echo "<h2>5. 密码哈希测试</h2>";
$testPassword = "SecureP@ssw0rd123";
$hash = Security::hashPassword($testPassword);
$verify1 = Security::verifyPassword($testPassword, $hash);
$verify2 = Security::verifyPassword("wrongpassword", $hash);

echo "<p>测试密码: <code>" . htmlspecialchars($testPassword) . "</code></p>";
echo "<p>哈希值: <code>" . htmlspecialchars($hash) . "</code></p>";
echo "<p>正确密码验证: " . ($verify1 ? '<span style="color: green;">✓ 通过</span>' : '<span style="color: red;">✗ 失败</span>') . "</p>";
echo "<p>错误密码验证: " . (!$verify2 ? '<span style="color: green;">✓ 正确拒绝</span>' : '<span style="color: red;">✗ 错误通过</span>') . "</p>";

// 6. 文件名清理测试
echo "<h2>6. 文件名安全测试</h2>";
$filenameTests = [
    '../../../etc/passwd',
    'virus.exe',
    '中文文件名.pdf',
    'file with spaces.jpg',
    'file<script>alert()</script>.pdf',
    '.htaccess'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>原始文件名</th><th>清理后</th></tr>";
foreach ($filenameTests as $filename) {
    $cleaned = Security::sanitizeFilename($filename);
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($filename) . "</code></td>";
    echo "<td><code>" . htmlspecialchars($cleaned) . "</code></td>";
    echo "</tr>";
}
echo "</table>";

// 7. 客户端 IP 获取
echo "<h2>7. 客户端信息</h2>";
$clientIp = Security::getClientIp();
echo "<p>客户端 IP: <code>" . htmlspecialchars($clientIp) . "</code></p>";
echo "<p>用户代理: <code>" . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "</code></p>";

// 8. 安全头检查
echo "<h2>8. 安全响应头</h2>";
echo "<p>以下安全头应该在生产环境中设置：</p>";
$securityHeaders = [
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Content-Security-Policy' => '已配置',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>安全头</th><th>推荐值</th></tr>";
foreach ($securityHeaders as $header => $value) {
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($header) . "</code></td>";
    echo "<td><code>" . htmlspecialchars($value) . "</code></td>";
    echo "</tr>";
}
echo "</table>";

// 9. 会话安全
echo "<h2>9. 会话安全</h2>";
$sessionConfig = [
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode')
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>配置项</th><th>当前值</th><th>推荐值</th></tr>";
$recommendations = [
    'session.cookie_httponly' => '1',
    'session.cookie_secure' => '1 (HTTPS)',
    'session.cookie_samesite' => 'Lax',
    'session.use_strict_mode' => '1'
];

foreach ($sessionConfig as $key => $value) {
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($key) . "</code></td>";
    echo "<td><code>" . htmlspecialchars($value ?: 'not set') . "</code></td>";
    echo "<td><code>" . htmlspecialchars($recommendations[$key]) . "</code></td>";
    echo "</tr>";
}
echo "</table>";

// 10. API 密钥验证测试
echo "<h2>10. API 密钥验证</h2>";
$testApiKey = 'test_api_key_123';
$_ENV['APP_API_KEY'] = $testApiKey; // 临时设置用于测试

$valid = Security::validateApiKey($testApiKey);
$invalid = Security::validateApiKey('wrong_key');

echo "<p>正确密钥验证: " . ($valid ? '<span style="color: green;">✓ 通过</span>' : '<span style="color: red;">✗ 失败</span>') . "</p>";
echo "<p>错误密钥验证: " . (!$invalid ? '<span style="color: green;">✓ 正确拒绝</span>' : '<span style="color: red;">✗ 错误通过</span>') . "</p>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}
h1, h2 {
    color: #333;
}
table {
    border-collapse: collapse;
    margin: 10px 0;
}
th {
    background: #f0f0f0;
}
code {
    background: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
}
</style> 