<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\Patient;

/**
 * PDF 导出服务类
 * 生成诊断报告的可打印 HTML 版本
 */
class PdfExporter
{
    /**
     * 生成诊断报告 HTML
     * 
     * @param int $consultationId
     * @return string|false
     */
    public function generateDiagnosisReport($consultationId)
    {
        // 获取相关数据
        $consultation = Consultation::find($consultationId);
        if (!$consultation) {
            return false;
        }
        
        $patient = $consultation->getPatient();
        $diagnosis = Diagnosis::findWhere(['consultation_id' => $consultationId]);
        $prescription = Prescription::findWhere(['consultation_id' => $consultationId]);
        
        // 生成 HTML
        $html = $this->generateReportHtml($consultation, $patient, $diagnosis, $prescription);
        
        return $html;
    }
    
    /**
     * 生成报告 HTML
     */
    private function generateReportHtml($consultation, $patient, $diagnosis, $prescription)
    {
        $html = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>中医诊断报告 - ' . htmlspecialchars($consultation->consultation_no) . '</title>
    <style>
        body {
            font-family: "Microsoft YaHei", "SimSun", sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 20px;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            color: #555;
        }
        .diagnosis-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .herbs-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        .herb-item {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>中医智能诊断报告</h1>
        <p>问诊编号：' . htmlspecialchars($consultation->consultation_no) . '</p>
        <p>生成时间：' . date('Y年m月d日 H:i') . '</p>
    </div>
    
    <div class="section">
        <h2>患者基本信息</h2>
        <table class="info-table">
            <tr>
                <td>姓名</td>
                <td>' . htmlspecialchars($patient->name ?? '未知') . '</td>
            </tr>
            <tr>
                <td>性别</td>
                <td>' . htmlspecialchars($patient->gender ?? '未知') . '</td>
            </tr>
            <tr>
                <td>年龄</td>
                <td>' . htmlspecialchars($patient->age ?? '未知') . '岁</td>
            </tr>
            <tr>
                <td>联系电话</td>
                <td>' . htmlspecialchars($patient->phone ?? '未提供') . '</td>
            </tr>
            <tr>
                <td>问诊时间</td>
                <td>' . date('Y-m-d H:i:s', strtotime($consultation->created_at)) . '</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>主诉与现病史</h2>
        <div class="diagnosis-content">
            <p><strong>主诉：</strong>' . nl2br(htmlspecialchars($consultation->chief_complaint)) . '</p>
            ' . (!empty($consultation->present_illness) ? '<p><strong>现病史：</strong>' . nl2br(htmlspecialchars($consultation->present_illness)) . '</p>' : '') . '
        </div>
    </div>
    
    <div class="section">
        <h2>中医四诊</h2>
        <div class="diagnosis-content">
            <p><strong>望诊：</strong>面色' . htmlspecialchars($consultation->complexion ?? '正常') . '，精神' . htmlspecialchars($consultation->spirit ?? '尚可') . '</p>
            <p><strong>舌诊：</strong>舌质' . htmlspecialchars($consultation->tongue_body) . '，舌苔' . htmlspecialchars($consultation->tongue_coating) . '</p>
            <p><strong>脉诊：</strong>' . htmlspecialchars($consultation->pulse_diagnosis) . '</p>
            ' . (!empty($consultation->symptoms) ? '<p><strong>症状：</strong>' . htmlspecialchars($consultation->symptoms) . '</p>' : '') . '
        </div>
    </div>';
    
        // 诊断结果部分
        if ($diagnosis) {
            $html .= '
    <div class="section">
        <h2>中医诊断</h2>';
            
            if (!empty($diagnosis->syndrome_analysis)) {
                $html .= '
        <div class="diagnosis-content">
            <h3>辨证分析</h3>
            <p>' . nl2br(htmlspecialchars($diagnosis->syndrome_analysis)) . '</p>
        </div>';
            }
            
            if (!empty($diagnosis->treatment_principle)) {
                $html .= '
        <div class="diagnosis-content">
            <h3>治疗原则</h3>
            <p>' . nl2br(htmlspecialchars($diagnosis->treatment_principle)) . '</p>
        </div>';
            }
            
            if (!empty($diagnosis->diagnosis_result)) {
                $html .= '
        <div class="diagnosis-content">
            <h3>诊断结果</h3>
            <p>' . nl2br(htmlspecialchars($diagnosis->diagnosis_result)) . '</p>
        </div>';
            }
            
            $html .= '</div>';
        }
        
        // 处方部分
        if ($prescription) {
            $html .= '
    <div class="section">
        <h2>中药处方</h2>
        <div class="diagnosis-content">
            <h3>' . htmlspecialchars($prescription->prescription_name) . '</h3>';
            
            // 药物组成
            if (!empty($prescription->herbs)) {
                $herbs = is_string($prescription->herbs) ? json_decode($prescription->herbs, true) : $prescription->herbs;
                if (is_array($herbs) && count($herbs) > 0) {
                    $html .= '<p><strong>药物组成：</strong></p><div class="herbs-list">';
                    foreach ($herbs as $herb) {
                        if (is_array($herb)) {
                            $html .= '<span class="herb-item">' . htmlspecialchars($herb['name']) . ' ' . 
                                    htmlspecialchars($herb['dosage'] ?? '') . htmlspecialchars($herb['unit'] ?? '克') . '</span>';
                        } else {
                            $html .= '<span class="herb-item">' . htmlspecialchars($herb) . '</span>';
                        }
                    }
                    $html .= '</div>';
                }
            }
            
            $html .= '
            <p><strong>用法用量：</strong>' . htmlspecialchars($prescription->dosage) . '</p>';
            
            if (!empty($prescription->preparation_method)) {
                $html .= '<p><strong>煎服方法：</strong>' . htmlspecialchars($prescription->preparation_method) . '</p>';
            }
            
            $html .= '<p><strong>服用天数：</strong>' . htmlspecialchars($prescription->duration_days) . '天</p>';
            
            if (!empty($prescription->modifications)) {
                $html .= '<p><strong>加减变化：</strong>' . htmlspecialchars($prescription->modifications) . '</p>';
            }
            
            $html .= '</div></div>';
        }
        
        // 医嘱建议
        if ($diagnosis && !empty($diagnosis->suggestions)) {
            $html .= '
    <div class="section">
        <h2>医嘱建议</h2>
        <div class="diagnosis-content">
            ' . nl2br(htmlspecialchars($diagnosis->suggestions)) . '
        </div>
    </div>';
        }
        
        // 注意事项
        if ($diagnosis && !empty($diagnosis->precautions)) {
            $html .= '
    <div class="section">
        <h2>注意事项</h2>
        <div class="diagnosis-content">
            ' . nl2br(htmlspecialchars($diagnosis->precautions)) . '
        </div>
    </div>';
        }
        
        $html .= '
    <div class="footer">
        <p>本报告由中医智能诊断系统生成，仅供参考</p>
        <p>请在专业中医师指导下用药，如症状加重请及时就医</p>
    </div>
</body>
</html>';
        
        return $html;
    }
} 