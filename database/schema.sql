-- 中医智能问诊系统数据库架构
-- 创建数据库
CREATE DATABASE IF NOT EXISTS `yisheng_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yisheng_db`;

-- 1. 患者信息表
CREATE TABLE IF NOT EXISTS `patients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT '姓名',
    `gender` ENUM('男', '女') NOT NULL COMMENT '性别',
    `age` TINYINT UNSIGNED NOT NULL COMMENT '年龄',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系电话',
    `id_card` VARCHAR(18) DEFAULT NULL COMMENT '身份证号',
    `occupation` VARCHAR(50) DEFAULT NULL COMMENT '职业',
    `marriage` ENUM('未婚', '已婚', '离异', '丧偶') DEFAULT NULL COMMENT '婚姻状况',
    `address` VARCHAR(200) DEFAULT NULL COMMENT '住址',
    `emergency_contact` VARCHAR(50) DEFAULT NULL COMMENT '紧急联系人',
    `emergency_phone` VARCHAR(20) DEFAULT NULL COMMENT '紧急联系电话',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    INDEX `idx_name` (`name`),
    INDEX `idx_phone` (`phone`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='患者信息表';

-- 2. 问诊记录表
CREATE TABLE IF NOT EXISTS `consultations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL COMMENT '患者ID',
    `consultation_no` VARCHAR(20) NOT NULL UNIQUE COMMENT '问诊编号',
    `consultation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '问诊时间',
    
    -- 基本生命体征
    `blood_pressure` VARCHAR(20) DEFAULT NULL COMMENT '血压',
    `temperature` DECIMAL(3,1) DEFAULT NULL COMMENT '体温',
    `pulse` SMALLINT UNSIGNED DEFAULT NULL COMMENT '脉搏',
    `respiration` SMALLINT UNSIGNED DEFAULT NULL COMMENT '呼吸',
    `weight` DECIMAL(5,2) DEFAULT NULL COMMENT '体重',
    
    -- 主诉和病史
    `chief_complaint` TEXT NOT NULL COMMENT '主诉',
    `present_illness` TEXT DEFAULT NULL COMMENT '现病史',
    `past_history` TEXT DEFAULT NULL COMMENT '既往史',
    `allergy_history` VARCHAR(500) DEFAULT NULL COMMENT '过敏史',
    `personal_history` TEXT DEFAULT NULL COMMENT '个人史',
    `family_history` TEXT DEFAULT NULL COMMENT '家族史',
    
    -- 生活习惯
    `smoking` VARCHAR(100) DEFAULT NULL COMMENT '吸烟史',
    `drinking` VARCHAR(100) DEFAULT NULL COMMENT '饮酒史',
    `lifestyle` TEXT DEFAULT NULL COMMENT '生活习惯',
    
    -- 体格检查
    `physical_exam` TEXT DEFAULT NULL COMMENT '体格检查',
    
    -- 中医四诊
    `complexion` VARCHAR(50) DEFAULT NULL COMMENT '面色',
    `spirit` VARCHAR(50) DEFAULT NULL COMMENT '精神',
    `tongue_body` VARCHAR(100) DEFAULT NULL COMMENT '舌质',
    `tongue_coating` VARCHAR(100) DEFAULT NULL COMMENT '舌苔',
    `voice_breath` VARCHAR(100) DEFAULT NULL COMMENT '声音气息',
    `symptoms` TEXT DEFAULT NULL COMMENT '症状集合',
    `sleep_quality` VARCHAR(100) DEFAULT NULL COMMENT '睡眠',
    `diet_appetite` VARCHAR(100) DEFAULT NULL COMMENT '饮食胃纳',
    `urine` VARCHAR(100) DEFAULT NULL COMMENT '小便',
    `stool` VARCHAR(100) DEFAULT NULL COMMENT '大便',
    `pulse_diagnosis` VARCHAR(200) NOT NULL COMMENT '脉诊',
    
    -- 补充信息
    `additional_info` TEXT DEFAULT NULL COMMENT '补充说明',
    
    -- 状态
    `status` ENUM('待诊断', '诊断中', '已完成', '已取消') NOT NULL DEFAULT '待诊断' COMMENT '状态',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
    INDEX `idx_consultation_no` (`consultation_no`),
    INDEX `idx_patient_id` (`patient_id`),
    INDEX `idx_consultation_date` (`consultation_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问诊记录表';

