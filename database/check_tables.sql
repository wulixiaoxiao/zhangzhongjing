-- 检查数据库表结构和数据的 SQL 脚本
-- 使用方法：mysql -u root yisheng_db < database/check_tables.sql

USE yisheng_db;

-- 显示所有表
SELECT '========== 数据库中的所有表 ==========' AS '';
SHOW TABLES;

-- 显示各表的记录数
SELECT '========== 各表记录数统计 ==========' AS '';
SELECT 
    '患者信息表 (patients)' AS '表名',
    COUNT(*) AS '记录数'
FROM patients
UNION ALL
SELECT 
    '问诊记录表 (consultations)' AS '表名',
    COUNT(*) AS '记录数'
FROM consultations
UNION ALL
SELECT 
    '诊断结果表 (diagnoses)' AS '表名',
    COUNT(*) AS '记录数'
FROM diagnoses
UNION ALL
SELECT 
    '处方记录表 (prescriptions)' AS '表名',
    COUNT(*) AS '记录数'
FROM prescriptions
UNION ALL
SELECT 
    '系统日志表 (system_logs)' AS '表名',
    COUNT(*) AS '记录数'
FROM system_logs
UNION ALL
SELECT 
    'AI调用记录表 (ai_call_logs)' AS '表名',
    COUNT(*) AS '记录数'
FROM ai_call_logs;

-- 查看患者表结构
SELECT '========== patients 表结构 ==========' AS '';
DESCRIBE patients;

-- 查看问诊表结构
SELECT '========== consultations 表结构 ==========' AS '';
DESCRIBE consultations;

-- 查看诊断表结构
SELECT '========== diagnoses 表结构 ==========' AS '';
DESCRIBE diagnoses;

-- 查看处方表结构
SELECT '========== prescriptions 表结构 ==========' AS '';
DESCRIBE prescriptions;

-- 查看部分患者数据
SELECT '========== 患者示例数据 (前5条) ==========' AS '';
SELECT id, name, gender, age, phone, occupation, created_at 
FROM patients 
LIMIT 5;

-- 查看部分问诊数据
SELECT '========== 问诊示例数据 (前3条) ==========' AS '';
SELECT c.id, c.consultation_no, p.name AS patient_name, 
       c.chief_complaint, c.status, c.created_at
FROM consultations c
JOIN patients p ON c.patient_id = p.id
ORDER BY c.created_at DESC
LIMIT 3;

-- 查看诊断和处方关联数据
SELECT '========== 诊断和处方示例 ==========' AS '';
SELECT 
    d.id AS diagnosis_id,
    c.consultation_no,
    p.name AS patient_name,
    d.syndrome AS '证型',
    d.treatment_principle AS '治法',
    pr.prescription_name AS '方剂名称',
    pr.duration AS '疗程'
FROM diagnoses d
JOIN consultations c ON d.consultation_id = c.id
JOIN patients p ON c.patient_id = p.id
LEFT JOIN prescriptions pr ON pr.diagnosis_id = d.id
LIMIT 5; 