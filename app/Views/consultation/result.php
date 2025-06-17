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
        .result-section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }
        .diagnosis-section {
            margin-bottom: 30px;
        }
        .diagnosis-section h5 {
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .diagnosis-section h5 i {
            margin-right: 8px;
        }
        .herb-item {
            display: inline-block;
            background: #f8f9fa;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 4px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .result-section {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 no-print">
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
                        <a class="nav-link" href="/consultation/form">新建问诊</a>
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
        <nav aria-label="breadcrumb" class="no-print">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item"><a href="/consultation">问诊管理</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-12">
                <!-- 诊断结果区域 -->
                <div class="result-section">
                    <h4 class="mb-4"><i class="bi bi-file-medical"></i> 诊断报告</h4>
                    
                    <?php if ($status === 'submitted' || $status === 'processing'): ?>
                    <!-- 加载状态 -->
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">AI 正在分析您的问诊信息，请稍候...</p>
                            <small class="text-secondary">问诊编号：<?= htmlspecialchars($consultation->consultation_no) ?></small>
                        </div>
                    </div>
                    
                    <?php elseif ($status === 'failed'): ?>
                    <!-- 失败状态 -->
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>诊断失败</strong>
                        <p>AI 诊断过程中出现错误，请稍后重试或联系管理员。</p>
                        <p class="mb-0">问诊编号：<?= htmlspecialchars($consultation->consultation_no) ?></p>
                    </div>
                    
                    <?php elseif ($status === 'completed' && $diagnosis): ?>
                    <!-- 诊断结果 -->
                    <div id="resultContent">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5><i class="bi bi-person"></i> 患者信息</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="30%">姓名：</td>
                                        <td><?= htmlspecialchars($patient->name ?? '未知') ?></td>
                                    </tr>
                                    <tr>
                                        <td>性别：</td>
                                        <td><?= htmlspecialchars($patient->gender ?? '未知') ?></td>
                                    </tr>
                                    <tr>
                                        <td>年龄：</td>
                                        <td><?= htmlspecialchars($patient->age ?? '未知') ?>岁</td>
                                    </tr>
                                    <tr>
                                        <td>问诊编号：</td>
                                        <td><?= htmlspecialchars($consultation->consultation_no) ?></td>
                                    </tr>
                                    <tr>
                                        <td>问诊时间：</td>
                                        <td><?= date('Y-m-d H:i:s', strtotime($consultation->created_at)) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bi bi-clipboard-pulse"></i> 主诉</h5>
                                <p><?= nl2br(htmlspecialchars($consultation->chief_complaint)) ?></p>
                            </div>
                        </div>

                        <hr>

                        <?php if (!empty($diagnosis['syndrome_analysis'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-diagram-3"></i> 辨证分析</h5>
                            <div class="alert alert-light">
                                <?= nl2br(htmlspecialchars($diagnosis['syndrome_analysis'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['treatment_principle'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-bullseye"></i> 治疗原则</h5>
                            <p><?= nl2br(htmlspecialchars($diagnosis['treatment_principle'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['diagnosis_result'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-clipboard-check"></i> 诊断结果</h5>
                            <p><?= nl2br(htmlspecialchars($diagnosis['diagnosis_result'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($prescription): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-prescription2"></i> 中药处方</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($prescription['prescription_name']) ?></h6>
                                    
                                    <?php if (!empty($prescription['herbs'])): ?>
                                    <p class="card-text">
                                        <strong>药物组成：</strong><br>
                                        <?php 
                                        $herbs = is_string($prescription['herbs']) ? json_decode($prescription['herbs'], true) : $prescription['herbs'];
                                        if (is_array($herbs)):
                                            foreach ($herbs as $herb):
                                                if (is_array($herb)):
                                        ?>
                                            <span class="herb-item">
                                                <?= htmlspecialchars($herb['name']) ?> 
                                                <?= htmlspecialchars($herb['dosage'] ?? '') ?><?= htmlspecialchars($herb['unit'] ?? '克') ?>
                                            </span>
                                        <?php 
                                                else:
                                        ?>
                                            <span class="herb-item"><?= htmlspecialchars($herb) ?></span>
                                        <?php 
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <p class="card-text">
                                        <strong>用法用量：</strong><?= htmlspecialchars($prescription['dosage']) ?>
                                    </p>
                                    
                                    <?php if (!empty($prescription['preparation_method'])): ?>
                                    <p class="card-text">
                                        <strong>煎服方法：</strong><?= htmlspecialchars($prescription['preparation_method']) ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <p class="card-text">
                                        <strong>服用天数：</strong><?= htmlspecialchars($prescription['duration_days']) ?>天
                                    </p>
                                    
                                    <?php if (!empty($prescription['modifications'])): ?>
                                    <p class="card-text">
                                        <strong>加减变化：</strong><?= htmlspecialchars($prescription['modifications']) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['suggestions'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-chat-left-text"></i> 医嘱建议</h5>
                            <div class="alert alert-info">
                                <?= nl2br(htmlspecialchars($diagnosis['suggestions'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['precautions'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-exclamation-triangle"></i> 注意事项</h5>
                            <div class="alert alert-warning">
                                <?= nl2br(htmlspecialchars($diagnosis['precautions'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnosis['prognosis'])): ?>
                        <div class="diagnosis-section">
                            <h5><i class="bi bi-graph-up"></i> 预后评估</h5>
                            <p><?= nl2br(htmlspecialchars($diagnosis['prognosis'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i>
                            <strong>重要提示：</strong>
                            本诊断结果由 AI 系统生成，诊断置信度：<?= number_format($diagnosis['confidence_score'] ?? 85, 0) ?>%。
                            请在专业中医师指导下用药，如症状加重请及时就医。
                        </div>

                        <div class="text-center mt-4 no-print">
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> 打印报告
                            </button>
                            <a href="/consultation/export/<?= $consultation->id ?>" class="btn btn-secondary" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> 导出 PDF
                            </a>
                            <a href="/consultation/form" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> 新建问诊
                            </a>
                            <a href="/history" class="btn btn-outline-primary">
                                <i class="bi bi-clock-history"></i> 查看历史
                            </a>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- 无诊断结果 -->
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i>
                        <strong>暂无诊断结果</strong>
                        <p>未找到相关诊断信息，请稍后再试。</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($status === 'submitted' || $status === 'processing'): ?>
    <script>
        // 轮询检查诊断状态
        let checkCount = 0;
        const maxChecks = 60; // 最多检查60次（约3分钟）
        
        function checkDiagnosisStatus() {
            if (checkCount >= maxChecks) {
                location.reload();
                return;
            }
            
            fetch('/consultation/check-status/<?= $consultation->id ?>', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.completed) {
                    location.reload();
                } else {
                    checkCount++;
                    setTimeout(checkDiagnosisStatus, 3000); // 3秒后再次检查
                }
            })
            .catch(error => {
                console.error('状态检查失败:', error);
                checkCount++;
                setTimeout(checkDiagnosisStatus, 5000); // 出错时5秒后重试
            });
        }
        
        // 开始轮询
        setTimeout(checkDiagnosisStatus, 3000);
    </script>
    <?php endif; ?>
</body>
</html> 