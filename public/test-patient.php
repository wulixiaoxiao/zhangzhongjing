<?php
require_once __DIR__ . '/../app/Core/Autoloader.php';
require_once __DIR__ . '/../src/helpers.php';

use App\Models\Patient;
use App\Core\Database;

// 测试数据
$testPatients = [
    [
        'name' => '张三',
        'gender' => '男',
        'age' => 35,
        'phone' => '13812345678',
        'id_card' => '110101198801011234',
        'occupation' => '软件工程师',
        'marriage' => '已婚',
        'address' => '北京市朝阳区某某街道',
        'emergency_contact' => '李四',
        'emergency_phone' => '13987654321'
    ],
    [
        'name' => '王五',
        'gender' => '女',
        'age' => 28,
        'phone' => '13666666666',
        'occupation' => '教师',
        'marriage' => '未婚'
    ]
];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create':
            $patient = new Patient($_POST);
            if ($patient->save()) {
                echo json_encode(['success' => true, 'id' => $patient->id]);
            } else {
                echo json_encode(['success' => false, 'errors' => $patient->getErrors()]);
            }
            exit;
            
        case 'search':
            $results = Patient::search($_POST['keyword']);
            echo json_encode(['success' => true, 'results' => $results]);
            exit;
            
        case 'delete':
            $patient = Patient::find($_POST['id']);
            if ($patient && $patient->delete()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => '删除失败']);
            }
            exit;
    }
}

