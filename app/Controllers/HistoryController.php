<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Services\DataExporter;

/**
 * 历史记录控制器
 */
class HistoryController extends Controller
{
    /**
     * 历史记录列表页
     */
    public function index()
    {
        // 获取查询参数
        $page = (int)($_GET['page'] ?? 1);
        $pageSize = (int)($_GET['pageSize'] ?? 10);
        $search = $_GET['search'] ?? '';
        $patientId = $_GET['patient_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // 确保页码有效
        $page = max(1, $page);
        $pageSize = max(5, min(50, $pageSize));
        
        // 构建查询条件
        $conditions = [];
        $params = [];
        
        if ($search) {
            $conditions[] = "(p.name LIKE ? OR p.phone LIKE ? OR c.consultation_no LIKE ? OR c.chief_complaint LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if ($patientId) {
            $conditions[] = "c.patient_id = ?";
            $params[] = $patientId;
        }
        
        if ($status) {
            $conditions[] = "c.status = ?";
            $params[] = $status;
        }
        
        if ($startDate) {
            $conditions[] = "DATE(c.consultation_date) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $conditions[] = "DATE(c.consultation_date) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // 获取总记录数
        $countSql = "
            SELECT COUNT(*) as total 
            FROM consultations c 
            JOIN patients p ON c.patient_id = p.id 
            {$whereClause}
        ";
        
        $totalResult = Database::selectOne($countSql, $params);
        $totalRecords = $totalResult['total'];
        $totalPages = ceil($totalRecords / $pageSize);
        
        // 获取分页数据
        $offset = ($page - 1) * $pageSize;
        $sql = "
            SELECT 
                c.id,
                c.consultation_no,
                c.consultation_date,
                c.chief_complaint,
                c.status,
                p.id as patient_id,
                p.name as patient_name,
                p.gender,
                p.age,
                p.phone,
                d.syndrome,
                d.treatment_principle
            FROM consultations c
            JOIN patients p ON c.patient_id = p.id
            LEFT JOIN diagnoses d ON d.consultation_id = c.id
            {$whereClause}
            ORDER BY c.consultation_date DESC
            LIMIT {$pageSize} OFFSET {$offset}
        ";
        
        $consultations = Database::select($sql, $params);
        
        // 获取所有患者列表（用于筛选下拉框）
        $patients = Patient::all(['id', 'name', 'phone']);
        
        $this->view('history/index', [
            'consultations' => $consultations,
            'patients' => $patients,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'pageSize' => $pageSize
            ],
            'filters' => [
                'search' => $search,
                'patient_id' => $patientId,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
    
    /**
     * 查看问诊详情
     */
    public function detail($id = null)
    {
        if (!$id) {
            $this->redirect('/history');
            return;
        }
        
        // 获取问诊记录
        $consultation = Consultation::findById($id);
        if (!$consultation) {
            $_SESSION['error'] = '问诊记录不存在';
            $this->redirect('/history');
            return;
        }
        
        // 获取患者信息
        $patient = Patient::findById($consultation->patient_id);
        
        // 获取诊断结果
        $diagnosis = null;
        $prescription = null;
        
        $diagnosisData = Diagnosis::findByConsultationId($consultation->id);
        if ($diagnosisData) {
            $diagnosis = $diagnosisData;
            
            // 获取处方信息
            $prescriptionData = Prescription::findByDiagnosisId($diagnosis->id);
            if ($prescriptionData) {
                $prescription = $prescriptionData;
                // herbs已经在模型中自动解析为数组
                $prescription->herbs_array = $prescription->herbs;
            }
        }
        
        $this->view('history/detail', [
            'consultation' => $consultation,
            'patient' => $patient,
            'diagnosis' => $diagnosis,
            'prescription' => $prescription
        ]);
    }
    
    /**
     * 患者历史记录
     */
    public function patient($patientId = null)
    {
        if (!$patientId) {
            $this->redirect('/history');
            return;
        }
        
        // 获取患者信息
        $patient = Patient::findById($patientId);
        if (!$patient) {
            $_SESSION['error'] = '患者不存在';
            $this->redirect('/history');
            return;
        }
        
        // 获取该患者的所有问诊记录
        $sql = "
            SELECT 
                c.*,
                d.syndrome,
                d.treatment_principle,
                d.diagnosis_time
            FROM consultations c
            LEFT JOIN diagnoses d ON d.consultation_id = c.id
            WHERE c.patient_id = ?
            ORDER BY c.consultation_date DESC
        ";
        
        $consultations = Database::select($sql, [$patientId]);
        
        // 统计信息
        $stats = [
            'total_consultations' => count($consultations),
            'completed' => 0,
            'pending' => 0,
            'last_visit' => null
        ];
        
        foreach ($consultations as $c) {
            if ($c['status'] === '已完成') {
                $stats['completed']++;
            } elseif ($c['status'] === '待诊断') {
                $stats['pending']++;
            }
            
            if (!$stats['last_visit'] || $c['consultation_date'] > $stats['last_visit']) {
                $stats['last_visit'] = $c['consultation_date'];
            }
        }
        
        $this->view('history/patient', [
            'patient' => $patient,
            'consultations' => $consultations,
            'stats' => $stats
        ]);
    }
    
    /**
     * 删除问诊记录（软删除）
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/history');
            return;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '无效的请求';
            $this->redirect('/history');
            return;
        }
        
        // 更新状态为已取消
        $consultation = Consultation::findById($id);
        if ($consultation) {
            $consultation->status = '已取消';
            $consultation->save();
            $_SESSION['success'] = '问诊记录已取消';
        } else {
            $_SESSION['error'] = '问诊记录不存在';
        }
        
        $this->redirect('/history');
    }
    
    /**
     * 导出历史记录（JSON格式）
     */
    public function exportJson()
    {
        // 获取查询参数（复用index方法的筛选逻辑）
        $search = $_GET['search'] ?? '';
        $patientId = $_GET['patient_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // 构建查询条件
        $conditions = [];
        $params = [];
        
        if ($search) {
            $conditions[] = "(p.name LIKE ? OR p.phone LIKE ? OR c.consultation_no LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if ($patientId) {
            $conditions[] = "c.patient_id = ?";
            $params[] = $patientId;
        }
        
        if ($status) {
            $conditions[] = "c.status = ?";
            $params[] = $status;
        }
        
        if ($startDate) {
            $conditions[] = "DATE(c.consultation_date) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $conditions[] = "DATE(c.consultation_date) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // 获取数据
        $sql = "
            SELECT 
                c.*,
                p.name as patient_name,
                p.gender as patient_gender,
                p.age as patient_age,
                p.phone as patient_phone,
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
            {$whereClause}
            ORDER BY c.consultation_date DESC
        ";
        
        $data = Database::select($sql, $params);
        
        // 设置响应头
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="consultations_' . date('YmdHis') . '.json"');
        
        // 输出JSON
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 导出历史记录（CSV格式）
     */
    public function exportCsv()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'patient_id' => $_GET['patient_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        DataExporter::exportConsultationsCSV($filters);
    }
    
    /**
     * 导出历史记录（Excel格式）
     */
    public function exportExcel()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'patient_id' => $_GET['patient_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        DataExporter::exportConsultationsExcel($filters);
    }
    
    /**
     * 导出患者列表
     */
    public function exportPatients()
    {
        DataExporter::exportPatientsCSV();
    }
    
    /**
     * 导出单个问诊详情
     */
    public function exportDetail($id = null)
    {
        if (!$id) {
            $this->redirect('/history');
            return;
        }
        
        $format = $_GET['format'] ?? 'csv';
        DataExporter::exportConsultationDetail($id, $format);
    }
} 