# 中医智能问诊系统 - 数据库说明

## 数据库信息

- **数据库名称**：`yisheng_db`
- **字符集**：`utf8mb4`
- **排序规则**：`utf8mb4_unicode_ci`
- **引擎**：`InnoDB`

## 数据库连接信息

```
主机：localhost
端口：3306
用户名：root
密码：（空）
数据库：yisheng_db
```

## 数据表结构

### 1. 患者信息表 (patients)

存储患者的基本信息。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| name | VARCHAR(50) | 姓名 |
| gender | ENUM('男', '女') | 性别 |
| age | TINYINT UNSIGNED | 年龄 |
| phone | VARCHAR(20) | 联系电话 |
| id_card | VARCHAR(18) | 身份证号 |
| occupation | VARCHAR(50) | 职业 |
| marriage | ENUM | 婚姻状况 |
| address | VARCHAR(200) | 住址 |
| emergency_contact | VARCHAR(50) | 紧急联系人 |
| emergency_phone | VARCHAR(20) | 紧急联系电话 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 2. 问诊记录表 (consultations)

存储患者的问诊记录，包括症状、体征、中医四诊等信息。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| patient_id | INT UNSIGNED | 患者ID（外键） |
| consultation_no | VARCHAR(20) | 问诊编号（唯一） |
| consultation_date | DATETIME | 问诊时间 |
| chief_complaint | TEXT | 主诉 |
| tongue_body | VARCHAR(100) | 舌质 |
| tongue_coating | VARCHAR(100) | 舌苔 |
| pulse_diagnosis | VARCHAR(200) | 脉诊 |
| status | ENUM | 状态（待诊断/诊断中/已完成/已取消） |

还包括：生命体征、病史、生活习惯、体格检查、中医四诊等详细字段。

### 3. 诊断结果表 (diagnoses)

存储AI生成的诊断结果。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| consultation_id | INT UNSIGNED | 问诊记录ID（外键） |
| syndrome | VARCHAR(200) | 证型 |
| syndrome_analysis | TEXT | 辨证分析 |
| treatment_principle | VARCHAR(200) | 治法 |
| ai_model | VARCHAR(50) | AI模型 |
| ai_raw_response | JSON | AI原始响应 |
| medical_advice | TEXT | 医嘱建议 |
| lifestyle_advice | TEXT | 生活建议 |
| dietary_advice | TEXT | 饮食建议 |

### 4. 处方记录表 (prescriptions)

存储中药处方信息。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| diagnosis_id | INT UNSIGNED | 诊断ID（外键） |
| prescription_no | VARCHAR(20) | 处方编号（唯一） |
| prescription_name | VARCHAR(100) | 方剂名称 |
| herbs | JSON | 药物组成（JSON格式） |
| usage_method | VARCHAR(200) | 用法 |
| dosage | VARCHAR(100) | 用量 |
| frequency | VARCHAR(100) | 频次 |
| duration | VARCHAR(50) | 疗程 |

### 5. 系统日志表 (system_logs)

记录系统操作日志。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| user_id | INT UNSIGNED | 用户ID |
| action | VARCHAR(100) | 操作动作 |
| module | VARCHAR(50) | 模块 |
| description | TEXT | 描述 |
| ip_address | VARCHAR(45) | IP地址 |
| created_at | TIMESTAMP | 创建时间 |

### 6. AI调用记录表 (ai_call_logs)

记录AI接口调用情况。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键，自增 |
| consultation_id | INT UNSIGNED | 问诊记录ID |
| model | VARCHAR(50) | AI模型 |
| request_data | JSON | 请求数据 |
| response_data | JSON | 响应数据 |
| tokens_used | INT UNSIGNED | 使用的令牌数 |
| cost | DECIMAL(10,4) | 费用 |
| duration | INT UNSIGNED | 耗时(毫秒) |
| status | ENUM | 状态（成功/失败/超时） |

## 数据库操作

### 1. 初始化数据库

```bash
# 使用初始化脚本（推荐）
php database/init.php

# 或者手动执行SQL
mysql -u root < database/schema.sql
```

### 2. 插入测试数据

```bash
# 执行测试数据SQL
mysql -u root yisheng_db < database/test_data.sql

# 或在初始化时选择插入测试数据
php database/init.php
# 当提示"是否要插入测试数据？"时输入 y
```

### 3. 检查数据库状态

```bash
# 查看表结构和数据统计
mysql -u root yisheng_db < database/check_tables.sql
```

### 4. 备份数据库

```bash
# 备份整个数据库
mysqldump -u root yisheng_db > backup_$(date +%Y%m%d_%H%M%S).sql

# 只备份表结构
mysqldump -u root --no-data yisheng_db > schema_backup.sql

# 只备份数据
mysqldump -u root --no-create-info yisheng_db > data_backup.sql
```

### 5. 恢复数据库

```bash
# 恢复完整备份
mysql -u root yisheng_db < backup_20250617_120000.sql
```

## 注意事项

1. **字符集**：确保 MySQL 配置文件中设置了 `utf8mb4` 字符集
2. **时区**：数据库使用 `Asia/Shanghai` 时区
3. **外键约束**：使用 `ON DELETE CASCADE`，删除患者会级联删除相关记录
4. **JSON字段**：`herbs`、`ai_raw_response` 等字段使用 JSON 格式存储
5. **索引优化**：已为常用查询字段创建索引

## 数据统计（当前）

- 患者信息：9 条
- 问诊记录：4 条
- 诊断结果：2 条
- 处方记录：2 条
- 系统日志：0 条
- AI调用记录：0 条 