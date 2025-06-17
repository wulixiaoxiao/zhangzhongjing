<?php
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Services\PdfExporter;
use App\Models\Consultation;

// 初始化自动加载器
$autoloader = new Autoloader();
$autoloader->register();

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PDF 导出功能测试</h1>";

try {
    echo "<h2>1. 获取问诊记录列表</h2>";
    
    // 获取已完成的问诊记录
    $consultations = Consultation::all(['status' => 'completed'], ['created_at' => 'DESC'], 5);
    
    if (empty($consultations)) {
        // 如果没有已完成的，获取所有记录
        $consultations = Consultation::all([], ['created_at' => 'DESC'], 5);
    }
    
    if (empty($consultations)) {
        echo "<p style='color: red;'>错误：没有找到问诊记录。</p>";
        exit;
    }
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>
            <th>ID</th>
            <th>问诊编号</th>
            <th>患者姓名</th>
            <th>主诉</th>
            <th>状态</th>
            <th>创建时间</th>
            <th>操作</th>
          </tr>";
    
    foreach ($consultations as $consultation) {
        $patient = $consultation->getPatient();
        echo "<tr>";
        echo "<td>{$consultation->id}</td>";
        echo "<td>{$consultation->consultation_no}</td>";
        echo "<td>" . ($patient ? htmlspecialchars($patient->name) : '未知') . "</td>";
        echo "<td>" . htmlspecialchars(mb_substr($consultation->chief_complaint, 0, 30)) . "...</td>";
        echo "<td>{$consultation->status}</td>";
        echo "<td>{$consultation->created_at}</td>";
        echo "<td>
                <a href='/consultation/result/{$consultation->id}' target='_blank'>查看</a> |
                <a href='/consultation/export/{$consultation->id}' target='_blank'>导出</a>
              </td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. 测试 PDF 导出服务</h2>";
    
    // 使用第一条记录进行测试
    $testConsultation = $consultations[0];
    echo "<p>使用问诊记录 ID: {$testConsultation->id} 进行测试</p>";
    
    $exporter = new PdfExporter();
    $html = $exporter->generateDiagnosisReport($testConsultation->id);
    
    if ($html) {
        echo "<p style='color: green;'>✓ PDF 导出服务正常工作</p>";
        echo "<p>生成的 HTML 长度: " . strlen($html) . " 字节</p>";
        
        // 显示预览
        echo "<h3>预览（前 500 字符）:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto;'>";
        echo htmlspecialchars(mb_substr($html, 0, 500)) . "...";
        echo "</pre>";
        
        echo "<p><a href='/consultation/export/{$testConsultation->id}' target='_blank' class='button'>
                点击这里查看完整导出结果
              </a></p>";
    } else {
        echo "<p style='color: red;'>✗ PDF 导出失败</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>错误:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h4>堆栈跟踪:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
.button {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
}
.button:hover {
    background: #0056b3;
}
table {
    margin: 20px 0;
    border-collapse: collapse;
}
th {
    background: #f8f9fa;
}
</style>

<hr>
<p><a href="/consultation">返回问诊管理</a></p> 