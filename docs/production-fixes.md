# 生产环境问题修复说明

## 问题描述

在生产环境 `/www/wwwroot/zzj.aha233.com/` 中遇到了两个PHP警告：

1. `Warning: putenv() has been disabled for security reasons`
2. `Warning: session_start(): Cannot start session when headers already sent`

## 解决方案

### 1. 修复 putenv() 被禁用的问题

**文件**: `app/Core/Config.php`

**修改内容**：
```php
// 检查 putenv 是否可用
if (function_exists('putenv') && !in_array('putenv', explode(',', ini_get('disable_functions')))) {
    @putenv("$key=$value");
}
```

**说明**：
- 在调用 `putenv()` 前检查函数是否存在且未被禁用
- 使用 `@` 抑制可能的警告
- 即使 `putenv()` 不可用，系统仍可通过 `$_ENV` 和 `$_SERVER` 数组正常工作

### 2. 修复 session_start() 警告

**文件**: `public/index.php`

**修改内容**：
1. 在文件开头添加输出缓冲：
   ```php
   // 启动输出缓冲（防止意外输出影响header和session）
   ob_start();
   ```

2. 将 session_start() 移到加载配置之前：
   ```php
   // 启动会话（在任何输出之前）
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }
   ```

3. 在文件末尾刷新输出缓冲：
   ```php
   // 刷新输出缓冲
   ob_end_flush();
   ```

## 额外的生产环境优化

### 1. 环境检测和配置

添加了环境检测逻辑，根据 `APP_ENV` 环境变量自动应用不同的配置：

```php
$environment = getenv('APP_ENV') ?: 'production';

if ($environment === 'production') {
    // 禁用错误显示
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'storage/logs/php_errors.log');
}
```

### 2. 会话安全配置

为生产环境添加了会话安全设置：

```php
if ($environment === 'production') {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    // 如果使用HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
}
```

### 3. 目录保护

创建了 `storage/.htaccess` 文件防止直接访问敏感目录：

```apache
# 禁止直接访问
Order deny,allow
Deny from all

# 对于Apache 2.4+
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
```

## 测试和验证

1. 访问 `/test-environment.php` 检查环境配置
2. 确认没有PHP警告显示
3. 验证会话功能正常
4. 检查错误日志路径是否正确

## 部署步骤

1. 上传修改后的文件到服务器
2. 设置正确的目录权限：
   ```bash
   chmod -R 755 storage/
   chmod -R 777 storage/cache/
   chmod -R 777 storage/sessions/
   chmod -R 777 storage/logs/
   ```
3. 在 `.env` 文件中设置 `APP_ENV=production`
4. 重启 PHP-FPM 服务（如果使用）
5. 测试系统功能

## 注意事项

- 定期检查 `storage/logs/php_errors.log` 文件
- 确保数据库连接信息正确
- 建议使用专用数据库用户而非 root
- 启用 HTTPS 以提高安全性 