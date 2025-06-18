<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-clock-history"></i> 问诊历史记录</h2>
                <div>
                    <a href="/consultation" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 新建问诊
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> 导出数据
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">
                                <i class="bi bi-file-earmark-text"></i> 导出为 CSV
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">
                                <i class="bi bi-file-earmark-excel"></i> 导出为 Excel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('json')">
                                <i class="bi bi-filetype-json"></i> 导出为 JSON
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/history/export-patients">
                                <i class="bi bi-people"></i> 导出患者列表
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 搜索和筛选表单 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/history" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">搜索</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="患者姓名/电话/问诊编号/主诉" 
                                       value="<?= htmlspecialchars($filters['search']) ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">患者</label>
                                <select name="patient_id" class="form-select">
                                    <option value="">全部患者</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['id'] ?>" 
                                                <?= $filters['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($patient['name']) ?> - <?= htmlspecialchars($patient['phone']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">状态</label>
                                <select name="status" class="form-select">
                                    <option value="">全部状态</option>
                                    <option value="待诊断" <?= $filters['status'] === '待诊断' ? 'selected' : '' ?>>待诊断</option>
                                    <option value="诊断中" <?= $filters['status'] === '诊断中' ? 'selected' : '' ?>>诊断中</option>
                                    <option value="已完成" <?= $filters['status'] === '已完成' ? 'selected' : '' ?>>已完成</option>
                                    <option value="已取消" <?= $filters['status'] === '已取消' ? 'selected' : '' ?>>已取消</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">日期范围</label>
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" 
                                           value="<?= htmlspecialchars($filters['start_date']) ?>">
                                    <span class="input-group-text">至</span>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="<?= htmlspecialchars($filters['end_date']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> 搜索
                            </button>
                            <a href="/history" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> 重置
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 统计信息 -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <small class="text-muted">
                        共找到 <?= $pagination['totalRecords'] ?> 条记录，
                        显示第 <?= (($pagination['currentPage'] - 1) * $pagination['pageSize'] + 1) ?> - 
                        <?= min($pagination['currentPage'] * $pagination['pageSize'], $pagination['totalRecords']) ?> 条
                    </small>
                </div>
            </div>
            
            <!-- 历史记录列表 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>问诊编号</th>
                            <th>患者信息</th>
                            <th>问诊时间</th>
                            <th>主诉</th>
                            <th>诊断结果</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($consultations)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">暂无历史记录</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($consultations as $record): ?>
                                <tr>
                                    <td>
                                        <a href="/history/detail/<?= $record['id'] ?>">
                                            <?= htmlspecialchars($record['consultation_no']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/history/patient/<?= $record['patient_id'] ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($record['patient_name']) ?>
                                            <small class="text-muted">
                                                (<?= $record['gender'] ?>, <?= $record['age'] ?>岁)
                                            </small>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($record['phone']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= date('Y-m-d H:i', strtotime($record['consultation_date'])) ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $complaint = mb_substr($record['chief_complaint'], 0, 30);
                                        echo htmlspecialchars($complaint);
                                        if (mb_strlen($record['chief_complaint']) > 30) echo '...';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($record['syndrome']): ?>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($record['syndrome']) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($record['treatment_principle']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            '待诊断' => 'warning',
                                            '诊断中' => 'info',
                                            '已完成' => 'success',
                                            '已取消' => 'secondary'
                                        ][$record['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= htmlspecialchars($record['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/history/detail/<?= $record['id'] ?>" 
                                               class="btn btn-outline-primary" title="查看详情">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($record['status'] === '待诊断'): ?>
                                                <a href="/consultation/continue/<?= $record['id'] ?>" 
                                                   class="btn btn-outline-success" title="继续诊断">
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($record['status'] !== '已取消'): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="cancelConsultation(<?= $record['id'] ?>)" 
                                                        title="取消">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <nav aria-label="分页导航">
                    <ul class="pagination justify-content-center">
                        <!-- 首页 -->
                        <li class="page-item <?= $pagination['currentPage'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">
                                <i class="bi bi-chevron-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- 上一页 -->
                        <li class="page-item <?= $pagination['currentPage'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['currentPage'] - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- 页码 -->
                        <?php
                        $startPage = max(1, $pagination['currentPage'] - 2);
                        $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= $i == $pagination['currentPage'] ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- 下一页 -->
                        <li class="page-item <?= $pagination['currentPage'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['currentPage'] + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        
                        <!-- 末页 -->
                        <li class="page-item <?= $pagination['currentPage'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['totalPages']])) ?>">
                                <i class="bi bi-chevron-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// 取消问诊
function cancelConsultation(id) {
    if (!confirm('确定要取消这条问诊记录吗？')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/history/delete';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = id;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// 导出数据
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    let url = '';
    
    switch (format) {
        case 'csv':
            url = '/history/export-csv?' + params.toString();
            break;
        case 'excel':
            url = '/history/export-excel?' + params.toString();
            break;
        case 'json':
        default:
            url = '/history/export-json?' + params.toString();
            break;
    }
    
    window.location.href = url;
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?> 