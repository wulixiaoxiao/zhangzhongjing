<?php
/**
 * 历史记录功能测试
 */

require_once __DIR__ . '/../app/Core/Autoloader.php';
$autoloader = new App\Core\Autoloader();
$autoloader->register();

use App\Core\Database;

// 测试配置
$tests = [];

try {
    // 测试1：访问历史记录列表页
    $tests[] = [
        'name' => '访问历史记录列表页',
        'url' => '/history',
        'status' => 'info',
        'message' => '可以通过访问 /history 查看历史记录列表'
    ];
    
    // 测试2：检查问诊记录数量
    $count = Database::selectOne("SELECT COUNT(*) as total FROM consultations");
    $tests[] = [
        'name' => '问诊记录数量',
        'status' => 'success',
        'message' => "数据库中有 {$count['total']} 条问诊记录"
    ];
    
    // 测试3：检查已完成的问诊
    $completed = Database::selectOne("SELECT COUNT(*) as total FROM consultations WHERE status = '已完成'");
    $tests[] = [
        'name' => '已完成问诊数量',
        'status' => 'success',
        'message' => "已完成的问诊有 {$completed['total']} 条"
    ];
    
    // 测试4：获取最新的问诊记录
    $latest = Database::selectOne("
        SELECT c.*, p.name as patient_name 
        FROM consultations c 
        JOIN patients p ON c.patient_id = p.id 
        ORDER BY c.consultation_date DESC 
        LIMIT 1
    ");
    
    if ($latest) {
        $tests[] = [
            'name' => '最新问诊记录',
            'status' => 'success',
            'message' => "最新问诊：{$latest['patient_name']} - {$latest['consultation_no']} ({$latest['consultation_date']})"
        ];
        
        // 测试5：查看详情页
        $tests[] = [
            'name' => '查看问诊详情',
            'url' => "/history/detail/{$latest['id']}",
            'status' => 'info',
            'message' => "可以访问 /history/detail/{$latest['id']} 查看详情"
        ];
    }
    
    // 测试6：测试搜索功能
    $tests[] = [
        'name' => '搜索功能',
        'url' => '/history?search=头痛',
        'status' => 'info',
        'message' => '可以使用搜索参数筛选记录'
    ];
    
    // 测试7：测试分页
    $tests[] = [
        'name' => '分页功能',
        'url' => '/history?page=2&pageSize=5',
        'status' => 'info',
        'message' => '支持分页参数：page 和 pageSize'
    ];
    
    // 测试8：按状态筛选
    $tests[] = [
        'name' => '状态筛选',
        'url' => '/history?status=已完成',
        'status' => 'info',
        'message' => '可以按状态筛选：待诊断、诊断中、已完成、已取消'
    ];
    
    // 测试9：患者历史记录
    $patient = Database::selectOne("SELECT id, name FROM patients LIMIT 1");
    if ($patient) {
        $tests[] = [
            'name' => '患者历史记录',
            'url' => "/history/patient/{$patient['id']}",
            'status' => 'info',
            'message' => "查看患者 {$patient['name']} 的所有历史记录"
        ];
    }
    
    // 测试10：导出功能
    $tests[] = [
        'name' => '导出JSON功能',
        'url' => '/history/export-json',
        'status' => 'info',
        'message' => '可以导出搜索结果为JSON格式'
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
    <title>历史记录功能测试</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="mb-4"><i class="bi bi-clock-history"></i> 历史记录功能测试</h1>
        
        <div class="card">
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
                                <a href="<?= $test['url'] ?>" class="btn btn-sm btn-primary mt-1" target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i> 访问
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>功能说明</h5>
            <ul>
                <li><strong>历史记录列表</strong>：显示所有问诊记录，支持搜索、筛选和分页</li>
                <li><strong>问诊详情</strong>：查看完整的问诊信息、诊断结果和处方</li>
                <li><strong>患者历史</strong>：查看特定患者的所有问诊历史时间线</li>
                <li><strong>数据导出</strong>：将筛选后的记录导出为JSON格式</li>
                <li><strong>状态管理</strong>：可以取消未完成的问诊记录</li>
            </ul>
        </div>
        
        <div class="mt-4">
            <a href="/" class="btn btn-secondary">返回首页</a>
            <a href="/history" class="btn btn-primary">访问历史记录</a>
        </div>
    </div>
</body>
</html> 