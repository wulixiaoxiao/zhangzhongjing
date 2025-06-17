<?php

namespace App\Models;

use App\Core\Model;

/**
 * 问诊记录模型
 * 
 * @property int $id
 * @property int $patient_id 患者ID
 * @property string $consultation_no 问诊编号
 * @property string $chief_complaint 主诉
 * @property string $present_illness 现病史
 * @property string $past_history 既往史
 * @property string $allergy_history 过敏史
 * @property string $family_history 家族史
 * @property string $smoking 吸烟史
 * @property string $drinking 饮酒史
 * @property string $lifestyle 生活习惯
 * @property float $temperature 体温
 * @property int $pulse 脉搏
 * @property int $respiration 呼吸
 * @property string $blood_pressure 血压
 * @property float $weight 体重
 * @property string $physical_exam 体格检查
 * @property string $complexion 面色
 * @property string $spirit 精神
 * @property string $tongue_body 舌质
 * @property string $tongue_coating 舌苔
 * @property string $voice_breath 声音气息
 * @property string $symptoms 症状
 * @property string $sleep 睡眠
 * @property string $diet_appetite 饮食胃纳
 * @property string $urine 小便
 * @property string $stool 大便
 * @property string $pulse_diagnosis 脉诊
 * @property string $additional_info 补充信息
 * @property string $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Consultation extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'consultations';
    
    /**
     * 主键字段
     */
    protected $primaryKey = 'id';
    
    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'patient_id',
        'consultation_number',
        'chief_complaint',
        'present_illness',
        'past_history',
        'allergy_history',
        'family_history',
        'smoking',
        'drinking',
        'lifestyle',
        'temperature',
        'pulse',
        'respiration',
        'blood_pressure',
        'weight',
        'physical_exam',
        'complexion',
        'spirit',
        'tongue_body',
        'tongue_coating',
        'voice_breath',
        'symptoms',
        'sleep',
        'diet_appetite',
        'urine',
        'stool',
        'pulse_diagnosis',
        'additional_info',
        'status'
    ];
    
    /**
     * 数据类型转换
     */
    protected $casts = [
        'id' => 'int',
        'patient_id' => 'int',
        'temperature' => 'float',
        'pulse' => 'int',
        'respiration' => 'int',
        'weight' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 验证规则
     */
    protected $rules = [
        'patient_id' => 'required|integer',
        'chief_complaint' => 'required|min:10|max:500|safe_text',
        'present_illness' => 'max:1000|safe_text',
        'past_history' => 'max:500|safe_text',
        'allergy_history' => 'max:200|safe_text',
        'family_history' => 'max:500|safe_text',
        'smoking' => 'max:100|safe_text',
        'drinking' => 'max:100|safe_text',
        'lifestyle' => 'max:500|safe_text',
        'temperature' => 'numeric|between:35,42',
        'pulse' => 'integer|between:40,200',
        'respiration' => 'integer|between:10,40',
        'blood_pressure' => 'regex:/^\d{2,3}-\d{2,3}$/',
        'weight' => 'numeric|between:20,200',
        'physical_exam' => 'max:500|safe_text',
        'complexion' => 'in:红润,苍白,萎黄,潮红,晦暗',
        'spirit' => 'in:神清,疲倦,萎靡,烦躁,嗜睡',
        'tongue_body' => 'required|chinese|min:2|max:50',
        'tongue_coating' => 'required|chinese|min:2|max:50',
        'voice_breath' => 'max:100|safe_text',
        'symptoms' => 'max:200|safe_text',
        'sleep' => 'max:100|safe_text',
        'diet_appetite' => 'max:100|safe_text',
        'urine' => 'max:100|safe_text',
        'stool' => 'max:100|safe_text',
        'pulse_diagnosis' => 'required|chinese|min:2|max:100',
        'additional_info' => 'max:1000|safe_text',
        'status' => 'in:draft,submitted,diagnosed,completed'
    ];
    
    /**
     * 状态常量
     */
    const STATUS_DRAFT = 'draft';           // 草稿
    const STATUS_SUBMITTED = 'submitted';   // 已提交
    const STATUS_DIAGNOSED = 'diagnosed';   // 已诊断
    const STATUS_COMPLETED = 'completed';   // 已完成
    
    /**
     * 状态映射
     */
    public static $statusMap = [
        self::STATUS_DRAFT => '草稿',
        self::STATUS_SUBMITTED => '已提交',
        self::STATUS_DIAGNOSED => '已诊断',
        self::STATUS_COMPLETED => '已完成'
    ];
    
    /**
     * 生成问诊编号
     * 格式：C + 日期 + 4位流水号
     * 
     * @return string
     */
    public function generateConsultationNumber()
    {
        $date = date('Ymd');
        $prefix = 'C' . $date;
        
        // 查找今天最大的编号
        $sql = "SELECT consultation_number FROM {$this->table} 
                WHERE consultation_number LIKE ? 
                ORDER BY consultation_number DESC 
                LIMIT 1";
        
        $result = $this->db->selectOne($sql, [$prefix . '%']);
        
        if ($result && isset($result['consultation_number'])) {
            // 提取序号并加1
            $lastNumber = substr($result['consultation_number'], -4);
            $nextNumber = intval($lastNumber) + 1;
        } else {
            // 今天第一个
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * 获取患者信息
     * 
     * @return array|null
     */
    public function getPatient()
    {
        if (!$this->patient_id) {
            return null;
        }
        
        return Patient::find($this->patient_id);
    }
    
    /**
     * 获取诊断信息
     * 
     * @return array|null
     */
    public function getDiagnosis()
    {
        $sql = "SELECT * FROM diagnoses 
                WHERE consultation_id = :consultation_id 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        return \App\Core\Database::selectOne($sql, [':consultation_id' => $this->id]);
    }
    
    /**
     * 获取处方信息
     * 
     * @return array|null
     */
    public function getPrescription()
    {
        $sql = "SELECT * FROM prescriptions 
                WHERE consultation_id = :consultation_id 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        return \App\Core\Database::selectOne($sql, [':consultation_id' => $this->id]);
    }
    
    /**
     * 获取完整的问诊报告
     * 
     * @return array
     */
    public function getFullReport()
    {
        $report = $this->toArray();
        $report['patient'] = $this->getPatient();
        $report['diagnosis'] = $this->getDiagnosis();
        $report['prescription'] = $this->getPrescription();
        $report['status_text'] = self::$statusMap[$this->status] ?? '未知';
        
        return $report;
    }
    
    /**
     * 根据患者ID获取问诊记录
     * 
     * @param int $patientId
     * @param string $status
     * @return array
     */
    public static function getByPatientId($patientId, $status = null)
    {
        $conditions = ['patient_id' => $patientId];
        
        if ($status) {
            $conditions['status'] = $status;
        }
        
        return static::findWhere($conditions);
    }
    
    /**
     * 获取最近的问诊记录
     * 
     * @param int $limit
     * @return array
     */
    public static function getRecent($limit = 10)
    {
        $sql = "SELECT c.*, p.name as patient_name, p.gender, p.age 
                FROM consultations c
                LEFT JOIN patients p ON c.patient_id = p.id
                ORDER BY c.created_at DESC
                LIMIT :limit";
        
        return \App\Core\Database::select($sql, [':limit' => $limit]);
    }
    
    /**
     * 搜索问诊记录
     * 
     * @param array $criteria
     * @return array
     */
    public static function search($criteria)
    {
        $db = static::getDb();
        
        $sql = "SELECT c.*, p.name as patient_name, p.gender, p.age 
                FROM consultations c
                LEFT JOIN patients p ON c.patient_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // 按问诊编号搜索
        if (!empty($criteria['consultation_no'])) {
            $sql .= " AND c.consultation_no LIKE :consultation_no";
            $params[':consultation_no'] = '%' . $criteria['consultation_no'] . '%';
        }
        
        // 按患者姓名搜索
        if (!empty($criteria['patient_name'])) {
            $sql .= " AND p.name LIKE :patient_name";
            $params[':patient_name'] = '%' . $criteria['patient_name'] . '%';
        }
        
        // 按主诉搜索
        if (!empty($criteria['chief_complaint'])) {
            $sql .= " AND c.chief_complaint LIKE :chief_complaint";
            $params[':chief_complaint'] = '%' . $criteria['chief_complaint'] . '%';
        }
        
        // 按状态搜索
        if (!empty($criteria['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $criteria['status'];
        }
        
        // 按日期范围搜索
        if (!empty($criteria['start_date'])) {
            $sql .= " AND DATE(c.created_at) >= :start_date";
            $params[':start_date'] = $criteria['start_date'];
        }
        
        if (!empty($criteria['end_date'])) {
            $sql .= " AND DATE(c.created_at) <= :end_date";
            $params[':end_date'] = $criteria['end_date'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $db->select($sql, $params);
    }
    
    /**
     * 统计问诊数据
     * 
     * @return array
     */
    public static function getStatistics()
    {
        $db = static::getDb();
        
        // 总问诊数
        $totalSql = "SELECT COUNT(*) as total FROM consultations";
        $total = $db->selectOne($totalSql)['total'];
        
        // 按状态统计
        $statusSql = "SELECT status, COUNT(*) as count 
                      FROM consultations 
                      GROUP BY status";
        $statusStats = $db->select($statusSql);
        
        // 今日问诊数
        $todaySql = "SELECT COUNT(*) as today 
                     FROM consultations 
                     WHERE DATE(created_at) = CURDATE()";
        $today = $db->selectOne($todaySql)['today'];
        
        // 本月问诊数
        $monthSql = "SELECT COUNT(*) as month 
                     FROM consultations 
                     WHERE MONTH(created_at) = MONTH(CURDATE()) 
                     AND YEAR(created_at) = YEAR(CURDATE())";
        $month = $db->selectOne($monthSql)['month'];
        
        // 常见主诉
        $complaintSql = "SELECT chief_complaint, COUNT(*) as count 
                         FROM consultations 
                         GROUP BY chief_complaint 
                         ORDER BY count DESC 
                         LIMIT 10";
        $topComplaints = $db->select($complaintSql);
        
        return [
            'total' => $total,
            'today' => $today,
            'month' => $month,
            'status_stats' => $statusStats,
            'top_complaints' => $topComplaints
        ];
    }
    
    /**
     * 重写 save 方法以添加保存前的逻辑
     */
    public function save()
    {
        // 生成问诊编号
        if (!$this->consultation_number) {
            $this->consultation_number = self::generateConsultationNumber();
        }
        
        // 设置默认状态
        if (!$this->status) {
            $this->status = self::STATUS_DRAFT;
        }
        
        return parent::save();
    }
    
    /**
     * 获取格式化的创建时间
     * 
     * @return string
     */
    public function getFormattedCreatedAt()
    {
        return date('Y-m-d H:i:s', strtotime($this->created_at));
    }
    
    /**
     * 获取状态文本
     * 
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusMap[$this->status] ?? '未知';
    }
    
    /**
     * 是否可以编辑
     * 
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }
    
    /**
     * 是否已完成诊断
     * 
     * @return bool
     */
    public function isDiagnosed()
    {
        return in_array($this->status, [self::STATUS_DIAGNOSED, self::STATUS_COMPLETED]);
    }
} 