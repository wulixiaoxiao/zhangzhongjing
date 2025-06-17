# 配置管理系统文档

## 概述

中医智能问诊系统使用了一个灵活的配置管理系统，支持环境变量和配置文件的分离管理。

## 核心组件

### 1. Config 类 (`app/Core/Config.php`)

负责加载和管理所有配置：
- 加载 `.env` 文件中的环境变量
- 自动加载 `config/` 目录下的所有配置文件
- 提供配置读取和设置的 API

### 2. 辅助函数 (`src/helpers.php`)

提供全局辅助函数：
- `config()` - 获取/设置配置值
- `env()` - 获取环境变量值
- `app_path()` - 获取应用路径
- `config_path()` - 获取配置目录路径
- `storage_path()` - 获取存储目录路径
- `public_path()` - 获取公共目录路径

## 使用方法

### 环境变量设置

1. 复制 `.env.example` 到 `.env`：
```bash
cp .env.example .env
```

2. 或使用设置脚本：
```bash
php scripts/setup-env.php
```

3. 编辑 `.env` 文件配置环境变量

### 读取配置

```php
// 获取配置值
$appName = config('app.name');
$dbHost = config('app.database.host');
$debug = config('app.debug');

// 使用默认值
$timezone = config('app.timezone', 'UTC');

// 获取环境变量
$apiKey = env('DEEPSEEK_API_KEY');
$dbPass = env('DB_PASS', '');
```

### 设置配置

```php
// 设置单个配置
config(['app.locale' => 'zh_CN']);

// 设置多个配置
config([
    'app.timezone' => 'Asia/Shanghai',
    'app.locale' => 'zh_CN'
]);
```

## 配置文件

系统包含以下配置文件：

### app.php
应用基本配置：
- 应用名称、版本、时区
- 调试模式
- 数据库连接
- DeepSeek API 配置
- 会话配置
- 上传配置

### logging.php
日志配置：
- 日志通道（file, daily, database）
- 日志级别
- 日志文件路径

### cache.php
缓存配置：
- 缓存驱动（file, database, array）
- 缓存路径
- 缓存前缀和 TTL

## 环境变量

主要环境变量说明：

```bash
# 应用配置
APP_NAME              # 应用名称
APP_ENV               # 环境（development/production）
APP_DEBUG             # 调试模式（true/false）
APP_URL               # 应用 URL

# 数据库配置
DB_HOST               # 数据库主机
DB_PORT               # 数据库端口
DB_NAME               # 数据库名称
DB_USER               # 数据库用户
DB_PASS               # 数据库密码

# DeepSeek API
DEEPSEEK_API_KEY      # API 密钥
DEEPSEEK_API_URL      # API 端点
DEEPSEEK_MODEL        # 模型名称
DEEPSEEK_TEMPERATURE  # 温度参数
DEEPSEEK_MAX_TOKENS   # 最大令牌数

# 日志配置
LOG_CHANNEL           # 日志通道
LOG_LEVEL             # 日志级别
LOG_PATH              # 日志路径

# 缓存配置
CACHE_DRIVER          # 缓存驱动
CACHE_PATH            # 缓存路径
CACHE_PREFIX          # 缓存前缀
```

## 测试配置

访问 `/test-config.php` 可以查看当前配置状态和测试配置系统。

## 注意事项

1. **不要将 `.env` 文件提交到版本控制系统**
2. 生产环境应设置 `APP_DEBUG=false`
3. 敏感信息（如 API 密钥）应只存储在 `.env` 文件中
4. 配置文件中使用 `env()` 函数读取环境变量，提供默认值
5. `routes.php` 不是配置文件，会被自动跳过 