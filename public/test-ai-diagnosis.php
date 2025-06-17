<?php
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Services\DeepSeekAPI;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Diagnosis;
use App\Models\Prescription;

// 初始化自动加载器
$autoloader = new Autoloader();
$autoloader->register();

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>AI 诊断处理测试</h1>";

try {
    echo "<h2>1. 获取测试问诊记录</h2>";
    
    // 获取最新的问诊记录
    $consultations = Consultation::all([], ['created_at' => 'DESC'], 1);
    
    if (empty($consultations)) {
        echo "<p style='color: red;'>错误：没有找到问诊记录。请先创建问诊记录。</p>";
        exit;
    }
    
    $consultation = $consultations[0];
    echo "<p>✓ 找到问诊记录 ID: {$consultation->id}，编号: {$consultation->consultation_no}</p>";
    
    // 获取患者信息
    $patient = $consultation->getPatient();
    if ($patient) {
        echo "<p>✓ 患者信息: {$patient->name}, {$patient->age}岁, {$patient->gender}</p>";
    } else {
        echo "<p style='color: orange;'>⚠ 未找到患者信息</p>";
    }
    
    echo "<h2>2. 准备 AI 数据</h2>";
    
    // 准备 AI 需要的数据格式
    $aiData = [
        'patient_name' => $patient ? $patient->name : '测试患者',
        'age' => $patient ? $patient->age : 30,
        'gender' => $patient ? $patient->gender : '男',
        'height' => 170,
        'weight' => $consultation->weight ?? 65,
        'chief_complaint' => $consultation->chief_complaint,
        'present_illness' => $consultation->present_illness,
        'past_history' => $consultation->past_history,
        'family_history' => $consultation->family_history,
        'complexion' => $consultation->complexion,
        'spirit' => $consultation->spirit,
        'tongue_body' => $consultation->tongue_body,
        'tongue_coating' => $consultation->tongue_coating,
        'pulse' => $consultation->pulse_diagnosis,
        'sleep' => $consultation->sleep,
        'appetite' => $consultation->diet_appetite,
        'bowel' => $consultation->stool,
        'urine' => $consultation->urine,
        'symptoms' => $consultation->symptoms
    ];
    
    echo "<pre>";
    echo "AI 数据准备完成:\n";
    echo htmlspecialchars(json_encode($aiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "</pre>";
    
    echo "<h2>3. 调用 AI API</h2>";
    
    // 调用 DeepSeek API
    $api = new DeepSeekAPI();
    echo "<p>正在调用 AI 进行诊断分析...</p>";
    
    $result = $api->diagnose($aiData);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✓ AI 调用成功</p>";
        
        echo "<h3>AI 原始响应:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow-y: auto;'>";
        echo htmlspecialchars($result['diagnosis']);
        echo "</pre>";
        
        echo "<h2>4. 解析诊断结果</h2>";
        
        // 创建诊断记录
        $diagnosis = new Diagnosis();
        $diagnosis->parseAIResponse($result['diagnosis']);
        $diagnosis->consultation_id = $consultation->id;
        $diagnosis->ai_response_raw = $result['diagnosis'];
        $diagnosis->confidence_score = 0.85;
        
        echo "<h3>解析后的诊断数据:</h3>";
        echo "<ul>";
        echo "<li><strong>辨证分析：</strong>" . htmlspecialchars(mb_substr($diagnosis->syndrome_analysis, 0, 100)) . "...</li>";
        echo "<li><strong>治疗原则：</strong>" . htmlspecialchars($diagnosis->treatment_principle) . "</li>";
        echo "<li><strong>诊断结果：</strong>" . htmlspecialchars(mb_substr($diagnosis->diagnosis_result, 0, 100)) . "...</li>";
        echo "<li><strong>医嘱建议：</strong>" . htmlspecialchars(mb_substr($diagnosis->suggestions, 0, 100)) . "...</li>";
        echo "</ul>";
        
        // 保存诊断
        if ($diagnosis->save()) {
            echo "<p style='color: green;'>✓ 诊断记录保存成功，ID: {$diagnosis->id}</p>";
        } else {
            echo "<p style='color: red;'>✗ 诊断记录保存失败</p>";
            echo "<pre>" . print_r($diagnosis->getErrors(), true) . "</pre>";
        }
        
        echo "<h2>5. 解析处方信息</h2>";
        
        // 创建处方记录
        $prescription = new Prescription();
        $prescription->consultation_id = $consultation->id;
        $prescription->diagnosis_id = $diagnosis->id;
        $prescription->parseFromAIResponse($result['diagnosis']);
        
        echo "<h3>解析后的处方数据:</h3>";
        echo "<ul>";
        echo "<li><strong>方剂名称：</strong>" . htmlspecialchars($prescription->prescription_name) . "</li>";
        echo "<li><strong>药物组成：</strong>";
        if (is_array($prescription->herbs)) {
            echo "<ul>";
            foreach ($prescription->herbs as $herb) {
                if (is_array($herb)) {
                    echo "<li>{$herb['name']} {$herb['dosage']}{$herb['unit']}</li>";
                } else {
                    echo "<li>{$herb}</li>";
                }
            }
            echo "</ul>";
        }
        echo "</li>";
        echo "<li><strong>用法用量：</strong>" . htmlspecialchars($prescription->dosage) . "</li>";
        echo "<li><strong>服用天数：</strong>" . $prescription->duration_days . "天</li>";
        echo "</ul>";
        
        // 保存处方
        if ($prescription->save()) {
            echo "<p style='color: green;'>✓ 处方记录保存成功，ID: {$prescription->id}</p>";
        } else {
            echo "<p style='color: red;'>✗ 处方记录保存失败</p>";
            echo "<pre>" . print_r($prescription->getErrors(), true) . "</pre>";
        }
        
        // 更新问诊状态
        $consultation->status = 'completed';
        if ($consultation->save()) {
            echo "<p style='color: green;'>✓ 问诊状态更新为已完成</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ AI 调用失败: " . htmlspecialchars($result['error']) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>错误:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<h4>堆栈跟踪:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<hr>
<p><a href="/consultation">返回问诊管理</a> | <a href="/consultation/result/<?php echo isset($consultation) ? $consultation->id : ''; ?>">查看诊断结果</a></p> 