-- 3. 诊断结果表
CREATE TABLE IF NOT EXISTS `diagnoses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consultation_id` INT UNSIGNED NOT NULL COMMENT '问诊记录ID',
    `diagnosis_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '诊断时间',
    
    -- 中医诊断
    `syndrome` VARCHAR(200) NOT NULL COMMENT '证型',
    `syndrome_analysis` TEXT NOT NULL COMMENT '辨证分析',
    `treatment_principle` VARCHAR(200) NOT NULL COMMENT '治法',
    
    -- AI分析
    `ai_model` VARCHAR(50) DEFAULT 'DeepSeek' COMMENT 'AI模型',
    `ai_raw_response` JSON DEFAULT NULL COMMENT 'AI原始响应',
    `ai_confidence` DECIMAL(3,2) DEFAULT NULL COMMENT 'AI置信度(0-1)',
    
    -- 医嘱建议
    `medical_advice` TEXT DEFAULT NULL COMMENT '医嘱建议',
    `lifestyle_advice` TEXT DEFAULT NULL COMMENT '生活建议',
    `dietary_advice` TEXT DEFAULT NULL COMMENT '饮食建议',
    `followup_advice` VARCHAR(200) DEFAULT NULL COMMENT '复诊建议',
    
    -- 审核信息
    `reviewed_by` VARCHAR(50) DEFAULT NULL COMMENT '审核医师',
    `reviewed_at` DATETIME DEFAULT NULL COMMENT '审核时间',
    `review_notes` TEXT DEFAULT NULL COMMENT '审核意见',
    
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
    INDEX `idx_consultation_id` (`consultation_id`),
    INDEX `idx_diagnosis_time` (`diagnosis_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='诊断结果表';

-- 4. 处方记录表
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `diagnosis_id` INT UNSIGNED NOT NULL COMMENT '诊断ID',
    `prescription_no` VARCHAR(20) NOT NULL UNIQUE COMMENT '处方编号',
    `prescription_name` VARCHAR(100) NOT NULL COMMENT '方剂名称',
    `prescription_type` ENUM('汤剂', '丸剂', '散剂', '膏剂', '丹剂', '其他') DEFAULT '汤剂' COMMENT '剂型',
    
    -- 方剂组成（JSON格式存储）
    `herbs` JSON NOT NULL COMMENT '药物组成',
    /* 示例格式：
    [
        {"name": "柴胡", "dosage": 10, "unit": "g"},
        {"name": "当归", "dosage": 10, "unit": "g"},
        {"name": "白芍", "dosage": 15, "unit": "g"}
    ]
    */
    
    -- 用法用量
    `usage_method` VARCHAR(200) NOT NULL COMMENT '用法',
    `dosage` VARCHAR(100) NOT NULL COMMENT '用量',
    `frequency` VARCHAR(100) NOT NULL COMMENT '频次',
    `duration` VARCHAR(50) DEFAULT NULL COMMENT '疗程',
    
    -- 注意事项
    `precautions` TEXT DEFAULT NULL COMMENT '注意事项',
    `contraindications` TEXT DEFAULT NULL COMMENT '禁忌',
    
    -- 配药信息
    `dispensed` BOOLEAN DEFAULT FALSE COMMENT '是否已配药',
    `dispensed_at` DATETIME DEFAULT NULL COMMENT '配药时间',
    `dispensed_by` VARCHAR(50) DEFAULT NULL COMMENT '配药人',
    
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`) ON DELETE CASCADE,
    INDEX `idx_diagnosis_id` (`diagnosis_id`),
    INDEX `idx_prescription_no` (`prescription_no`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='处方记录表';

-- 5. 系统日志表（可选）
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL COMMENT '用户ID',
    `action` VARCHAR(100) NOT NULL COMMENT '操作动作',
    `module` VARCHAR(50) NOT NULL COMMENT '模块',
    `description` TEXT DEFAULT NULL COMMENT '描述',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP地址',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT '用户代理',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统日志表';

-- 6. AI调用记录表（可选）
CREATE TABLE IF NOT EXISTS `ai_call_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consultation_id` INT UNSIGNED DEFAULT NULL COMMENT '问诊记录ID',
    `model` VARCHAR(50) NOT NULL COMMENT 'AI模型',
    `request_data` JSON NOT NULL COMMENT '请求数据',
    `response_data` JSON DEFAULT NULL COMMENT '响应数据',
    `tokens_used` INT UNSIGNED DEFAULT NULL COMMENT '使用的令牌数',
    `cost` DECIMAL(10,4) DEFAULT NULL COMMENT '费用',
    `duration` INT UNSIGNED DEFAULT NULL COMMENT '耗时(毫秒)',
    `status` ENUM('成功', '失败', '超时') NOT NULL COMMENT '状态',
    `error_message` TEXT DEFAULT NULL COMMENT '错误信息',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
    INDEX `idx_consultation_id` (`consultation_id`),
    INDEX `idx_model` (`model`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI调用记录表'; 