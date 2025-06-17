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
        .status-badge {
            font-size: 0.875rem;
        }
        .status-draft { background-color: #6c757d; }
        .status-submitted { background-color: #ffc107; }
        .status-completed { background-color: #28a745; }
        .status-failed { background-color: #dc3545; }
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
                        <a class="nav-link active" href="/consultation">问诊管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/consultation/form">新建问诊</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- 面包屑导航 -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col">
                <h2><?= $title ?></h2>
            </div>
            <div class="col-auto">
                <a href="/consultation/form" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> 新建问诊
                </a>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?= $stats['total'] ?? 0 ?></h5>
                        <p class="card-text text-muted">总问诊数</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?= $stats['today'] ?? 0 ?></h5>
                        <p class="card-text text-muted">今日问诊</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?= $stats['completed'] ?? 0 ?></h5>
                        <p class="card-text text-muted">已完成</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?= $stats['pending'] ?? 0 ?></h5>
                        <p class="card-text text-muted">待处理</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 问诊记录列表 -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">最近问诊记录</h5>
            </div>
            <div class="card-body">
                <?php if (empty($consultations)): ?>
                    <p class="text-center text-muted py-5">暂无问诊记录</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>问诊编号</th>
                                    <th>患者姓名</th>
                                    <th>年龄</th>
                                    <th>性别</th>
                                    <th>主诉</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultations as $consultation): ?>
                                    <tr>
                                        <td>
                                            <a href="/consultation/result/<?= $consultation['id'] ?>">
                                                <?= htmlspecialchars($consultation['consultation_no']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($consultation['patient_name'] ?? '未知') ?></td>
                                        <td><?= htmlspecialchars($consultation['age'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($consultation['gender'] ?? '-') ?></td>
                                        <td>
                                            <small><?= htmlspecialchars(mb_substr($consultation['chief_complaint'], 0, 30)) ?>...</small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'status-' . ($consultation['status'] ?? 'draft');
                                            $statusText = \App\Models\Consultation::$statusMap[$consultation['status']] ?? '未知';
                                            ?>
                                            <span class="badge <?= $statusClass ?> status-badge">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('Y-m-d H:i', strtotime($consultation['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/consultation/result/<?= $consultation['id'] ?>" 
                                                   class="btn btn-outline-primary" title="查看">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($consultation['status'] === 'completed'): ?>
                                                <a href="/consultation/export/<?= $consultation['id'] ?>" 
                                                   class="btn btn-outline-secondary" title="导出" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 