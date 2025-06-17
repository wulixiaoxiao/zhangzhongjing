-- 测试数据
USE `yisheng_db`;

-- 插入测试患者数据
INSERT INTO `patients` (`name`, `gender`, `age`, `phone`, `occupation`, `marriage`, `address`) VALUES
('李四', '女', 28, '13900139000', '会计', '已婚', '北京市朝阳区'),
('王五', '男', 45, '13700137000', '工程师', '已婚', '上海市浦东新区'),
('赵六', '女', 52, '13600136000', '退休', '已婚', '广州市天河区'),
('孙七', '男', 38, '13500135000', '销售', '离异', '深圳市南山区');

-- 插入测试问诊记录
INSERT INTO `consultations` 
(`patient_id`, `consultation_no`, `chief_complaint`, `present_illness`, `past_history`, 
`tongue_body`, `tongue_coating`, `pulse_diagnosis`, `status`) VALUES
(1, 'C20250101001', '失眠多梦1月余，伴心烦易怒', 
'患者诉近1月来入睡困难，多梦易醒，每晚睡眠时间约4-5小时。伴有心烦易怒，口苦咽干，偶有头晕。', 
'既往体健，无特殊病史。', '红', '黄腻', '弦数', '已完成'),

(2, 'C20250102001', '胃脘胀痛2周，餐后加重', 
'2周前无明显诱因出现胃脘胀痛，以餐后为甚，伴有嗳气、反酸，纳差。', 
'慢性胃炎病史3年。', '淡红', '白腻', '弦缓', '已完成'),

(3, 'C20250103001', '腰膝酸软3月，畏寒肢冷', 
'3月来感腰膝酸软无力，伴有畏寒肢冷，夜尿频多（3-4次/夜），精神疲倦。', 
'高血压病史5年，规律服药控制。', '淡白', '薄白', '沉细', '待诊断');

-- 插入测试诊断结果
INSERT INTO `diagnoses` 
(`consultation_id`, `syndrome`, `syndrome_analysis`, `treatment_principle`, 
`medical_advice`, `lifestyle_advice`, `dietary_advice`, `followup_advice`) VALUES
(1, '肝郁化火，心神不宁', 
'患者情志不遂，肝气郁结，郁而化火，上扰心神，故见失眠多梦；肝火上炎，故见心烦易怒、口苦咽干；舌红苔黄腻、脉弦数为肝郁化火之征。', 
'清肝泻火，安神定志', 
'按时服药，定期复诊。如症状加重请及时就医。', 
'保持心情舒畅，避免情绪激动。规律作息，23点前入睡。适当运动如散步、太极拳等。', 
'饮食清淡，忌辛辣刺激、油腻食物。可多食百合、莲子、酸枣仁等安神食材。', 
'建议2周后复诊'),

(2, '肝胃不和，气机郁滞', 
'情志不畅，肝气郁结，横逆犯胃，胃失和降，故见胃脘胀痛、嗳气反酸；气机不畅，故餐后加重；舌淡红苔白腻、脉弦缓为肝胃不和之象。', 
'疏肝理气，和胃止痛', 
'规律服药，避免自行停药。注意饮食卫生。', 
'保持情绪稳定，避免过度紧张。规律进餐，细嚼慢咽。', 
'少食多餐，避免暴饮暴食。忌生冷、辛辣、油腻食物。可适当食用山药、白术等健脾食材。', 
'建议1月后复诊');

-- 插入测试处方
INSERT INTO `prescriptions` 
(`diagnosis_id`, `prescription_no`, `prescription_name`, `herbs`, 
`usage_method`, `dosage`, `frequency`, `duration`) VALUES
(1, 'P20250101001', '龙胆泻肝汤加减', 
'[{"name":"龙胆草","dosage":10,"unit":"g"},
{"name":"黄芩","dosage":10,"unit":"g"},
{"name":"栀子","dosage":10,"unit":"g"},
{"name":"泽泻","dosage":10,"unit":"g"},
{"name":"木通","dosage":6,"unit":"g"},
{"name":"车前子","dosage":10,"unit":"g"},
{"name":"当归","dosage":10,"unit":"g"},
{"name":"生地黄","dosage":15,"unit":"g"},
{"name":"柴胡","dosage":10,"unit":"g"},
{"name":"甘草","dosage":6,"unit":"g"},
{"name":"酸枣仁","dosage":15,"unit":"g"},
{"name":"夜交藤","dosage":15,"unit":"g"}]',
'水煎服', '每日1剂', '分2次温服', '14天'),

(2, 'P20250102001', '柴胡疏肝散合左金丸', 
'[{"name":"柴胡","dosage":12,"unit":"g"},
{"name":"陈皮","dosage":10,"unit":"g"},
{"name":"川芎","dosage":10,"unit":"g"},
{"name":"香附","dosage":10,"unit":"g"},
{"name":"枳壳","dosage":10,"unit":"g"},
{"name":"芍药","dosage":15,"unit":"g"},
{"name":"甘草","dosage":6,"unit":"g"},
{"name":"黄连","dosage":6,"unit":"g"},
{"name":"吴茱萸","dosage":3,"unit":"g"},
{"name":"白术","dosage":10,"unit":"g"},
{"name":"茯苓","dosage":15,"unit":"g"}]',
'水煎服', '每日1剂', '分2次温服，餐前30分钟服用', '30天'); 