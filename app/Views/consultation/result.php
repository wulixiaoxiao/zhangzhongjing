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
        <nav aria-label="breadcrumb">
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
                    
                    <!-- 加载状态 -->
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">AI 正在分析您的问诊信息，请稍候...</p>
                            <small class="text-secondary">问诊编号：<?= $consultation_id ?></small>
                        </div>
                    </div>

                    <!-- 诊断结果（暂时隐藏） -->
                    <div id="resultContent" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>提示：</strong>AI 诊断功能正在开发中，以下为模拟结果。
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>患者信息</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="30%">问诊编号：</td>
                                        <td><?= $consultation_id ?></td>
                                    </tr>
                                    <tr>
                                        <td>问诊时间：</td>
                                        <td><?= date('Y-m-d H:i:s') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>诊断医师</h5>
                                <p class="text-muted">DeepSeek AI 智能辅助诊断系统</p>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h5>中医辩证</h5>
                            <div class="alert alert-light">
                                <p>根据您提供的症状信息，初步辨证为：<strong>肝郁脾虚证</strong></p>
                                <p class="mb-0">主要表现：情志不舒，胸胁胀满，食少纳呆，疲乏无力等。</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>治法</h5>
                            <p>疏肝解郁，健脾益气</p>
                        </div>

                        <div class="mb-4">
                            <h5>推荐方剂</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">逍遥散加减</h6>
                                    <p class="card-text">
                                        <strong>组成：</strong>柴胡10g、当归10g、白芍15g、白术10g、茯苓15g、甘草6g、薄荷6g、生姜3片
                                    </p>
                                    <p class="card-text">
                                        <strong>用法：</strong>水煎服，每日1剂，分2次温服
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>医嘱建议</h5>
                            <ul>
                                <li>保持心情舒畅，避免情绪波动</li>
                                <li>规律作息，保证充足睡眠</li>
                                <li>饮食清淡，忌辛辣刺激</li>
                                <li>适当运动，如散步、太极拳等</li>
                                <li>建议2周后复诊</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>重要提示：</strong>
                            本诊断结果由 AI 系统生成，仅供参考。请在专业中医师指导下用药，如症状加重请及时就医。
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> 打印报告
                            </button>
                            <a href="/consultation/form" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> 新建问诊
                            </a>
                            <a href="/history" class="btn btn-outline-primary">
                                <i class="bi bi-clock-history"></i> 查看历史
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 模拟 AI 分析过程
        setTimeout(function() {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultContent').style.display = 'block';
        }, 3000); // 3秒后显示结果
    </script>
</body>
</html> 