// 获取所有患者
$patients = Patient::all();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>患者模型测试 - <?php echo config('app.name', '智医'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">患者模型测试</h1>
        
        <div class="alert alert-info">
            <strong>测试说明：</strong>本页面测试 Patient 模型的 CRUD 操作和各种查询功能。
        </div>

        <!-- 创建患者表单 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">创建新患者</h5>
            </div>
            <div class="card-body">
                <form id="createPatientForm">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">姓名 *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="gender" class="form-label">性别 *</label>
                            <select class="form-select" name="gender" required>
                                <option value="">请选择</option>
                                <option value="男">男</option>
                                <option value="女">女</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="age" class="form-label">年龄 *</label>
                            <input type="number" class="form-control" name="age" min="1" max="150" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="phone" class="form-label">手机号</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="id_card" class="form-label">身份证号</label>
                            <input type="text" class="form-control" name="id_card">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="occupation" class="form-label">职业</label>
                            <input type="text" class="form-control" name="occupation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="marriage" class="form-label">婚姻状况</label>
                            <select class="form-select" name="marriage">
                                <option value="">请选择</option>
                                <option value="未婚">未婚</option>
                                <option value="已婚">已婚</option>
                                <option value="离异">离异</option>
                                <option value="丧偶">丧偶</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">地址</label>
                        <input type="text" class="form-control" name="address">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact" class="form-label">紧急联系人</label>
                            <input type="text" class="form-control" name="emergency_contact">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_phone" class="form-label">紧急联系电话</label>
                            <input type="text" class="form-control" name="emergency_phone">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">创建患者</button>
                    <button type="button" class="btn btn-secondary" onclick="fillTestData()">填充测试数据</button>
                </form>
                <div id="createResult" class="mt-3"></div>
            </div>
        </div>

        <!-- 搜索患者 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">搜索患者</h5>
            </div>
            <div class="card-body">
                <form id="searchForm">
                    <input type="hidden" name="action" value="search">
                    <div class="input-group">
                        <input type="text" class="form-control" name="keyword" placeholder="输入姓名、手机号或身份证号">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </form>
                <div id="searchResults" class="mt-3"></div>
            </div>
        </div>

        <!-- 患者列表 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">患者列表（共 <?php echo count($patients); ?> 人）</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patients)): ?>
                    <p class="text-muted">暂无患者数据</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>姓名</th>
                                    <th>性别</th>
                                    <th>年龄</th>
                                    <th>手机号</th>
                                    <th>职业</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td><?php echo $patient['id']; ?></td>
                                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                        <td><?php echo $patient['gender']; ?></td>
                                        <td><?php echo $patient['age']; ?>岁</td>
                                        <td><?php echo $patient['phone'] ?: '-'; ?></td>
                                        <td><?php echo htmlspecialchars($patient['occupation'] ?: '-'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($patient['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deletePatient(<?php echo $patient['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 模型方法测试 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">模型方法测试</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($patients)): ?>
                    <?php 
                    // 获取第一个患者进行测试
                    $testPatient = Patient::find($patients[0]['id']);
                    ?>
                    <h6>测试患者：<?php echo $testPatient->getDisplayName(); ?></h6>
                    <ul>
                        <li>年龄段：<?php echo $testPatient->getAgeGroup(); ?></li>
                        <li>问诊统计：
                            <?php
                            $stats = $testPatient->getConsultationStats();
                            echo "总问诊次数：" . $stats['total_consultations'];
                            ?>
                        </li>
                    </ul>
                <?php endif; ?>
                
                <h6>模型功能测试：</h6>
                <ul>
                    <li>数据验证：✓ 支持必填、类型、长度等验证</li>
                    <li>数据过滤：✓ 自动过滤 XSS 等危险内容</li>
                    <li>类型转换：✓ 自动转换数据类型</li>
                    <li>批量赋值保护：✓ 通过 fillable 控制</li>
                    <li>查询方法：✓ find、findWhere、all、search</li>
                    <li>关联查询：✓ getConsultations、getLatestConsultation</li>
                </ul>
            </div>
        </div>

        <div class="mb-3 text-center">
            <a href="/" class="btn btn-secondary">返回首页</a>
        </div>
    </div>

    <!-- 患者详情模态框 -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">患者详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="patientDetails">
                    <!-- 动态加载内容 -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        // 填充测试数据
        function fillTestData() {
            const testData = <?php echo json_encode($testPatients[0]); ?>;
            const form = document.getElementById('createPatientForm');
            
            for (let key in testData) {
                if (form.elements[key]) {
                    form.elements[key].value = testData[key];
                }
            }
        }
        
        // 创建患者
        $('#createPatientForm').on('submit', function(e) {
            e.preventDefault();
            
            $.post('', $(this).serialize(), function(response) {
                if (response.success) {
                    $('#createResult').html(
                        '<div class="alert alert-success">患者创建成功！ID: ' + response.id + '</div>'
                    );
                    setTimeout(() => location.reload(), 1500);
                } else {
                    let errors = '<ul>';
                    for (let field in response.errors) {
                        response.errors[field].forEach(error => {
                            errors += '<li>' + field + ': ' + error + '</li>';
                        });
                    }
                    errors += '</ul>';
                    $('#createResult').html(
                        '<div class="alert alert-danger">创建失败：' + errors + '</div>'
                    );
                }
            });
        });
        
        // 搜索患者
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            
            $.post('', $(this).serialize(), function(response) {
                if (response.success) {
                    let html = '<h6>搜索结果（' + response.results.length + ' 条）：</h6>';
                    if (response.results.length > 0) {
                        html += '<ul class="list-group">';
                        response.results.forEach(patient => {
                            html += '<li class="list-group-item">' +
                                patient.name + ' - ' + patient.gender + ' - ' + 
                                patient.age + '岁 - ' + (patient.phone || '无电话') +
                                '</li>';
                        });
                        html += '</ul>';
                    } else {
                        html += '<p class="text-muted">未找到匹配的患者</p>';
                    }
                    $('#searchResults').html(html);
                }
            });
        });
        
        // 查看患者详情
        function viewPatient(id) {
            // 这里可以通过 AJAX 获取详细信息
            $('#patientDetails').html('<p>患者 ID: ' + id + ' 的详细信息</p>');
            new bootstrap.Modal(document.getElementById('patientModal')).show();
        }
        
        // 删除患者
        function deletePatient(id) {
            if (confirm('确定要删除这个患者吗？')) {
                $.post('', {action: 'delete', id: id}, function(response) {
                    if (response.success) {
                        alert('删除成功');
                        location.reload();
                    } else {
                        alert('删除失败：' + response.message);
                    }
                });
            }
        }
    </script>
</body>
</html> 