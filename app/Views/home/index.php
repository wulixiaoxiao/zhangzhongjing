<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
        }
        .card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-hospital"></i> <?= $title ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/consultation/form">开始问诊</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/history">历史记录</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/patients">患者管理</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主体内容 -->
    <div class="hero">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4"><?= $title ?></h1>
            <p class="lead mb-5"><?= $description ?></p>
            <a href="/consultation/form" class="btn btn-light btn-lg px-5">
                <i class="bi bi-clipboard2-pulse"></i> 开始智能问诊
            </a>
        </div>
    </div>

    <!-- 功能介绍 -->
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-robot feature-icon mb-3"></i>
                        <h5 class="card-title">AI 智能诊断</h5>
                        <p class="card-text">基于 DeepSeek AI 的智能中医辅助诊断，提供专业的辩证分析和方剂建议。</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-file-medical feature-icon mb-3"></i>
                        <h5 class="card-title">详细问诊表单</h5>
                        <p class="card-text">完整的问诊信息收集，包括基本信息、主诉、病史、舌诊脉诊等全面数据。</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-clock-history feature-icon mb-3"></i>
                        <h5 class="card-title">历史记录管理</h5>
                        <p class="card-text">保存所有问诊记录，方便查询历史诊断，支持数据导出和打印功能。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 使用流程 -->
    <div class="container my-5">
        <h2 class="text-center mb-5">使用流程</h2>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-4">1</span>
                </div>
                <h5 class="mt-3">填写问诊表</h5>
                <p>详细填写患者基本信息和症状描述</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-4">2</span>
                </div>
                <h5 class="mt-3">提交分析</h5>
                <p>系统将问诊数据提交给 AI 进行分析</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-4">3</span>
                </div>
                <h5 class="mt-3">获取诊断</h5>
                <p>AI 返回中医辩证分析和处方建议</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-4">4</span>
                </div>
                <h5 class="mt-3">查看报告</h5>
                <p>查看详细诊断报告，支持打印导出</p>
            </div>
        </div>
    </div>

    <!-- 开发工具 -->
    <?php if (config('app.debug')): ?>
    <div class="container my-5">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-tools"></i> 开发工具（调试模式）</h5>
            </div>
            <div class="card-body">
                <p>以下工具仅在调试模式下显示：</p>
                <div class="btn-group" role="group">
                    <a href="/test-config.php" class="btn btn-outline-secondary">
                        <i class="bi bi-gear"></i> 配置测试
                    </a>
                    <a href="/test-api.php" class="btn btn-outline-secondary">
                        <i class="bi bi-plug"></i> API 测试
                    </a>
                    <a href="/test-db.php" class="btn btn-outline-secondary">
                        <i class="bi bi-database"></i> 数据库测试
                    </a>
                    <a href="/test-validation.php" class="btn btn-outline-secondary">
                        <i class="bi bi-check-circle"></i> 验证测试
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 页脚 -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2024 <?= $title ?>. 仅供参考，请在专业医师指导下使用。</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 