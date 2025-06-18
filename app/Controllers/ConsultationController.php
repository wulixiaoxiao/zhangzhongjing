<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Services\DeepSeekAPI;
use App\Services\PdfExporter;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Consultation;

/**
 * 问诊控制器
 */
class ConsultationController extends Controller
{
    /**
     * 问诊首页
     */
    public function index()
    {
        // 获取最近的问诊记录
        $consultations = \App\Models\Consultation::getRecent(20);
        
        // 获取统计数据
        $stats = \App\Models\Consultation::getStatistics();
        
        // 简化统计数据
        $simpleStats = [
            'total' => $stats['total'],
            'today' => $stats['today'],
            'completed' => 0,
            'pending' => 0
        ];
        
        // 计算已完成和待处理数量
        foreach ($stats['status_stats'] as $stat) {
            if ($stat['status'] === 'completed') {
                $simpleStats['completed'] = $stat['count'];
            } elseif (in_array($stat['status'], ['submitted', 'pending', 'draft'])) {
                $simpleStats['pending'] += $stat['count'];
            }
        }
        
        $data = [
            'title' => '问诊管理',
            'breadcrumb' => ['首页', '问诊管理'],
            'consultations' => $consultations,
            'stats' => $simpleStats
        ];
        
        $this->view('consultation/index', $data);
    }

    /**
     * 问诊表单页面
     */
    public function form()
    {
        $data = [
            'title' => '新建问诊',
            'breadcrumb' => ['首页', '问诊管理', '新建问诊'],
            'csrf_token' => $this->generateCsrf()
        ];
        
        $this->view('consultation/form', $data);
    }

