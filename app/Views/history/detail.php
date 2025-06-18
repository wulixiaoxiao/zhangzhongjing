<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- 页面标题 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-file-medical"></i> 问诊详情
                    <small class="text-muted">#<?= htmlspecialchars($consultation->consultation_no) ?></small>
                </h2>
                <div>
                    <a href="/history" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 返回列表
                    </a>
                    <?php if ($diagnosis && $prescription): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download"></i> 导出
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="/consultation/pdf/<?= $consultation->id ?>" target="_blank">
                                        <i class="bi bi-file-pdf"></i> 导出为 PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/history/export-detail/<?= $consultation->id ?>?format=csv">
                                        <i class="bi bi-file-earmark-text"></i> 导出为 CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/history/export-detail/<?= $consultation->id ?>?format=excel">
                                        <i class="bi bi-file-earmark-excel"></i> 导出为 Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
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
                        <div class="col-md-3">
                            <strong>职业：</strong><?= htmlspecialchars($patient->occupation ?: '-') ?>
                        </div>
                        <div class="col-md-3">
                            <strong>婚姻状况：</strong><?= htmlspecialchars($patient->marriage ?: '-') ?>
                        </div>
                        <div class="col-md-6">
                            <strong>住址：</strong><?= htmlspecialchars($patient->address ?: '-') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 问诊信息 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> 问诊信息</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>问诊时间：</strong>
                            <?= date('Y-m-d H:i:s', strtotime($consultation->consultation_date)) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>状态：</strong>
                            <?php
                            $statusClass = [
                                '待诊断' => 'warning',
                                '诊断中' => 'info', 
                                '已完成' => 'success',
                                '已取消' => 'secondary'
                            ][$consultation->status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= htmlspecialchars($consultation->status) ?>
                            </span>
                        </div>
                    </div>
                    
                    <h6 class="text-primary">主诉</h6>
                    <p><?= nl2br(htmlspecialchars($consultation->chief_complaint)) ?></p>
                    
                    <?php if ($consultation->present_illness): ?>
                        <h6 class="text-primary">现病史</h6>
                        <p><?= nl2br(htmlspecialchars($consultation->present_illness)) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($consultation->past_history): ?>
                        <h6 class="text-primary">既往史</h6>
                        <p><?= nl2br(htmlspecialchars($consultation->past_history)) ?></p>
                    <?php endif; ?>
                    
                    <!-- 中医四诊 -->
                    <h6 class="text-primary">中医四诊</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>面色：</strong><?= htmlspecialchars($consultation->complexion ?: '-') ?></li>
                                <li><strong>精神：</strong><?= htmlspecialchars($consultation->spirit ?: '-') ?></li>
                                <li><strong>声音气息：</strong><?= htmlspecialchars($consultation->voice_breath ?: '-') ?></li>
                                <li><strong>睡眠：</strong><?= htmlspecialchars($consultation->sleep_quality ?: '-') ?></li>
                                <li><strong>饮食：</strong><?= htmlspecialchars($consultation->diet_appetite ?: '-') ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>舌质：</strong><?= htmlspecialchars($consultation->tongue_body) ?></li>
                                <li><strong>舌苔：</strong><?= htmlspecialchars($consultation->tongue_coating) ?></li>
                                <li><strong>脉象：</strong><?= htmlspecialchars($consultation->pulse_diagnosis) ?></li>
                                <li><strong>小便：</strong><?= htmlspecialchars($consultation->urine ?: '-') ?></li>
                                <li><strong>大便：</strong><?= htmlspecialchars($consultation->stool ?: '-') ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <?php if ($consultation->symptoms): ?>
                        <h6 class="text-primary">症状集合</h6>
                        <p><?= nl2br(htmlspecialchars($consultation->symptoms)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 诊断结果 -->
            <?php if ($diagnosis): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> 诊断结果</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>诊断时间：</strong>
                                <?= date('Y-m-d H:i:s', strtotime($diagnosis->diagnosis_time)) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>AI模型：</strong>
                                <?= htmlspecialchars($diagnosis->ai_model) ?>
                                <?php if ($diagnosis->ai_confidence): ?>
                                    <small class="text-muted">
                                        (置信度: <?= round($diagnosis->ai_confidence, 2) ?>%)
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h6 class="text-success">证型</h6>
                        <p class="fw-bold"><?= htmlspecialchars($diagnosis->syndrome) ?></p>
                        
                        <h6 class="text-success">辨证分析</h6>
                        <p><?= nl2br(htmlspecialchars($diagnosis->syndrome_analysis)) ?></p>
                        
                        <h6 class="text-success">治法</h6>
                        <p><?= nl2br(htmlspecialchars($diagnosis->treatment_principle)) ?></p>
                        
                        <?php if ($diagnosis->medical_advice): ?>
                            <h6 class="text-success">医嘱建议</h6>
                            <p><?= nl2br(htmlspecialchars($diagnosis->medical_advice)) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($diagnosis->lifestyle_advice): ?>
                            <h6 class="text-success">生活建议</h6>
                            <p><?= nl2br(htmlspecialchars($diagnosis->lifestyle_advice)) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($diagnosis->dietary_advice): ?>
                            <h6 class="text-success">饮食建议</h6>
                            <p><?= nl2br(htmlspecialchars($diagnosis->dietary_advice)) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($diagnosis->followup_advice): ?>
                            <h6 class="text-success">复诊建议</h6>
                            <p><?= nl2br(htmlspecialchars($diagnosis->followup_advice)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 处方信息 -->
            <?php if ($prescription): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-prescription2"></i> 中药处方</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>处方编号：</strong>
                                <?= htmlspecialchars($prescription->prescription_no) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>方剂名称：</strong>
                                <?= htmlspecialchars($prescription->prescription_name) ?>
                            </div>
                        </div>
                        
                        <h6 class="text-info">药物组成</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>药材名称</th>
                                        <th>剂量</th>
                                        <th>单位</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prescription->herbs_array as $herb): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($herb['name']) ?></td>
                                            <td><?= htmlspecialchars($herb['dosage']) ?></td>
                                            <td><?= htmlspecialchars($herb['unit']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>用法：</strong>
                                <?= htmlspecialchars($prescription->usage_method) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>用量：</strong>
                                <?= htmlspecialchars($prescription->dosage) ?>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>频次：</strong>
                                <?= htmlspecialchars($prescription->frequency) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>疗程：</strong>
                                <?= htmlspecialchars($prescription->duration) ?>
                            </div>
                        </div>
                        
                        <?php if ($prescription->precautions): ?>
                            <div class="mt-3">
                                <strong>注意事项：</strong>
                                <p><?= nl2br(htmlspecialchars($prescription->precautions)) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($prescription->contraindications): ?>
                            <div class="mt-2">
                                <strong>禁忌：</strong>
                                <p><?= nl2br(htmlspecialchars($prescription->contraindications)) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 操作按钮 -->
            <div class="text-center mb-4">
                <a href="/history" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 返回列表
                </a>
                <a href="/history/patient/<?= $patient->id ?>" class="btn btn-info">
                    <i class="bi bi-person-lines-fill"></i> 查看患者历史
                </a>
                <?php if ($consultation->status === '待诊断'): ?>
                    <a href="/consultation/continue/<?= $consultation->id ?>" 
                       class="btn btn-success">
                        <i class="bi bi-play-circle"></i> 继续诊断
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?> 