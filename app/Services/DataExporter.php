<?php

namespace App\Services;

use App\Core\Database;

/**
 * 数据导出服务
 */
class DataExporter
{
    /**
     * 导出为CSV格式
     * 
     * @param array $data
     * @param string $filename
     * @param array $headers
     * @return void
     */
    public static function exportCSV($data, $filename = 'export.csv', $headers = [])
    {
        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // 输出BOM（让Excel正确识别UTF-8）
        echo "\xEF\xBB\xBF";
        
        // 打开输出流
        $output = fopen('php://output', 'w');
        
        // 如果提供了表头，先写入表头
        if (!empty($headers)) {
            fputcsv($output, $headers);
        } else if (!empty($data)) {
            // 否则使用第一行数据的键作为表头
            fputcsv($output, array_keys($data[0]));
        }
        
        // 写入数据
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * 导出问诊记录为CSV
     * 
     * @param array $filters
     * @return void
     */
    public static function exportConsultationsCSV($filters = [])
    {
        // 构建查询
        $conditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE ? OR p.phone LIKE ? OR c.consultation_no LIKE ?)";
            $searchParam = "%{$filters['search']}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filters['patient_id'])) {
            $conditions[] = "c.patient_id = ?";
            $params[] = $filters['patient_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $conditions[] = "DATE(c.consultation_date) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $conditions[] = "DATE(c.consultation_date) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // 获取数据
        $sql = "
            SELECT 
                c.consultation_no as '问诊编号',
                c.consultation_date as '问诊时间',
                c.status as '状态',
                p.name as '患者姓名',
                p.gender as '性别',
                p.age as '年龄',
                p.phone as '电话',
                c.chief_complaint as '主诉',
                c.present_illness as '现病史',
                c.past_history as '既往史',
                c.complexion as '面色',
                c.spirit as '精神',
                c.tongue_body as '舌质',
                c.tongue_coating as '舌苔',
                c.pulse_diagnosis as '脉象',
                d.syndrome as '证型',
                d.treatment_principle as '治法',
                d.syndrome_analysis as '辨证分析',
                pr.prescription_name as '方剂名称',
                pr.herbs as '药物组成'
            FROM consultations c
            JOIN patients p ON c.patient_id = p.id
            LEFT JOIN diagnoses d ON d.consultation_id = c.id
            LEFT JOIN prescriptions pr ON pr.diagnosis_id = d.id
            {$whereClause}
            ORDER BY c.consultation_date DESC
        ";
        
        $data = Database::select($sql, $params);
        
        // 处理JSON字段（药物组成）
        foreach ($data as &$row) {
            if ($row['药物组成']) {
                $herbs = json_decode($row['药物组成'], true);
                if (is_array($herbs)) {
                    $herbsList = [];
                    foreach ($herbs as $herb) {
                        if (is_array($herb)) {
                            $herbsList[] = $herb['name'] . ' ' . $herb['dosage'] . ($herb['unit'] ?? '克');
                        }
                    }
                    $row['药物组成'] = implode('、', $herbsList);
                }
            }
        }
        
        $filename = 'consultations_' . date('YmdHis') . '.csv';
        self::exportCSV($data, $filename);
    }
    
    /**
     * 导出患者信息为CSV
     * 
     * @return void
     */
    public static function exportPatientsCSV()
    {
        $sql = "
            SELECT 
                p.id as '患者ID',
                p.name as '姓名',
                p.gender as '性别',
                p.age as '年龄',
                p.phone as '电话',
                p.id_card as '身份证号',
                p.occupation as '职业',
                p.marriage as '婚姻状况',
                p.address as '住址',
                p.medical_history as '病史',
                p.allergy_history as '过敏史',
                p.created_at as '创建时间',
                COUNT(c.id) as '问诊次数',
                MAX(c.consultation_date) as '最近问诊'
            FROM patients p
            LEFT JOIN consultations c ON c.patient_id = p.id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $data = Database::select($sql);
        
        $filename = 'patients_' . date('YmdHis') . '.csv';
        self::exportCSV($data, $filename);
    }
    
    /**
     * 导出为简单的Excel格式（HTML表格）
     * 
     * @param array $data
     * @param string $filename
     * @param array $headers
     * @return void
     */
    public static function exportExcel($data, $filename = 'export.xls', $headers = [])
    {
        // 设置响应头
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // 开始HTML表格
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><style>td{mso-number-format:\@;}</style></head>';
        echo '<body>';
        echo '<table border="1">';
        
        // 写入表头
        if (!empty($headers)) {
            echo '<tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr>';
        } else if (!empty($data)) {
            echo '<tr>';
            foreach (array_keys($data[0]) as $key) {
                echo '<th>' . htmlspecialchars($key) . '</th>';
            }
            echo '</tr>';
        }
        
        // 写入数据
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . htmlspecialchars($value) . '</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</body></html>';
        exit;
    }
    
    /**
     * 导出问诊记录为Excel
     * 
     * @param array $filters
     * @return void
     */
    public static function exportConsultationsExcel($filters = [])
    {
        // 使用与CSV相同的查询逻辑
        $conditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE ? OR p.phone LIKE ? OR c.consultation_no LIKE ?)";
            $searchParam = "%{$filters['search']}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filters['patient_id'])) {
            $conditions[] = "c.patient_id = ?";
            $params[] = $filters['patient_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $conditions[] = "DATE(c.consultation_date) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $conditions[] = "DATE(c.consultation_date) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // 获取数据
        $sql = "
            SELECT 
                c.consultation_no as '问诊编号',
                c.consultation_date as '问诊时间',
                c.status as '状态',
                p.name as '患者姓名',
                p.gender as '性别',
                p.age as '年龄',
                p.phone as '电话',
                c.chief_complaint as '主诉',
                c.present_illness as '现病史',
                c.past_history as '既往史',
                c.complexion as '面色',
                c.spirit as '精神',
                c.tongue_body as '舌质',
                c.tongue_coating as '舌苔',
                c.pulse_diagnosis as '脉象',
                d.syndrome as '证型',
                d.treatment_principle as '治法',
                d.syndrome_analysis as '辨证分析',
                pr.prescription_name as '方剂名称',
                pr.herbs as '药物组成'
            FROM consultations c
            JOIN patients p ON c.patient_id = p.id
            LEFT JOIN diagnoses d ON d.consultation_id = c.id
            LEFT JOIN prescriptions pr ON pr.diagnosis_id = d.id
            {$whereClause}
            ORDER BY c.consultation_date DESC
        ";
        
        $data = Database::select($sql, $params);
        
        // 处理JSON字段（药物组成）
        foreach ($data as &$row) {
            if ($row['药物组成']) {
                $herbs = json_decode($row['药物组成'], true);
                if (is_array($herbs)) {
                    $herbsList = [];
                    foreach ($herbs as $herb) {
                        if (is_array($herb)) {
                            $herbsList[] = $herb['name'] . ' ' . $herb['dosage'] . ($herb['unit'] ?? '克');
                        }
                    }
                    $row['药物组成'] = implode('、', $herbsList);
                }
            }
        }
        
        $filename = 'consultations_' . date('YmdHis') . '.xls';
        self::exportExcel($data, $filename);
    }
    
    /**
     * 导出单个问诊详情
     * 
     * @param int $consultationId
     * @param string $format
     * @return void
     */
    public static function exportConsultationDetail($consultationId, $format = 'csv')
    {
        $sql = "
            SELECT 
                c.*,
                p.name as patient_name,
                p.gender as patient_gender,
                p.age as patient_age,
                p.phone as patient_phone,
                p.address as patient_address,
                d.syndrome,
                d.syndrome_analysis,
                d.treatment_principle,
                d.medical_advice,
                d.lifestyle_advice,
                d.dietary_advice,
                pr.prescription_name,
                pr.herbs,
                pr.usage_method,
                pr.dosage,
                pr.frequency,
                pr.duration
            FROM consultations c
            JOIN patients p ON c.patient_id = p.id
            LEFT JOIN diagnoses d ON d.consultation_id = c.id
            LEFT JOIN prescriptions pr ON pr.diagnosis_id = d.id
            WHERE c.id = ?
        ";
        
        $data = Database::select($sql, [$consultationId]);
        
        if (empty($data)) {
            header('Location: /history');
            exit;
        }
        
        $filename = 'consultation_' . $data[0]['consultation_no'] . '_' . date('YmdHis');
        
        if ($format === 'excel') {
            self::exportExcel($data, $filename . '.xls');
        } else {
            self::exportCSV($data, $filename . '.csv');
        }
    }
} 