    /**
     * 提交问诊表单
     */
    public function submit()
    {
        // 验证 CSRF Token
        $this->requireCsrf();
        
        // 验证请求方法
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => '无效的请求方法'], 400);
        }
        
        try {
            // 获取表单数据
            $data = $this->all();
            
            // 数据验证
            $errors = $this->validateConsultationData($data);
            if (!empty($errors)) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            
            // 查找或创建患者
            $patient = $this->findOrCreatePatient($data);
            if (!$patient) {
                $this->json(['success' => false, 'message' => '患者信息保存失败'], 500);
            }
            
            // 创建问诊记录
            $consultation = new Consultation();
            $consultationData = [
                'patient_id' => $patient->id,
                'consultation_number' => $consultation->generateConsultationNumber(),
                'chief_complaint' => $data['chief_complaint'],
                'present_illness' => $data['present_illness'] ?? '',
                'past_history' => $data['past_history'] ?? '',
                'personal_history' => $data['personal_history'] ?? '',
                'family_history' => $data['family_history'] ?? '',
                'tongue_quality' => $data['tongue_quality'] ?? '',
                'tongue_coating' => $data['tongue_coating'] ?? '',
                'pulse_left' => $data['pulse_left'] ?? '',
                'pulse_right' => $data['pulse_right'] ?? '',
                'symptoms' => $data['symptoms'] ?? '',
                'status' => 'pending'
            ];
            
            if ($consultation->create($consultationData)) {
                // 记录安全日志
                $this->logSecurity('consultation_created', [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $patient->id
                ]);
                
                // 异步调用 AI 诊断
                $this->json([
                    'success' => true,
                    'consultation_id' => $consultation->id,
                    'redirect' => '/index.php?url=consultation/processing/' . $consultation->id
                ]);
            } else {
                $this->json(['success' => false, 'message' => '问诊记录创建失败'], 500);
            }
            
        } catch (\Exception $e) {
            error_log("问诊提交错误: " . $e->getMessage());
            $this->json(['success' => false, 'message' => '系统错误，请稍后重试'], 500);
        }
    }

    /**
     * 显示诊断结果
     */
    public function result($id)
    {
        // 获取问诊记录
        $consultation = \App\Models\Consultation::find($id);
        if (!$consultation) {
            $this->redirect('/consultation');
            return;
        }
        
        // 获取患者信息
        $patient = $consultation->getPatient();
        
        // 获取诊断结果
        $diagnosis = $consultation->getDiagnosis();
        
        // 获取处方
        $prescription = $consultation->getPrescription();
        
        $data = [
            'title' => '诊断结果',
            'breadcrumb' => ['首页', '问诊管理', '诊断结果'],
            'consultation' => $consultation,
            'patient' => $patient,
            'diagnosis' => $diagnosis,
            'prescription' => $prescription,
            'status' => $consultation->status
        ];
        
        $this->view('consultation/result', $data);
    }

    /**
     * 保存草稿（AJAX）
     */
    public function saveDraft()
    {
        if (!$this->isAjax() || !$this->isPost()) {
            $this->json(['success' => false, 'message' => '请求无效'], 400);
            return;
        }

        // 获取表单数据
        $data = $this->all();
        
        // TODO: 保存到会话或数据库
        $_SESSION['consultation_draft'] = $data;
        
        $this->json([
            'success' => true,
            'message' => '草稿已保存',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 自动保存草稿
     */
    public function autosave()
    {
        // 验证 CSRF Token
        $this->requireCsrf();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => '无效的请求'], 400);
        }
        
        try {
            $data = $this->all();
            $sessionKey = 'consultation_draft_' . session_id();
            
            // 保存到会话
            $this->setSession($sessionKey, [
                'data' => $data,
                'updated_at' => time()
            ]);
            
            $this->json(['success' => true, 'message' => '草稿已保存']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => '保存失败'], 500);
        }
    }
    
    /**
     * 处理中页面
     */
    public function processing($id)
    {
        $consultation = new Consultation();
        $consultation = $consultation->find($id);
        
        if (!$consultation) {
            $this->error404('问诊记录不存在');
        }
        
        // 如果还未开始处理，启动 AI 诊断
        if ($consultation->status === 'pending') {
            // 异步处理 AI 诊断
            $this->startAiDiagnosis($consultation);
        }
        
        $this->view('consultation/processing', ['consultation' => $consultation]);
    }
    
    /**
     * 检查处理状态
     */
    public function checkStatus($id)
    {
        $consultation = new Consultation();
        $consultation = $consultation->find($id);
        
        if (!$consultation) {
            $this->json(['success' => false, 'message' => '记录不存在'], 404);
        }
        
        $response = ['status' => $consultation->status];
        
        if ($consultation->status === 'completed') {
            $response['redirect'] = '/index.php?url=consultation/result/' . $id;
        } elseif ($consultation->status === 'failed') {
            $response['message'] = '诊断失败，请重试';
        }
        
        $this->json($response);
    }
    
    /**
     * 验证问诊数据
     */
    private function validateConsultationData($data)
    {
        $errors = [];
        
        // 患者基本信息验证
        if (empty($data['name'])) {
            $errors['name'] = '请输入患者姓名';
        }
        
        if (empty($data['gender'])) {
            $errors['gender'] = '请选择性别';
        }
        
        // 主诉验证
        if (empty($data['chief_complaint'])) {
            $errors['chief_complaint'] = '请输入主诉';
        } elseif (strlen($data['chief_complaint']) < 10) {
            $errors['chief_complaint'] = '主诉描述过于简单，请详细描述';
        }
        
        return $errors;
    }
    
    /**
     * 查找或创建患者
     */
    private function findOrCreatePatient($data)
    {
        $patient = new Patient();
        
        // 先通过手机号查找
        $existing = $patient->findByPhone($data['phone']);
        if ($existing) {
            // 更新患者信息
            $existing->update([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'id_card' => $data['id_card'] ?? $existing->id_card,
                'address' => $data['address'] ?? $existing->address,
                'occupation' => $data['occupation'] ?? $existing->occupation,
                'emergency_contact' => $data['emergency_contact'] ?? $existing->emergency_contact,
                'emergency_phone' => $data['emergency_phone'] ?? $existing->emergency_phone
            ]);
            return $existing;
        }
        
        // 创建新患者
        $patientData = [
            'name' => $data['name'],
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'],
            'phone' => $data['phone'],
            'id_card' => $data['id_card'] ?? '',
            'address' => $data['address'] ?? '',
            'occupation' => $data['occupation'] ?? '',
            'emergency_contact' => $data['emergency_contact'] ?? '',
            'emergency_phone' => $data['emergency_phone'] ?? ''
        ];
        
        if ($patient->create($patientData)) {
            return $patient;
        }
        
        return null;
    }
    
    /**
     * 启动 AI 诊断
     */
    private function startAiDiagnosis($consultation)
    {
        try {
            // 更新状态为处理中
            $consultation->update(['status' => 'processing']);
            
            // 准备 AI 输入数据
            $patient = $consultation->getPatient();
            $age = date_diff(date_create($patient->birth_date), date_create())->y;
            
            $aiInput = [
                'patient' => [
                    'age' => $age,
                    'gender' => $patient->gender
                ],
                'symptoms' => [
                    'chief_complaint' => $consultation->chief_complaint,
                    'present_illness' => $consultation->present_illness,
                    'past_history' => $consultation->past_history,
                    'personal_history' => $consultation->personal_history,
                    'family_history' => $consultation->family_history
                ],
                'examination' => [
                    'tongue_quality' => $consultation->tongue_quality,
                    'tongue_coating' => $consultation->tongue_coating,
                    'pulse_left' => $consultation->pulse_left,
                    'pulse_right' => $consultation->pulse_right,
                    'symptoms' => $consultation->symptoms
                ]
            ];
            
            // 调用 DeepSeek API
            $api = new DeepSeekAPI();
            $result = $api->diagnose($aiInput);
            
            if ($result['success']) {
                // 保存诊断结果
                $diagnosis = new Diagnosis();
                $diagnosis->create([
                    'consultation_id' => $consultation->id,
                    'tcm_diagnosis' => $result['diagnosis']['tcm_diagnosis'],
                    'syndrome_differentiation' => $result['diagnosis']['syndrome_differentiation'],
                    'treatment_principle' => $result['diagnosis']['treatment_principle'],
                    'prescription_name' => $result['diagnosis']['prescription_name'],
                    'herbs' => json_encode($result['diagnosis']['herbs']),
                    'usage_method' => $result['diagnosis']['usage_method'],
                    'dietary_advice' => $result['diagnosis']['dietary_advice'],
                    'lifestyle_advice' => $result['diagnosis']['lifestyle_advice'],
                    'precautions' => $result['diagnosis']['precautions']
                ]);
                
                // 更新状态为完成
                $consultation->update(['status' => 'completed']);
                
                // 记录安全日志
                $this->logSecurity('ai_diagnosis_completed', [
                    'consultation_id' => $consultation->id,
                    'diagnosis_id' => $diagnosis->id
                ]);
            } else {
                // 更新状态为失败
                $consultation->update(['status' => 'failed']);
                error_log("AI诊断失败: " . ($result['error'] ?? 'Unknown error'));
            }
            
        } catch (\Exception $e) {
            $consultation->update(['status' => 'failed']);
            error_log("AI诊断异常: " . $e->getMessage());
        }
    }
    
    /**
     * 导出诊断报告为 PDF
     */
    public function export($id)
    {
        // 验证 CSRF Token
        $this->requireCsrf();
        
        $consultation = new Consultation();
        $consultation = $consultation->find($id);
        
        if (!$consultation) {
            $this->error404('问诊记录不存在');
        }
        
        try {
            // 获取关联数据
            $patient = $consultation->getPatient();
            $diagnosis = $consultation->getDiagnosis();
            $prescription = $consultation->getPrescription();
            
            // 准备导出数据
            $data = [
                'consultation' => $consultation,
                'patient' => $patient,
                'diagnosis' => $diagnosis,
                'prescription' => $prescription,
                'export_date' => date('Y-m-d H:i:s')
            ];
            
            // 使用 PdfExporter 服务
            $exporter = new \App\Services\PdfExporter();
            $pdf = $exporter->generateDiagnosisReport($data);
            
            // 设置响应头
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="诊断报告_' . $consultation->consultation_number . '.pdf"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $pdf;
            exit;
            
        } catch (\Exception $e) {
            error_log("PDF导出错误: " . $e->getMessage());
            $this->redirect('/index.php?url=consultation/result/' . $id);
        }
    }
} 