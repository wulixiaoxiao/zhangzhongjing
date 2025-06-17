<?php
/**
 * 数据库连接和模型测试
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

// 加载辅助函数
require_once ROOT_PATH . 'src/helpers.php';

use App\Core\Database;

echo "<h1>数据库连接和模型测试</h1>";

// 1. 测试数据库连接
echo "<h2>1. 数据库连接测试</h2>";
echo "<pre>";
try {
    $pdo = Database::connection();
    echo "✅ 数据库连接成功\n";
    echo "数据库类型: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "服务器版本: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "连接状态: " . ($pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) ?: '已连接') . "\n";
} catch (Exception $e) {
    echo "❌ 数据库连接失败: " . $e->getMessage() . "\n";
}
echo "</pre>";

// 2. 测试表信息查询
echo "<h2>2. 数据库表信息</h2>";
try {
    $tables = Database::select("SHOW TABLES");
    echo "<p>数据库中的表：</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 查询表信息失败: " . $e->getMessage() . "</p>";
}

// 3. 测试基础查询功能
echo "<h2>3. 基础查询测试</h2>";
echo "<pre>";
try {
    // 测试 SELECT
    echo "测试 SELECT 查询:\n";
    $patients = Database::select("SELECT * FROM patients LIMIT 3");
    echo "找到 " . count($patients) . " 条患者记录\n";
    
    // 测试 SELECT ONE
    echo "\n测试 SELECT ONE 查询:\n";
    $patient = Database::selectOne("SELECT * FROM patients WHERE id = ?", [1]);
    if ($patient) {
        echo "患者姓名: " . $patient['name'] . "\n";
    } else {
        echo "未找到 ID=1 的患者\n";
    }
    
    // 测试事务
    echo "\n测试事务功能:\n";
    Database::beginTransaction();
    echo "✅ 开始事务\n";
    Database::rollback();
    echo "✅ 回滚事务\n";
    
} catch (Exception $e) {
    echo "❌ 查询测试失败: " . $e->getMessage() . "\n";
}
echo "</pre>";

// 4. 创建测试模型
echo "<h2>4. 模型功能测试</h2>";

// 定义一个测试模型
class TestPatient extends \App\Core\Model {
    protected $table = 'patients';
    
    protected $fillable = [
        'name', 'gender', 'birth_date', 'phone', 
        'id_card', 'address', 'emergency_contact', 
        'emergency_phone', 'medical_history', 'allergy_history'
    ];
    
    protected $casts = [
        'id' => 'integer',
        'birth_date' => 'date'
    ];
    
    protected $rules = [
        'name' => 'required|max:50',
        'gender' => 'required|in:男,女',
        'phone' => 'max:20',
        'id_card' => 'max:18'
    ];
}

echo "<pre>";
try {
    // 测试查找所有
    echo "测试 Model::all():\n";
    $patients = TestPatient::all([], ['id' => 'ASC'], 3);
    echo "找到 " . count($patients) . " 条患者记录\n";
    
    // 测试根据主键查找
    echo "\n测试 Model::find():\n";
    $patient = TestPatient::find(1);
    if ($patient) {
        echo "患者信息:\n";
        echo "- ID: " . $patient->id . "\n";
        echo "- 姓名: " . $patient->name . "\n";
        echo "- 性别: " . $patient->gender . "\n";
        echo "- 出生日期: " . $patient->birth_date . "\n";
    } else {
        echo "未找到 ID=1 的患者\n";
    }
    
    // 测试条件查找
    echo "\n测试 Model::findWhere():\n";
    $femalePatient = TestPatient::findWhere(['gender' => '女']);
    if ($femalePatient) {
        echo "找到女性患者: " . $femalePatient->name . "\n";
    } else {
        echo "未找到女性患者\n";
    }
    
    // 测试验证功能
    echo "\n测试模型验证:\n";
    $newPatient = new TestPatient([
        'name' => '',  // 故意留空以触发验证错误
        'gender' => '未知',  // 无效值
        'phone' => '12345678901234567890123456'  // 超长
    ]);
    
    if ($newPatient->validate()) {
        echo "✅ 验证通过\n";
    } else {
        echo "❌ 验证失败:\n";
        foreach ($newPatient->getErrors() as $field => $errors) {
            foreach ($errors as $error) {
                echo "  - $error\n";
            }
        }
    }
    
    // 测试数据转换
    echo "\n测试数据转换:\n";
    if ($patient) {
        $array = $patient->toArray();
        echo "转换为数组: " . count($array) . " 个字段\n";
        echo "JSON 格式:\n" . json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ 模型测试失败: " . $e->getMessage() . "\n";
}
echo "</pre>";

// 5. 连接池测试
echo "<h2>5. 连接池测试</h2>";
echo "<pre>";
try {
    // 获取默认连接
    $conn1 = Database::connection();
    $conn2 = Database::connection();
    echo "默认连接是否相同: " . ($conn1 === $conn2 ? '✅ 是（连接池有效）' : '❌ 否') . "\n";
    
    // 断开连接
    Database::disconnect();
    $conn3 = Database::connection();
    echo "断开后重新连接: " . ($conn1 !== $conn3 ? '✅ 新连接' : '❌ 旧连接') . "\n";
    
} catch (Exception $e) {
    echo "❌ 连接池测试失败: " . $e->getMessage() . "\n";
}
echo "</pre>";

// 6. 性能测试
echo "<h2>6. 性能测试</h2>";
echo "<pre>";
$startTime = microtime(true);
try {
    for ($i = 0; $i < 10; $i++) {
        Database::selectOne("SELECT 1");
    }
    $duration = round(microtime(true) - $startTime, 3);
    echo "执行 10 次查询耗时: {$duration} 秒\n";
    echo "平均每次查询: " . round($duration / 10 * 1000, 2) . " 毫秒\n";
} catch (Exception $e) {
    echo "❌ 性能测试失败: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='/'>返回首页</a> | <a href='/test-config.php'>配置测试</a> | <a href='/test-api.php'>API 测试</a></p>"; 