# 性能优化指南

## 已实现的优化措施

### 1. 数据库查询优化

#### 查询缓存系统
- 实现了内存查询缓存，SELECT查询结果自动缓存
- 缓存键基于SQL语句和参数的MD5哈希
- INSERT/UPDATE/DELETE操作自动清除缓存
- 可通过 `Database::setCacheEnabled()` 控制缓存开关

#### 性能监控
- 查询计数器：记录执行的查询总数
- 执行时间统计：记录每个查询的执行时间
- 慢查询日志：超过1秒的查询自动记录到 `storage/logs/slow_queries.log`
- 查询统计API：`Database::getQueryStats()` 获取性能统计

#### 批量操作
- 实现了 `batchInsert()` 方法，支持批量插入数据
- 减少数据库连接开销，提高批量数据处理效率

### 2. 页面缓存系统

#### 文件缓存
- 位置：`storage/cache/`
- 支持TTL（生存时间）设置
- 自动过期清理
- 缓存文件分目录存储，避免单目录文件过多

#### 缓存API
```php
// 基本使用
Cache::set('key', $value, 3600); // 缓存1小时
$value = Cache::get('key', $default);
Cache::delete('key');
Cache::flush(); // 清空所有缓存

// Remember模式（推荐）
$data = Cache::remember('expensive_data', function() {
    // 耗时的数据处理
    return $processedData;
}, 3600);

// 页面缓存
if (!Cache::startPage('page_key', 3600)) {
    // 生成页面内容
    Cache::endPage('page_key', 3600);
}
```

### 3. 静态资源优化

#### GZIP压缩（.htaccess配置）
- HTML、CSS、JavaScript自动压缩
- 支持的MIME类型全面覆盖
- 可减少50-70%的传输数据量

#### 浏览器缓存
- 图片：缓存1年
- CSS/JS：缓存1个月  
- 字体：缓存1个月
- 通过Expires和Cache-Control头控制

#### 安全头部
- X-Content-Type-Options：防止MIME类型嗅探
- X-Frame-Options：防止点击劫持
- X-XSS-Protection：启用XSS保护
- Content-Security-Policy：内容安全策略

### 4. 生产环境优化

#### 错误处理
- 生产环境自动禁用错误显示
- 错误记录到日志文件
- 支持环境变量 `APP_ENV` 控制

#### 会话优化
- 自定义会话保存路径
- 启用HTTPOnly Cookie
- HTTPS环境下启用Secure Cookie
- 严格会话模式防止会话固定攻击

## 性能测试工具

访问 `/test-performance.php` 可以：
- 测试数据库查询性能
- 测试缓存读写性能
- 查看内存使用情况
- 获取性能优化建议
- 查看慢查询日志
- 查看系统信息

## 性能优化建议

### 1. 数据库优化
- 为常用查询字段添加索引
- 使用EXPLAIN分析复杂查询
- 避免SELECT *，只查询需要的字段
- 使用批量插入代替循环插入
- 合理使用事务

### 2. 代码优化
- 使用Cache::remember()缓存计算结果
- 避免在循环中执行数据库查询
- 及时释放不需要的变量
- 使用适当的数据结构

### 3. 服务器配置
```ini
; PHP配置建议 (php.ini)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60

; MySQL配置建议 (my.cnf)
query_cache_type = 1
query_cache_size = 64M
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
```

### 4. 定期维护
- 清理过期缓存：`Cache::cleanup()`
- 分析慢查询日志
- 监控内存使用
- 定期优化数据库表：`OPTIMIZE TABLE`

## 性能监控

### 实时监控
```php
// 在Controller或页面底部添加
$stats = Database::getQueryStats();
echo "<!-- 查询: {$stats['query_count']}次, 耗时: {$stats['total_time']}ms -->";
```

### 日志分析
```bash
# 查看最新的慢查询
tail -n 50 storage/logs/slow_queries.log

# 统计慢查询数量
grep -c "执行时间" storage/logs/slow_queries.log

# 找出最慢的查询
sort -t: -k3 -n storage/logs/slow_queries.log | tail -10
```

## 负载测试

使用Apache Bench进行压力测试：
```bash
# 100个并发，总共1000个请求
ab -n 1000 -c 100 http://localhost/

# 持续10秒的压力测试
ab -t 10 -c 50 http://localhost/consultation/
```

## 故障排查

1. **页面加载慢**
   - 检查慢查询日志
   - 使用性能测试工具分析
   - 检查是否启用了缓存

2. **内存占用高**
   - 检查是否有内存泄漏
   - 优化大数据集处理
   - 考虑分页处理

3. **数据库连接错误**
   - 检查连接池配置
   - 确认数据库服务正常
   - 检查最大连接数限制 