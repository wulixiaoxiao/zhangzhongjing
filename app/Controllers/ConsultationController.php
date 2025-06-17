<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;

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
        $data = [
            'title' => '问诊管理',
            'breadcrumb' => ['首页', '问诊管理']
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
        // 验证 CSRF 令牌
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => '安全验证失败'], 403);
            return;
        }

        // 验证是否是 POST 请求
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => '请求方法错误'], 405);
            return;
        }

        // 获取表单数据
        $data = $this->all();
        
        // 定义验证规则
        $rules = [
            // 基本信息
            'name' => 'required|chinese|min:2|max:20',
            'gender' => 'required|in:男,女',
            'age' => 'required|integer|between:1,150',
            'phone' => 'phone',
            'blood_pressure' => 'regex:/^\d{2,3}-\d{2,3}$/',
            'occupation' => 'max:50|safe_text',
            'marriage' => 'in:未婚,已婚,离异,丧偶',
            
            // 主诉和病史
            'chief_complaint' => 'required|min:10|max:500|safe_text',
            'present_illness' => 'max:1000|safe_text',
            'past_history' => 'max:500|safe_text',
            'allergy_history' => 'max:200|safe_text',
            'family_history' => 'max:500|safe_text',
            
            // 个人史
            'smoking' => 'max:100|safe_text',
            'drinking' => 'max:100|safe_text',
            'lifestyle' => 'max:500|safe_text',
            
            // 体格检查
            'temperature' => 'numeric|between:35,42',
            'pulse' => 'integer|between:40,200',
            'respiration' => 'integer|between:10,40',
            'weight' => 'numeric|between:20,200',
            'physical_exam' => 'max:500|safe_text',
            
            // 中医四诊
            'complexion' => 'in:红润,苍白,萎黄,潮红,晦暗',
            'spirit' => 'in:神清,疲倦,萎靡,烦躁,嗜睡',
            'tongue_body' => 'required|chinese|min:2|max:50',
            'tongue_coating' => 'required|chinese|min:2|max:50',
            'voice_breath' => 'max:100|safe_text',
            'sleep' => 'max:100|safe_text',
            'diet_appetite' => 'max:100|safe_text',
            'urine' => 'max:100|safe_text',
            'stool' => 'max:100|safe_text',
            'pulse_diagnosis' => 'required|chinese|min:2|max:100',
            
            // 补充信息
            'additional_info' => 'max:1000|safe_text'
        ];
        
        // 字段显示名称
        $fieldNames = [
            'name' => '姓名',
            'gender' => '性别',
            'age' => '年龄',
            'phone' => '联系电话',
            'blood_pressure' => '血压',
            'chief_complaint' => '主诉',
            'tongue_body' => '舌质',
            'tongue_coating' => '舌苔',
            'pulse_diagnosis' => '脉象'
        ];
        
        // 自定义错误消息
        $messages = [
            'name.required' => '请输入患者姓名',
            'name.chinese' => '姓名只能包含中文字符',
            'chief_complaint.required' => '请描述您的主要症状',
            'chief_complaint.min' => '症状描述至少需要10个字符',
            'blood_pressure.regex' => '血压格式应为：收缩压-舒张压，如：120-80'
        ];
        
        // 执行验证
        $validator = new Validator();
        if (!$validator->validate($data, $rules, $messages, $fieldNames)) {
            $this->json([
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ]);
            return;
        }
        
        // 数据清理和过滤
        $cleanData = Validator::sanitize($data, [
            'age' => 'int',
            'temperature' => 'float',
            'pulse' => 'int',
            'respiration' => 'int',
            'weight' => 'float'
        ]);
        
        // 处理症状数组
        $symptoms = isset($data['symptoms']) && is_array($data['symptoms']) 
            ? array_filter($data['symptoms'], function($symptom) {
                return in_array($symptom, [
                    '恶寒', '发热', '寒热往来', '无汗', '自汗', '盗汗',
                    '头痛', '头晕', '身痛'
                ]);
            })
            : [];
        
        $cleanData['symptoms'] = implode('，', $symptoms);
        
        // TODO: 保存到数据库
        // TODO: 调用 AI 分析
        
        // 暂时返回成功响应
        $this->json([
            'success' => true,
            'message' => '问诊数据已提交，正在分析中...',
            'consultation_id' => time() // 临时ID
        ]);
    }

    /**
     * 显示诊断结果
     */
    public function result($id)
    {
        $data = [
            'title' => '诊断结果',
            'breadcrumb' => ['首页', '问诊管理', '诊断结果'],
            'consultation_id' => $id
        ];
        
        // TODO: 从数据库获取诊断结果
        
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
} 