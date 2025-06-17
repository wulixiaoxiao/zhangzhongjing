# 数据库管理文档

## 概述

系统使用 PDO 实现数据库连接，提供了完整的数据库操作和 ORM 功能。

## 核心组件

### 1. Database 类 (`app/Core/Database.php`)

数据库连接管理类，主要功能：
- PDO 连接池管理
- 事务支持
- 基础查询方法
- 连接健康检查

### 2. Model 类 (`app/Core/Model.php`)

基础模型类，提供：
- CRUD 操作
- 数据验证
- 属性类型转换
- 批量赋值保护
- 时间戳自动管理

## 使用方法

### 直接使用 Database 类

```php
use App\Core\Database;

// 执行查询
$users = Database::select("SELECT * FROM users WHERE status = ?", ['active']);

// 获取单条记录
$user = Database::selectOne("SELECT * FROM users WHERE id = ?", [1]);

// 插入数据
$id = Database::insert("INSERT INTO users (name, email) VALUES (?, ?)", ['张三', 'zhang@example.com']);

// 更新数据
$affected = Database::update("UPDATE users SET name = ? WHERE id = ?", ['李四', 1]);

// 删除数据
$deleted = Database::delete("DELETE FROM users WHERE id = ?", [1]);

// 事务操作
Database::beginTransaction();
try {
    // 执行多个操作
    Database::insert(...);
    Database::update(...);
    Database::commit();
} catch (Exception $e) {
    Database::rollback();
    throw $e;
}
```

### 使用 Model 类

#### 定义模型

```php
use App\Core\Model;

class Patient extends Model
{
    // 表名
    protected $table = 'patients';
    
    // 可批量赋值的字段
    protected $fillable = [
        'name', 'gender', 'birth_date', 'phone', 
        'id_card', 'address', 'emergency_contact'
    ];
    
    // 隐藏字段（不在 JSON/数组中显示）
    protected $hidden = ['password'];
    
    // 类型转换
    protected $casts = [
        'id' => 'integer',
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'settings' => 'json'
    ];
    
    // 验证规则
    protected $rules = [
        'name' => 'required|max:50',
        'gender' => 'required|in:男,女',
        'phone' => 'required|max:20',
        'id_card' => 'unique|max:18'
    ];
}
```

#### 基本操作

```php
// 创建新记录
$patient = Patient::create([
    'name' => '王五',
    'gender' => '男',
    'birth_date' => '1990-01-01',
    'phone' => '13800138000'
]);

// 查找记录
$patient = Patient::find(1);
$patients = Patient::all();
$activePatients = Patient::all(['status' => 'active']);

// 条件查找
$patient = Patient::findWhere(['phone' => '13800138000']);

// 更新记录
$patient->name = '新名字';
$patient->save();

// 删除记录
$patient->delete();

// 批量操作
$patients = Patient::all(['gender' => '女'], ['name' => 'ASC'], 10);
```

## 数据验证

### 支持的验证规则

- `required` - 必填
- `string` - 字符串类型
- `numeric` - 数字类型
- `email` - 邮箱格式
- `min:n` - 最小长度
- `max:n` - 最大长度
- `in:value1,value2` - 枚举值
- `unique` - 唯一性检查

### 使用验证

```php
$patient = new Patient($data);

if ($patient->validate()) {
    $patient->save();
} else {
    $errors = $patient->getErrors();
    // 处理错误
}
```

## 类型转换

支持的类型：
- `int/integer` - 整数
- `float/double` - 浮点数
- `string` - 字符串
- `bool/boolean` - 布尔值
- `array` - 数组（JSON 存储）
- `json` - JSON 对象
- `datetime` - 日期时间
- `date` - 日期

## 时间戳管理

默认情况下，模型会自动管理 `created_at` 和 `updated_at` 字段。

```php
// 禁用时间戳
protected $timestamps = false;

// 自定义时间戳字段名
protected $createdField = 'create_time';
protected $updatedField = 'update_time';
```

## 连接池管理

系统自动管理数据库连接池，确保连接复用和健康检查。

```php
// 获取连接（自动复用）
$pdo = Database::connection();

// 断开连接
Database::disconnect();

// 断开所有连接
Database::disconnectAll();
```

## 配置

数据库配置在 `.env` 文件中设置：

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=yisheng_db
DB_USER=root
DB_PASS=password
DB_CHARSET=utf8mb4
```

## 测试

访问 `/test-db.php` 进行数据库测试：
- 连接测试
- 表信息查询
- 基础 CRUD 测试
- 模型功能测试
- 连接池测试
- 性能测试

## 最佳实践

1. **使用参数绑定** - 始终使用参数绑定防止 SQL 注入
2. **事务处理** - 对多个相关操作使用事务
3. **模型验证** - 在保存前验证数据
4. **批量赋值保护** - 使用 `fillable` 或 `guarded`
5. **类型转换** - 定义 `casts` 确保数据类型正确
6. **错误处理** - 捕获并处理数据库异常

## 故障排查

### 常见问题

1. **连接失败**
   - 检查数据库服务是否运行
   - 验证连接参数是否正确
   - 确认防火墙设置

2. **字符集问题**
   - 确保数据库使用 `utf8mb4` 字符集
   - 检查表和字段的字符集设置

3. **性能问题**
   - 使用索引优化查询
   - 避免 N+1 查询问题
   - 使用连接池复用连接

4. **事务死锁**
   - 保持事务简短
   - 按相同顺序访问资源
   - 使用适当的隔离级别 