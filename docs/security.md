# 中医智能问诊系统 - 安全配置指南

## 概述

本文档描述了系统的安全功能和配置指南，以确保应用程序的安全性。

## 已实现的安全功能

### 1. SQL 注入防护

系统使用参数化查询防止 SQL 注入攻击：

- 所有数据库操作使用 PDO 预处理语句
- 参数绑定确保用户输入不会被解释为 SQL 代码
- Database 类自动处理参数转义

```php
// 安全的查询示例
$sql = "SELECT * FROM patients WHERE id = ?";
$result = Database::selectOne($sql, [$id]);
```

### 2. XSS（跨站脚本）防护

- 自动转义所有输出到 HTML 的数据
- 提供多个转义函数：`e()`, `escape()`, `Security::escape()`
- HTML 内容清理功能：`clean_html()`
- XSS 风险检测：`Security::hasXssRisk()`

```php
// 在视图中使用
<?php echo e($user_input); ?>

// 清理 HTML 内容
$clean = clean_html($html_content);
```

### 3. CSRF（跨站请求伪造）防护

- 自动生成和验证 CSRF Token
- Token 有效期：2小时
- 所有 POST 请求需要验证 CSRF Token

```php
// 在表单中添加 CSRF 字段
<?php echo csrf_field(); ?>

// 在控制器中验证
$this->requireCsrf();
```

### 4. 数据加密

- 使用 AES-256-CBC 加密敏感数据
- 自动生成和管理加密密钥
- 提供加密/解密辅助函数

```php
// 加密数据
$encrypted = encrypt($sensitive_data);

// 解密数据
$decrypted = decrypt($encrypted);
```

### 5. 密码安全

- 使用 bcrypt 算法哈希密码（cost factor: 12）
- 提供密码验证功能
- 密码策略配置支持

```php
// 哈希密码
$hash = hash_password($password);

// 验证密码
if (verify_password($password, $hash)) {
    // 密码正确
}
```

### 6. 文件上传安全

- 文件名清理，防止目录遍历攻击
- 文件类型限制
- 文件大小限制（默认 10MB）

```php
// 清理文件名
$safe_filename = sanitize_filename($uploaded_filename);
```

### 7. 会话安全

- 支持 HTTPOnly Cookie
- 支持 Secure Cookie（HTTPS）
- 支持 SameSite 属性

### 8. API 安全

- API 密钥验证
- 请求频率限制配置
- 安全日志记录

## 安全配置

### 1. 环境变量配置

在 `.env` 文件中配置以下安全相关变量：

```env
# 应用密钥（用于加密）
APP_KEY=your-32-character-random-string

# API 密钥
APP_API_KEY=your-api-key

# 会话配置
SESSION_SECURE_COOKIE=true  # 仅在 HTTPS 环境启用
SESSION_SAME_SITE=Lax

# 调试模式（生产环境必须设置为 false）
APP_DEBUG=false
```

### 2. Apache 安全配置

`.htaccess` 文件已配置以下安全措施：

- 防止目录浏览
- 限制 PHP 文件执行
- 安全响应头设置
- 内容安全策略（CSP）
- 防止热链接

### 3. PHP 配置建议

在 `php.ini` 中建议的安全配置：

```ini
; 会话安全
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = Lax

; 文件上传
upload_max_filesize = 10M
post_max_size = 10M

; 其他安全设置
expose_php = Off
display_errors = Off
log_errors = On
```

### 4. 数据库安全

- 使用最小权限原则配置数据库用户
- 定期备份数据库
- 使用 SSL 连接（如果可能）

## 安全最佳实践

### 1. 输入验证

始终验证和清理用户输入：

```php
// 使用 Validator 类
$validator = new Validator();
$validator->validate($data, [
    'email' => 'required|email',
    'age' => 'required|integer|between:1,150'
]);
```

### 2. 输出转义

始终转义输出到浏览器的数据：

```php
// 在视图中
<?php echo e($data); ?>

// 或使用辅助函数
{{ escape($data) }}
```

### 3. 敏感数据处理

- 不要在日志中记录敏感信息
- 使用加密存储敏感数据
- 及时清理不需要的敏感数据

### 4. 错误处理

- 生产环境不显示详细错误信息
- 记录错误到日志文件
- 提供友好的错误页面

### 5. 定期安全审计

- 定期检查安全日志
- 更新依赖包
- 进行安全扫描
- 代码审查

## 安全事件响应

如果发生安全事件：

1. 立即记录事件详情
2. 评估影响范围
3. 采取紧急措施（如暂时下线）
4. 修复漏洞
5. 通知相关人员
6. 总结经验教训

## 安全测试

使用提供的测试页面进行安全功能验证：

```bash
# 访问安全测试页面
http://localhost:8000/test-security.php
```

该页面测试以下功能：
- CSRF Token 生成和验证
- XSS 防护
- SQL 注入防护
- 数据加密/解密
- 密码哈希
- 文件名清理
- 客户端信息获取
- 安全响应头
- 会话安全配置
- API 密钥验证

## 更新日志

- 2024-01-17：初始安全实现
  - 添加 Security 类
  - 实现 CSRF 保护
  - 实现 XSS 防护
  - 实现数据加密
  - 更新控制器基类
  - 添加安全辅助函数
  - 创建安全测试页面 