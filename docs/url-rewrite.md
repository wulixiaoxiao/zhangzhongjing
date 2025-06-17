# URL 重写规则说明

## 概述

中医智能问诊系统使用 URL 重写技术实现友好的访问地址。所有请求都通过 `index.php` 入口文件处理。

## URL 格式

### 原始格式
```
/index.php?url=controller/action/param1/param2
```

### 友好格式（重写后）
```
/controller/action/param1/param2
```

## 系统路由示例

### 1. 首页
- 友好 URL: `/`
- 实际访问: `/index.php?url=home/index`

### 2. 问诊表单
- 友好 URL: `/consultation/form`
- 实际访问: `/index.php?url=consultation/form`

### 3. 提交问诊
- 友好 URL: `/consultation/submit` (POST)
- 实际访问: `/index.php?url=consultation/submit`

### 4. 处理中页面
- 友好 URL: `/consultation/processing/123`
- 实际访问: `/index.php?url=consultation/processing/123`

### 5. 诊断结果
- 友好 URL: `/consultation/result/123`
- 实际访问: `/index.php?url=consultation/result/123`

### 6. 历史记录
- 友好 URL: `/consultation`
- 实际访问: `/index.php?url=consultation/index`

### 7. 导出报告
- 友好 URL: `/consultation/export/123`
- 实际访问: `/index.php?url=consultation/export/123`

## Nginx 配置

### 基础配置
```nginx
server {
    listen 80;
    server_name yisheng.example.com;
    root /var/www/yisheng/public;
    index index.php;
    
    # 核心重写规则
    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }
    
    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 规则解释

1. **try_files 指令**
   ```nginx
   try_files $uri $uri/ /index.php?url=$uri&$args;
   ```
   - 首先尝试访问实际文件 (`$uri`)
   - 如果不存在，尝试访问目录 (`$uri/`)
   - 最后重写到 `index.php` 并传递 URL 参数

2. **保留查询参数**
   - `&$args` 确保原始查询参数被保留
   - 例如: `/consultation?page=2` => `/index.php?url=consultation&page=2`

## Apache 配置（.htaccess）

如果使用 Apache，已包含的 `.htaccess` 文件提供相同功能：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

## 测试 URL 重写

### 1. 测试静态文件访问
```bash
# CSS 文件应该直接访问
curl http://your-domain.com/css/style.css

# JS 文件应该直接访问
curl http://your-domain.com/js/app.js
```

### 2. 测试动态路由
```bash
# 应该返回首页内容
curl http://your-domain.com/

# 应该返回问诊表单
curl http://your-domain.com/consultation/form
```

### 3. 检查重写日志
```bash
# Nginx 错误日志
tail -f /var/log/nginx/error.log

# PHP 错误日志
tail -f /var/log/php8.1-fpm.log
```

## 常见问题

### 1. 404 错误
- 检查 nginx 配置中的 `root` 路径是否正确
- 确保指向 `public` 目录，而不是项目根目录

### 2. 500 错误
- 检查 PHP-FPM 是否运行
- 检查文件权限（特别是 `storage` 目录）

### 3. 重写不生效
- 确保 nginx 配置已重新加载: `nginx -s reload`
- 检查是否有其他 location 块冲突

### 4. 静态文件 404
- 确保静态文件在 `public` 目录中
- 检查文件权限

## 调试技巧

### 1. 启用 Nginx 调试日志
```nginx
error_log /var/log/nginx/debug.log debug;
rewrite_log on;
```

### 2. 查看实际接收的参数
在 `index.php` 中添加：
```php
error_log('URL: ' . ($_GET['url'] ?? 'none'));
error_log('Full URI: ' . $_SERVER['REQUEST_URI']);
```

### 3. 测试特定路由
```bash
# 使用 curl 测试并查看响应头
curl -I http://your-domain.com/consultation/form
```

## 安全注意事项

1. **防止目录遍历**
   - 重写规则已过滤危险字符
   - Router 类应验证控制器和方法名

2. **隐藏敏感文件**
   ```nginx
   location ~ /\.(env|git|htaccess) {
       deny all;
   }
   ```

3. **限制 PHP 执行**
   - 只允许 `public` 目录中的 PHP 文件执行
   - 其他目录应禁止 PHP 执行 