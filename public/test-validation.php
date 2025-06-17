<?php
require_once __DIR__ . '/../app/Core/Autoloader.php';
require_once __DIR__ . '/../src/helpers.php';

use App\Core\Validator;

$testData = [
    '有效数据' => [
        'data' => [
            'name' => '张三',
            'gender' => '男',
            'age' => '35',
            'phone' => '13812345678',
            'blood_pressure' => '120-80',
            'chief_complaint' => '头晕头痛三天，伴有恶心呕吐症状',
            'tongue_body' => '舌质淡红',
            'tongue_coating' => '舌苔薄白',
            'pulse_diagnosis' => '脉弦细'
        ],
        'rules' => [
            'name' => 'required|chinese|min:2|max:20',
            'gender' => 'required|in:男,女',
            'age' => 'required|integer|between:1,150',
            'phone' => 'phone',
            'blood_pressure' => 'regex:/^\d{2,3}-\d{2,3}$/',
            'chief_complaint' => 'required|min:10|max:500',
            'tongue_body' => 'required|chinese|min:2|max:50',
            'tongue_coating' => 'required|chinese|min:2|max:50',
            'pulse_diagnosis' => 'required|chinese|min:2|max:100'
        ]
    ],
    '无效数据' => [
        'data' => [
            'name' => 'John123',  // 包含英文和数字
            'gender' => '其他',   // 不在允许的值中
            'age' => '200',       // 超出范围
            'phone' => '1234567', // 格式错误
            'blood_pressure' => '120/80',  // 格式错误，应该用-分隔
            'chief_complaint' => '头痛',    // 太短
            'tongue_body' => 'Red',         // 英文
            'tongue_coating' => '苔',       // 太短
            'pulse_diagnosis' => ''         // 空值
        ],
        'rules' => [
            'name' => 'required|chinese|min:2|max:20',
            'gender' => 'required|in:男,女',
            'age' => 'required|integer|between:1,150',
            'phone' => 'phone',
            'blood_pressure' => 'regex:/^\d{2,3}-\d{2,3}$/',
            'chief_complaint' => 'required|min:10|max:500',
            'tongue_body' => 'required|chinese|min:2|max:50',
            'tongue_coating' => 'required|chinese|min:2|max:50',
            'pulse_diagnosis' => 'required|chinese|min:2|max:100'
        ]
    ],
    '身份证验证' => [
        'data' => [
            'id_card_valid' => '110101199001011234',   // 有效格式（示例）
            'id_card_invalid' => '123456789012345678', // 无效格式
            'id_card_short' => '12345678901234567'     // 长度不对
        ],
        'rules' => [
            'id_card_valid' => 'id_card',
            'id_card_invalid' => 'id_card',
            'id_card_short' => 'id_card'
        ]
    ],
    '数据过滤测试' => [
        'data' => [
            'unsafe_text' => '<script>alert("XSS")</script>你好',
            'safe_number' => '123.45abc',
            'safe_email' => 'test@example..com',
            'mixed_text' => '正常文本<iframe src="hack.com"></iframe>'
        ],
        'rules' => [
            'unsafe_text' => 'safe_text',
            'safe_number' => 'numeric',
            'safe_email' => 'email',
            'mixed_text' => 'safe_text'
        ]
    ]
];

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>表单验证测试 - <?php echo config('app.name', '智医'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">表单验证测试</h1>
        
        <div class="alert alert-info">
            <strong>测试说明：</strong>本页面测试后端 Validator 类的各种验证规则和数据过滤功能。
        </div>

        <?php foreach ($testData as $testName => $test): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $testName; ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $validator = new Validator();
                    $isValid = $validator->validate($test['data'], $test['rules']);
                    ?>
                    
                    <h6>测试数据：</h6>
                    <pre class="bg-light p-3"><?php print_r($test['data']); ?></pre>
                    
                    <h6>验证规则：</h6>
                    <pre class="bg-light p-3"><?php print_r($test['rules']); ?></pre>
                    
                    <h6>验证结果：</h6>
                    <div class="alert <?php echo $isValid ? 'alert-success' : 'alert-danger'; ?>">
                        <?php if ($isValid): ?>
                            <strong>✓ 验证通过</strong>
                        <?php else: ?>
                            <strong>✗ 验证失败</strong>
                            <hr>
                            <strong>错误信息：</strong>
                            <ul class="mb-0">
                                <?php foreach ($validator->getErrors() as $field => $errors): ?>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $field; ?>: <?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($testName === '数据过滤测试'): ?>
                        <h6>数据过滤结果：</h6>
                        <?php
                        $filtered = Validator::sanitize($test['data'], [
                            'unsafe_text' => 'safe',
                            'safe_number' => 'float',
                            'safe_email' => 'email',
                            'mixed_text' => 'safe'
                        ]);
                        ?>
                        <pre class="bg-light p-3"><?php print_r($filtered); ?></pre>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0">前端验证测试</h5>
            </div>
            <div class="card-body">
                <form id="testForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="test_name" class="form-label">姓名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="test_name" name="test_name" data-label="姓名">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="test_phone" class="form-label">手机号</label>
                            <input type="text" class="form-control" id="test_phone" name="test_phone" data-label="手机号">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="test_complaint" class="form-label">症状描述 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="test_complaint" name="test_complaint" rows="3" data-label="症状描述"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">提交测试</button>
                </form>
            </div>
        </div>
        
        <div class="mb-3 text-center">
            <a href="/" class="btn btn-secondary">返回首页</a>
        </div>
    </div>

    <script src="/js/form-validator.js"></script>
    <script>
        // 初始化前端验证
        const testValidator = new FormValidator('testForm', {
            'test_name': 'required|chinese|min:2|max:20',
            'test_phone': 'phone',
            'test_complaint': 'required|min:10|max:100'
        }, {
            'test_name.required': '请输入姓名',
            'test_name.chinese': '姓名只能包含中文',
            'test_complaint.required': '请描述症状',
            'test_complaint.min': '症状描述至少10个字符'
        });
        
        // 表单提交事件
        document.getElementById('testForm').addEventListener('submit', function(e) {
            if (e.defaultPrevented) {
                console.log('表单验证失败，已阻止提交');
            } else {
                e.preventDefault();
                alert('前端验证通过！');
            }
        });
    </script>
</body>
</html> 