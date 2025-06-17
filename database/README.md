# 数据库管理说明

## 数据库结构

本系统使用 MySQL 数据库，包含以下主要表：

### 1. 核心业务表
- **patients** - 患者信息表
- **consultations** - 问诊记录表
- **diagnoses** - 诊断结果表
- **prescriptions** - 处方记录表

### 2. 辅助表
- **system_logs** - 系统日志表
- **ai_call_logs** - AI调用记录表

## 初始化数据库

### 方法一：使用初始化脚本（推荐）

1. 确保已配置 `.env` 文件中的数据库连接信息：
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=yisheng_db
   DB_USER=root
   DB_PASS=your_password
   ```

2. 运行初始化脚本：
   ```bash
   php database/init.php
   ```

3. 根据提示选择是否插入测试数据

### 方法二：手动执行 SQL

1. 登录 MySQL：
   ```bash
   mysql -u root -p
   ```

2. 执行架构文件：
   ```sql
   source /path/to/yisheng/database/schema.sql
   ```

3. （可选）导入测试数据：
   ```sql
   source /path/to/yisheng/database/test_data.sql
   ```

## 表结构说明

### patients（患者信息表）
存储患者的基本信息，包括姓名、性别、年龄、联系方式等。

### consultations（问诊记录表）
记录每次问诊的详细信息，包括：
- 基本生命体征
- 主诉和病史
- 中医四诊信息
- 症状描述

### diagnoses（诊断结果表）
保存 AI 生成的诊断结果：
- 中医证型和辨证分析
- 治疗原则
- 医嘱建议
- AI 模型信息

### prescriptions（处方记录表）
存储处方信息：
- 方剂名称和组成（JSON格式）
- 用法用量
- 注意事项

## 数据库维护

### 备份
```bash
mysqldump -u root -p yisheng_db > backup_$(date +%Y%m%d).sql
```

### 恢复
```bash
mysql -u root -p yisheng_db < backup_20250101.sql
```

### 清空测试数据
```sql
-- 保留表结构，仅清空数据
TRUNCATE TABLE prescriptions;
TRUNCATE TABLE diagnoses;
TRUNCATE TABLE consultations;
TRUNCATE TABLE patients;
```

## 注意事项

1. 生产环境部署前请修改默认的数据库密码
2. 定期备份数据库
3. 建议为应用创建专用的数据库用户，而不是使用 root
4. 确保数据库字符集为 utf8mb4 以支持中文和特殊字符 