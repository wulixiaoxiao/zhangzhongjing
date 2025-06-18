<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- 页面标题 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-person-lines-fill"></i> 患者历史记录
                </h2>
                <a href="/history" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回列表
                </a>
            </div>
            
            <!-- 患者信息卡片 -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> 患者信息</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>姓名：</strong><?= htmlspecialchars($patient->name) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>性别：</strong><?= htmlspecialchars($patient->gender) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>年龄：</strong><?= $patient->age ?>岁
                        </div>
                        <div class="col-md-3">
                            <strong>电话：</strong><?= htmlspecialchars($patient->phone) ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>身份证号：</strong><?= htmlspecialchars($patient->id_card ?: '-') ?>
                        </div>
                        <div class="col-md-4">
                            <strong>职业：</strong><?= htmlspecialchars($patient->occupation ?: '-') ?>
                        </div>
                        <div class="col-md-4">
                            <strong>婚姻状况：</strong><?= htmlspecialchars($patient->marriage ?: '-') ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>住址：</strong><?= htmlspecialchars($patient->address ?: '-') ?>
                        </div>
                    </div>
                    
                    <!-- 统计信息 -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary"><?= $stats['total_consultations'] ?></h3>
                                <small class="text-muted">总问诊次数</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success"><?= $stats['completed'] ?></h3>
                                <small class="text-muted">已完成</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning"><?= $stats['pending'] ?></h3>
                                <small class="text-muted">待诊断</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">
                                    <?= $stats['last_visit'] ? date('Y-m-d', strtotime($stats['last_visit'])) : '-' ?>
                                </h3>
                                <small class="text-muted">最近就诊</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 问诊历史时间线 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> 问诊历史</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($consultations)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">该患者暂无问诊记录</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($consultations as $index => $consultation): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker <?= $consultation['status'] === '已完成' ? 'bg-success' : 'bg-warning' ?>"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                <a href="/history/detail/<?= $consultation['id'] ?>">
                                                    问诊编号：<?= htmlspecialchars($consultation['consultation_no']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?= date('Y-m-d H:i', strtotime($consultation['consultation_date'])) ?>
                                            </small>
                                        </div>
                                        
                                        <p class="mb-2">
                                            <strong>主诉：</strong>
                                            <?= htmlspecialchars($consultation['chief_complaint']) ?>
                                        </p>
                                        
                                        <?php if ($consultation['syndrome']): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($consultation['syndrome']) ?>
                                                </span>
                                                <small class="text-muted ms-2">
                                                    <?= htmlspecialchars($consultation['treatment_principle']) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-<?= $consultation['status'] === '已完成' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($consultation['status']) ?>
                                            </span>
                                            <div>
                                                <a href="/history/detail/<?= $consultation['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> 查看详情
                                                </a>
                                                <?php if ($consultation['status'] === '待诊断'): ?>
                                                    <a href="/consultation/continue/<?= $consultation['id'] ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-play-circle"></i> 继续诊断
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="text-center mt-4">
                <a href="/consultation?patient_id=<?= $patient->id ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> 新建问诊
                </a>
                <a href="/history" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回列表
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* 时间线样式 */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.timeline-item:last-child {
    margin-bottom: 0;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?> 