<?php
/**
 * 数据导出功能测试
 */

require_once __DIR__ . '/../app/Core/Autoloader.php';
$autoloader = new App\Core\Autoloader();
$autoloader->register();

use App\Core\Database;

// 测试配置
$tests = [];

try {
    // 测试1：导出功能列表
    $tests[] = [
        'name' => '导出功能概览',
        'status' => 'info',
        'message' => '系统支持多种格式的数据导出：CSV、Excel、JSON、PDF'
    ];
    
    // 测试2：导出CSV功能
    $tests[] = [
        'name' => '导出问诊记录（CSV）',
        'url' => '/history/export-csv',
        'status' => 'info',
        'message' => '导出所有问诊记录为CSV格式，支持筛选条件'
    ];
    
    // 测试3：导出Excel功能
    $tests[] = [
        'name' => '导出问诊记录（Excel）',
        'url' => '/history/export-excel',
        'status' => 'info',
        'message' => '导出所有问诊记录为Excel格式（HTML表格）'
    ];
    
    // 测试4：导出JSON功能
    $tests[] = [
        'name' => '导出问诊记录（JSON）',
        'url' => '/history/export-json',
        'status' => 'info',
        'message' => '导出所有问诊记录为JSON格式，包含完整数据'
    ];
    
    // 测试5：导出患者列表
    $tests[] = [
        'name' => '导出患者列表',
        'url' => '/history/export-patients',
        'status' => 'info',
        'message' => '导出所有患者信息和统计数据'
    ];
    
    // 测试6：导出单个问诊详情
    $consultation = Database::selectOne("SELECT id, consultation_no FROM consultations LIMIT 1");
    if ($consultation) {
        $tests[] = [
            'name' => '导出单个问诊（CSV）',
            'url' => "/history/export-detail/{$consultation['id']}?format=csv",
            'status' => 'info',
            'message' => "导出问诊 {$consultation['consultation_no']} 的详细信息"
        ];
        
        $tests[] = [
            'name' => '导出单个问诊（Excel）',
            'url' => "/history/export-detail/{$consultation['id']}?format=excel",
            'status' => 'info',
            'message' => "导出问诊 {$consultation['consultation_no']} 为Excel格式"
        ];
    }
    
    // 测试7：带筛选条件的导出
    $tests[] = [
        'name' => '带筛选条件的导出',
        'url' => '/history/export-csv?status=已完成&start_date=' . date('Y-m-01'),
        'status' => 'info',
        'message' => '可以带筛选条件导出：状态、日期范围、患者等'
    ];
    
    // 测试8：检查导出数据的完整性
    $checkSql = "
        SELECT COUNT(DISTINCT c.id) as total_consultations,
               COUNT(DISTINCT p.id) as total_patients,
               COUNT(DISTINCT d.id) as with_diagnosis,
               COUNT(DISTINCT pr.id) as with_prescription
        FROM consultations c
        JOIN patients p ON c.patient_id = p.id
        LEFT JOIN diagnoses d ON d.consultation_id = c.id
        LEFT JOIN prescriptions pr ON pr.diagnosis_id = d.id
    ";
    
    $stats = Database::selectOne($checkSql);
    $tests[] = [
        'name' => '数据统计',
        'status' => 'success',
        'message' => "问诊记录: {$stats['total_consultations']} | 患者: {$stats['total_patients']} | 有诊断: {$stats['with_diagnosis']} | 有处方: {$stats['with_prescription']}"
    ];
    
} catch (Exception $e) {
    $tests[] = [
        'name' => '错误',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>数据导出功能测试</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="mb-4"><i class="bi bi-download"></i> 数据导出功能测试</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">测试结果</h5>
                
                <?php foreach ($tests as $test): ?>
                    <div class="alert alert-<?= $test['status'] === 'error' ? 'danger' : ($test['status'] === 'success' ? 'success' : 'info') ?> d-flex align-items-center">
                        <i class="bi bi-<?= $test['status'] === 'error' ? 'x-circle' : ($test['status'] === 'success' ? 'check-circle' : 'info-circle') ?> me-2"></i>
                        <div>
                            <strong><?= htmlspecialchars($test['name']) ?>:</strong>
                            <?= htmlspecialchars($test['message']) ?>
                            <?php if (isset($test['url'])): ?>
                                <br>
                                <a href="<?= $test['url'] ?>" class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-download"></i> 下载
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">支持的导出格式</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-file-earmark-text text-success"></i> CSV格式</h6>
                        <ul>
                            <li>通用格式，可用Excel/WPS等打开</li>
                            <li>支持UTF-8编码，正确显示中文</li>
                            <li>适合数据分析和批量处理</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-file-earmark-excel text-success"></i> Excel格式</h6>
                        <ul>
                            <li>HTML表格格式，Excel可直接打开</li>
                            <li>保留表格样式和边框</li>
                            <li>适合直接查看和打印</li>
                        </ul>
                    </div>
                    <div class="col-md-6 mt-3">
                        <h6><i class="bi bi-filetype-json text-primary"></i> JSON格式</h6>
                        <ul>
                            <li>完整的数据结构</li>
                            <li>适合程序处理和数据迁移</li>
                            <li>包含所有字段信息</li>
                        </ul>
                    </div>
                    <div class="col-md-6 mt-3">
                        <h6><i class="bi bi-file-pdf text-danger"></i> PDF格式</h6>
                        <ul>
                            <li>专业的打印格式</li>
                            <li>包含中药处方的完整信息</li>
                            <li>适合归档和发送给患者</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">使用说明</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li><strong>批量导出</strong>：在历史记录列表页点击"导出数据"按钮，选择需要的格式</li>
                    <li><strong>筛选导出</strong>：先设置筛选条件（日期、状态等），再导出筛选后的结果</li>
                    <li><strong>单个导出</strong>：在问诊详情页可以导出单个问诊的完整信息</li>
                    <li><strong>患者列表</strong>：导出所有患者的基本信息和问诊统计</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>注意：</strong>导出的数据包含患者隐私信息，请妥善保管，遵守相关法律法规。
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="/" class="btn btn-secondary">返回首页</a>
            <a href="/history" class="btn btn-primary">访问历史记录</a>
        </div>
    </div>
</body>
</html> 