# DeepSeek API 集成文档

## 概述

本系统通过 OpenRouter 集成了 DeepSeek 模型，用于中医智能诊断。OpenRouter 提供了免费的 DeepSeek 模型访问。

## 核心组件

### 1. DeepSeekAPI 类 (`app/Services/DeepSeekAPI.php`)

主要功能：
- 调用 AI 进行中医诊断
- 格式化问诊数据
- 错误处理和重试机制
- 请求限流
- API 调用日志记录

### 2. ApiLogger 类 (`app/Services/ApiLogger.php`)

主要功能：
- 记录所有 API 调用详情
- 敏感信息脱敏
- 日志文件轮转
- API 调用统计

## 配置说明

### 环境变量配置

在 `.env` 文件中设置：

```bash
# 使用 OpenRouter 访问 DeepSeek 模型
DEEPSEEK_API_KEY=your_openrouter_api_key_here
DEEPSEEK_API_URL=https://openrouter.ai/api/v1/chat/completions
DEEPSEEK_MODEL=deepseek/deepseek-chat
DEEPSEEK_TEMPERATURE=0.7
DEEPSEEK_MAX_TOKENS=2000
```

### 获取 OpenRouter API Key

1. 访问 [OpenRouter](https://openrouter.ai/)
2. 注册账号并登录
3. 在账户设置中生成 API Key
4. 将 API Key 配置到 `.env` 文件中

## 使用方法

### 基本使用

```php
use App\Services\DeepSeekAPI;

// 初始化 API
$api = new DeepSeekAPI();

// 准备问诊数据
$consultationData = [
    'patient_name' => '张三',
    'age' => 35,
    'gender' => '男',
    'height' => 175,
    'weight' => 70,
    'chief_complaint' => '失眠多梦，心烦易怒',
    'present_illness' => '近一个月失眠...',
    // ... 其他问诊信息
];

// 调用诊断
$result = $api->diagnose($consultationData);

if ($result['success']) {
    echo $result['diagnosis'];
} else {
    echo "诊断失败：" . $result['error'];
}
```

### 测试连接

```php
$api = new DeepSeekAPI();
if ($api->testConnection()) {
    echo "API 连接成功";
} else {
    echo "API 连接失败";
}
```

## 数据格式

### 输入数据格式

问诊数据应包含以下字段：

**基本信息：**
- `patient_name` - 患者姓名
- `age` - 年龄
- `gender` - 性别
- `height` - 身高（cm）
- `weight` - 体重（kg）

**病情信息：**
- `chief_complaint` - 主诉
- `present_illness` - 现病史
- `past_history` - 既往史（可选）
- `family_history` - 家族史（可选）

**中医四诊：**
- `complexion` - 面色
- `spirit` - 精神状态
- `body_shape` - 形体
- `tongue_body` - 舌质
- `tongue_coating` - 舌苔
- `pulse` - 脉象
- `voice` - 声音
- `breath` - 呼吸
- `sleep` - 睡眠
- `appetite` - 食欲
- `bowel` - 大便
- `urine` - 小便

### 输出数据格式

诊断结果包含：
- 证候分析
- 病机
- 治法
- 方剂推荐
- 中药处方
- 调护建议

## 错误处理

系统实现了完善的错误处理机制：

1. **自动重试** - 失败后自动重试最多 3 次
2. **请求限流** - 防止频繁请求
3. **详细日志** - 记录所有请求和错误
4. **优雅降级** - API 失败时返回友好提示

## 日志记录

### 日志文件位置

- API 调用日志：`storage/logs/api_calls.log`
- API 错误日志：`storage/logs/api_errors.log`

### 查看日志统计

```php
use App\Services\ApiLogger;

$logger = new ApiLogger();

// 获取最近的调用记录
$recentCalls = $logger->getRecentCalls(10);

// 获取统计信息
$stats = $logger->getStatistics();
```

## 测试

访问 `/test-api.php` 进行 API 测试：
- 检查配置状态
- 测试 API 连接
- 执行模拟诊断
- 查看调用日志

## 注意事项

1. **API 密钥安全** - 不要将 API 密钥提交到版本控制
2. **请求限制** - OpenRouter 免费账户有请求限制
3. **数据隐私** - 患者数据应加密存储
4. **错误监控** - 定期检查错误日志
5. **成本控制** - 监控 Token 使用量

## 故障排查

### 常见问题

1. **API 密钥未配置**
   - 检查 `.env` 文件中的 `DEEPSEEK_API_KEY`
   - 确保已从 OpenRouter 获取有效的 API Key

2. **连接失败**
   - 检查网络连接
   - 验证 API URL 是否正确
   - 查看错误日志获取详细信息

3. **诊断结果为空**
   - 确保输入数据格式正确
   - 检查 Token 限制设置
   - 查看 API 响应日志

4. **请求超时**
   - 增加超时时间设置
   - 检查网络延迟
   - 考虑使用更小的 max_tokens 