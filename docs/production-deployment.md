# 生产环境部署指南

## 解决的问题

1. **putenv() 函数被禁用**
   - 已修改 `Config.php`，在调用 `putenv()` 前检查函数是否可用
   - 如果不可用，系统仍可通过 `$_ENV` 和 `$_SERVER` 正常工作

2. **session_start() 警告**
   - 添加输出缓冲防止意外输出
   - 将 session_start() 移到文件顶部
   - 添加会话状态检查避免重复启动

## 目录权限设置

在生产服务器上，需要设置正确的目录权限：

```bash
# 设置存储目录权限
chmod -R 755 storage/
chmod -R 777 storage/cache/
chmod -R 777 storage/sessions/
chmod -R 777 storage/logs/

# 确保Web服务器用户可写
chown -R www-data:www-data storage/  # Ubuntu/Debian
# 或
chown -R apache:apache storage/      # CentOS/RedHat
```

## 环境配置

1. **创建 .env 文件**
```bash
cp env.example .env
```

2. **编辑 .env 文件，设置生产环境**
```
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_NAME=yisheng_db
DB_USER=your_db_user
DB_PASS=your_db_password

# DeepSeek API配置
DEEPSEEK_API_KEY=your_api_key
```

## 安全建议

1. **禁用错误显示**
   - 系统已自动在生产环境禁用错误显示
   - 错误日志保存在 `storage/logs/php_errors.log`

2. **保护敏感目录**
   - `storage/` 目录已添加 `.htaccess` 文件防止直接访问
   - 确保 Web 服务器配置正确处理 `.htaccess`

3. **HTTPS 配置**
   - 强烈建议使用 HTTPS
   - 系统会自动为 HTTPS 连接启用安全的 Cookie 设置

4. **数据库安全**
   - 不要使用 root 用户
   - 创建专用数据库用户并限制权限：
   
```sql
CREATE USER 'yisheng_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON yisheng_db.* TO 'yisheng_user'@'localhost';
FLUSH PRIVILEGES;
```

## 性能优化

1. **启用 OPcache**
```ini
; 在 php.ini 中
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

2. **会话垃圾回收**
```bash
# 添加 cron 任务清理过期会话
0 2 * * * find /path/to/storage/sessions -name "sess_*" -mtime +7 -delete
```

## 监控和维护

1. **定期检查错误日志**
```bash
tail -f storage/logs/php_errors.log
```

2. **备份数据库**
```bash
# 每日备份脚本
mysqldump -u yisheng_user -p yisheng_db > backup_$(date +%Y%m%d).sql
```

3. **监控磁盘空间**
   - 特别注意 `storage/logs/` 目录
   - 定期清理旧日志文件

## 故障排查

如果遇到问题：

1. 检查 PHP 错误日志：`storage/logs/php_errors.log`
2. 检查 Web 服务器错误日志
3. 确认目录权限正确
4. 验证 `.env` 文件配置
5. 使用 `php -m` 检查必需的 PHP 扩展是否已安装 