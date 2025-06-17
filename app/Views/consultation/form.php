<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - 中医智能问诊系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .sticky-top {
            top: 20px;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .auto-save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            display: none;
            z-index: 1000;
        }
        .invalid-feedback {
            display: none;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-hospital"></i> 中医智能问诊系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">首页</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/consultation/form">新建问诊</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/history">历史记录</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- 面包屑导航 -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item"><a href="/consultation">问诊管理</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- 左侧表单区域 -->
            <div class="col-lg-9">
                <form id="consultationForm" method="POST" action="/consultation/submit">
                    <?php echo csrf_field(); ?>
                    
                    <!-- 基本信息 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-person-badge"></i> 基本信息</h4>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label required">姓名</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="gender" class="form-label required">性别</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">请选择</option>
                                    <option value="男">男</option>
                                    <option value="女">女</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="age" class="form-label required">年龄</label>
                                <input type="number" class="form-control" id="age" name="age" min="1" max="150" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="blood_pressure" class="form-label">血压</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="如：130-90">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">联系电话</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="occupation" class="form-label">职业</label>
                                <input type="text" class="form-control" id="occupation" name="occupation">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="marriage" class="form-label">婚姻状况</label>
                                <select class="form-select" id="marriage" name="marriage">
                                    <option value="">请选择</option>
                                    <option value="未婚">未婚</option>
                                    <option value="已婚">已婚</option>
                                    <option value="离异">离异</option>
                                    <option value="丧偶">丧偶</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 主诉 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-chat-text"></i> 主诉</h4>
                        <div class="mb-3">
                            <label for="chief_complaint" class="form-label required">请描述您的主要不适症状及持续时间</label>
                            <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" required
                                placeholder="例如：头晕、乏力2周，伴有食欲不振"></textarea>
                        </div>
                    </div>

                    <!-- 现病史 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-clock-history"></i> 现病史</h4>
                        <div class="mb-3">
                            <label for="present_illness" class="form-label">详细描述发病过程、症状变化、诊治经过等</label>
                            <textarea class="form-control" id="present_illness" name="present_illness" rows="4"
                                placeholder="包括：起病时间、诱因、症状特点、加重或缓解因素、已接受的治疗及效果等"></textarea>
                        </div>
                    </div>

                    <!-- 既往史 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-journal-medical"></i> 既往史</h4>
                        <div class="mb-3">
                            <label for="past_history" class="form-label">过去的疾病史、手术史、外伤史、输血史等</label>
                            <textarea class="form-control" id="past_history" name="past_history" rows="3"
                                placeholder="例如：高血压5年，服用降压药物控制；2年前阑尾炎手术等"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="allergy_history" class="form-label">过敏史</label>
                            <input type="text" class="form-control" id="allergy_history" name="allergy_history" 
                                placeholder="药物过敏、食物过敏等">
                        </div>
                    </div>

                    <!-- 个人史 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-person"></i> 个人史</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="smoking" class="form-label">吸烟史</label>
                                <input type="text" class="form-control" id="smoking" name="smoking" 
                                    placeholder="如：吸烟20年，每天1包">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="drinking" class="form-label">饮酒史</label>
                                <input type="text" class="form-control" id="drinking" name="drinking" 
                                    placeholder="如：偶尔饮酒，每周1-2次">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="lifestyle" class="form-label">生活习惯</label>
                            <textarea class="form-control" id="lifestyle" name="lifestyle" rows="2"
                                placeholder="饮食偏好、运动习惯、睡眠情况等"></textarea>
                        </div>
                    </div>

                    <!-- 家族史 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-people"></i> 家族史</h4>
                        <div class="mb-3">
                            <label for="family_history" class="form-label">家族遗传病史</label>
                            <textarea class="form-control" id="family_history" name="family_history" rows="2"
                                placeholder="如：父亲高血压、母亲糖尿病等"></textarea>
                        </div>
                    </div>

                    <!-- 体格检查 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-clipboard2-pulse"></i> 体格检查</h4>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="temperature" class="form-label">体温(℃)</label>
                                <input type="number" class="form-control" id="temperature" name="temperature" 
                                    step="0.1" min="35" max="42">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="pulse" class="form-label">脉搏(次/分)</label>
                                <input type="number" class="form-control" id="pulse" name="pulse" 
                                    min="40" max="200">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="respiration" class="form-label">呼吸(次/分)</label>
                                <input type="number" class="form-control" id="respiration" name="respiration" 
                                    min="10" max="40">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="weight" class="form-label">体重(kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight" 
                                    step="0.1" min="20" max="200">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="physical_exam" class="form-label">其他体格检查发现</label>
                            <textarea class="form-control" id="physical_exam" name="physical_exam" rows="3"
                                placeholder="如：面色、精神状态、淋巴结、心肺听诊等"></textarea>
                        </div>
                    </div>

                    <!-- 中医四诊 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-yin-yang"></i> 中医四诊</h4>
                        
                        <!-- 望诊 -->
                        <div class="mb-4">
                            <h5 class="mb-3">望诊</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="complexion" class="form-label">面色</label>
                                    <select class="form-select" id="complexion" name="complexion">
                                        <option value="">请选择</option>
                                        <option value="红润">红润</option>
                                        <option value="苍白">苍白</option>
                                        <option value="萎黄">萎黄</option>
                                        <option value="潮红">潮红</option>
                                        <option value="晦暗">晦暗</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="spirit" class="form-label">精神</label>
                                    <select class="form-select" id="spirit" name="spirit">
                                        <option value="">请选择</option>
                                        <option value="神清">神清</option>
                                        <option value="疲倦">疲倦</option>
                                        <option value="萎靡">萎靡</option>
                                        <option value="烦躁">烦躁</option>
                                        <option value="嗜睡">嗜睡</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tongue" class="form-label required">舌诊</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" id="tongue_body" name="tongue_body" 
                                            placeholder="舌质（如：淡红、红、淡白、紫暗等）" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" id="tongue_coating" name="tongue_coating" 
                                            placeholder="舌苔（如：薄白、厚腻、黄腻、无苔等）" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 闻诊 -->
                        <div class="mb-4">
                            <h5 class="mb-3">闻诊</h5>
                            <div class="mb-3">
                                <label for="voice_breath" class="form-label">声音气息</label>
                                <input type="text" class="form-control" id="voice_breath" name="voice_breath" 
                                    placeholder="如：声音洪亮、低微、咳嗽、气促等">
                            </div>
                        </div>

                        <!-- 问诊（症状） -->
                        <div class="mb-4">
                            <h5 class="mb-3">问诊（症状详情）</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">寒热</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="cold" name="symptoms[]" value="恶寒">
                                            <label class="form-check-label" for="cold">恶寒</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="fever" name="symptoms[]" value="发热">
                                            <label class="form-check-label" for="fever">发热</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="alternating" name="symptoms[]" value="寒热往来">
                                            <label class="form-check-label" for="alternating">寒热往来</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">汗</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="no_sweat" name="symptoms[]" value="无汗">
                                            <label class="form-check-label" for="no_sweat">无汗</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="spontaneous" name="symptoms[]" value="自汗">
                                            <label class="form-check-label" for="spontaneous">自汗</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="night_sweat" name="symptoms[]" value="盗汗">
                                            <label class="form-check-label" for="night_sweat">盗汗</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">头身</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="headache" name="symptoms[]" value="头痛">
                                            <label class="form-check-label" for="headache">头痛</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="dizzy" name="symptoms[]" value="头晕">
                                            <label class="form-check-label" for="dizzy">头晕</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="body_pain" name="symptoms[]" value="身痛">
                                            <label class="form-check-label" for="body_pain">身痛</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sleep" class="form-label">睡眠</label>
                                    <input type="text" class="form-control" id="sleep" name="sleep" 
                                        placeholder="如：失眠、多梦、易醒、嗜睡等">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="diet_appetite" class="form-label">饮食胃纳</label>
                                    <input type="text" class="form-control" id="diet_appetite" name="diet_appetite" 
                                        placeholder="如：纳差、食欲亢进、偏食等">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="urine" class="form-label">小便</label>
                                    <input type="text" class="form-control" id="urine" name="urine" 
                                        placeholder="如：正常、频数、黄赤、清长等">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stool" class="form-label">大便</label>
                                    <input type="text" class="form-control" id="stool" name="stool" 
                                        placeholder="如：正常、便秘、腹泻、稀溏等">
                                </div>
                            </div>
                        </div>

                        <!-- 切诊 -->
                        <div class="mb-4">
                            <h5 class="mb-3">切诊</h5>
                            <div class="mb-3">
                                <label for="pulse" class="form-label required">脉诊</label>
                                <input type="text" class="form-control" id="pulse_diagnosis" name="pulse_diagnosis" 
                                    placeholder="如：浮、沉、迟、数、滑、涩、弦、细等" required>
                            </div>
                        </div>
                    </div>

                    <!-- 其他补充 -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="bi bi-journal-text"></i> 补充说明</h4>
                        <div class="mb-3">
                            <label for="additional_info" class="form-label">其他需要说明的情况</label>
                            <textarea class="form-control" id="additional_info" name="additional_info" rows="3"
                                placeholder="请补充其他您认为重要的信息"></textarea>
                        </div>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="form-section text-center">
                        <button type="button" class="btn btn-secondary me-2" onclick="saveDraft()">
                            <i class="bi bi-save"></i> 保存草稿
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-send"></i> 提交问诊
                        </button>
                    </div>
                </form>
            </div>

            <!-- 右侧提示区域 -->
            <div class="col-lg-3">
                <div class="sticky-top">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-info-circle"></i> 填写说明
                        </div>
                        <div class="card-body">
                            <ul class="small mb-0">
                                <li>带 <span class="text-danger">*</span> 的为必填项</li>
                                <li>请如实填写各项信息</li>
                                <li>症状描述越详细越好</li>
                                <li>系统会自动保存您的输入</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-warning">
                            <i class="bi bi-exclamation-triangle"></i> 重要提示
                        </div>
                        <div class="card-body">
                            <p class="small mb-0">
                                本系统仅供参考，不能替代医生的专业诊断。如有急症，请立即就医。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 自动保存提示 -->
    <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="bi bi-check-circle"></i> 已自动保存
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="/js/form-validator.js"></script>
    <script>
        // 初始化表单验证
        const validator = new FormValidator('consultationForm', {
            // 基本信息验证规则
            'name': 'required|chinese|min:2|max:20',
            'gender': 'required|in:男,女',
            'age': 'required|integer|between:1,150',
            'phone': 'phone',
            'blood_pressure': 'regex:/^\\d{2,3}-\\d{2,3}$/',
            
            // 主诉验证
            'chief_complaint': 'required|min:10|max:500',
            
            // 舌诊验证
            'tongue_body': 'required|chinese|min:2|max:50',
            'tongue_coating': 'required|chinese|min:2|max:50',
            
            // 脉诊验证
            'pulse_diagnosis': 'required|chinese|min:2|max:100'
        }, {
            // 自定义错误消息
            'name.required': '请输入患者姓名',
            'name.chinese': '姓名只能包含中文字符',
            'chief_complaint.required': '请描述您的主要症状',
            'chief_complaint.min': '症状描述至少需要10个字符',
            'blood_pressure.regex': '血压格式应为：收缩压-舒张压，如：120-80'
        });

        // 为表单字段添加标签属性（用于错误提示）
        document.querySelector('[name="name"]').setAttribute('data-label', '姓名');
        document.querySelector('[name="gender"]').setAttribute('data-label', '性别');
        document.querySelector('[name="age"]').setAttribute('data-label', '年龄');
        document.querySelector('[name="phone"]').setAttribute('data-label', '联系电话');
        document.querySelector('[name="blood_pressure"]').setAttribute('data-label', '血压');
        document.querySelector('[name="chief_complaint"]').setAttribute('data-label', '主诉');
        document.querySelector('[name="tongue_body"]').setAttribute('data-label', '舌质');
        document.querySelector('[name="tongue_coating"]').setAttribute('data-label', '舌苔');
        document.querySelector('[name="pulse_diagnosis"]').setAttribute('data-label', '脉象');

        // 表单自动保存
        let autoSaveTimer;
        const form = document.getElementById('consultationForm');
        const autoSaveIndicator = document.getElementById('autoSaveIndicator');

        // 监听表单变化
        form.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveDraft, 2000); // 2秒后自动保存
        });

        // 保存草稿
        function saveDraft() {
            const formData = new FormData(form);
            
            fetch('/index.php?url=consultation/autosave', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 显示保存成功提示
                    autoSaveIndicator.style.display = 'block';
                    setTimeout(() => {
                        autoSaveIndicator.style.display = 'none';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('保存失败:', error);
            });
        }

        // 表单提交
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 显示加载状态
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>提交中...';
            
            // 提交表单
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 跳转到结果页面
                    window.location.href = data.redirect || '/index.php?url=consultation/result/' + data.consultation_id;
                } else {
                    alert(data.message || '提交失败，请重试');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                alert('网络错误，请检查网络连接');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // 加载草稿（如果有）
        window.addEventListener('load', function() {
            // TODO: 从服务器加载草稿数据
        });
    </script>
</body>
</html> 