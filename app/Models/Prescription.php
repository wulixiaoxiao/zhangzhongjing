<?php

namespace App\Models;

use App\Core\Model;

/**
 * 处方模型
 * 
 * @property int $id
 * @property int $consultation_id 问诊ID
 * @property string $prescription_name 方剂名称
 * @property array $herbs 药物组成
 * @property string $dosage 用法用量
 * @property string $preparation_method 煎服方法
 * @property int $duration_days 服用天数
 * @property string $modifications 加减变化
 * @property string $contraindications 禁忌事项
 * @property string $notes 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Prescription extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'prescriptions';
    
    /**
     * 主键字段
     */
    protected $primaryKey = 'id';
    
    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'diagnosis_id',
        'prescription_no',
        'prescription_name',
        'prescription_type',
        'herbs',
        'usage_method',
        'dosage',
        'frequency',
        'duration',
        'precautions',
        'contraindications'
    ];
    
    /**
     * 数据类型转换
     */
    protected $casts = [
        'id' => 'int',
        'consultation_id' => 'int',
        'herbs' => 'json',
        'duration_days' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 验证规则
     */
    protected $rules = [
        'consultation_id' => 'required|integer',
        'prescription_name' => 'required|chinese|max:100',
        'herbs' => 'required',
        'dosage' => 'required|safe_text',
        'preparation_method' => 'safe_text',
        'duration_days' => 'integer|between:1,180',
        'modifications' => 'safe_text',
        'contraindications' => 'safe_text',
        'notes' => 'safe_text'
    ];
    
    /**
     * 根据诊断ID查找处方
     * 
     * @param int $diagnosisId
     * @return Prescription|null
     */
    public static function findByDiagnosisId($diagnosisId)
    {
        return static::findWhere(['diagnosis_id' => $diagnosisId]);
    }
    
    /**
     * 从AI响应创建处方
     * 
     * @param int $consultationId
     * @param array $aiResponse
     * @return Prescription|null
     */
    public static function createFromAiResponse($consultationId, $aiResponse)
    {
        $prescriptionData = self::parseAiPrescription($aiResponse);
        $prescriptionData['consultation_id'] = $consultationId;
        
        $prescription = new self($prescriptionData);
        
        return $prescription->save() ? $prescription : null;
    }
    
    /**
     * 从AI响应解析处方信息（实例方法）
     * 
     * @param string|array $aiResponse
     * @return void
     */
    public function parseFromAIResponse($aiResponse)
    {
        // 如果是字符串，尝试从中提取处方部分
        if (is_string($aiResponse)) {
            // 查找处方相关部分
            if (preg_match('/(?:中药处方|处方|方剂)[：:]\s*(.+?)(?=##|$)/su', $aiResponse, $matches)) {
                $aiResponse = ['content' => $matches[1]];
            } else {
                $aiResponse = ['content' => $aiResponse];
            }
        }
        
        $data = self::parseAiPrescription($aiResponse);
        
        // 设置属性
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
        
        // 如果没有处方名称，使用默认名称
        if (empty($this->prescription_name)) {
            $this->prescription_name = '中医处方';
        }
    }
    
    /**
     * 解析AI处方信息
     * 
     * @param array $aiResponse
     * @return array
     */
    protected static function parseAiPrescription($aiResponse)
    {
        $data = [
            'prescription_name' => '',
            'herbs' => [],
            'dosage' => '每日一剂，水煎服，分两次温服',
            'preparation_method' => '先煎后下，文火煎煮30分钟',
            'duration_days' => 7,
            'modifications' => '',
            'contraindications' => '',
            'notes' => ''
        ];
        
        // 尝试从结构化数据中提取
        if (isset($aiResponse['prescription'])) {
            $prescription = $aiResponse['prescription'];
            
            $data['prescription_name'] = $prescription['name'] ?? $prescription['方剂名称'] ?? '';
            $data['herbs'] = $prescription['herbs'] ?? $prescription['药物组成'] ?? [];
            $data['dosage'] = $prescription['dosage'] ?? $prescription['用法用量'] ?? $data['dosage'];
            $data['preparation_method'] = $prescription['preparation'] ?? $prescription['煎服方法'] ?? $data['preparation_method'];
            $data['duration_days'] = intval($prescription['duration'] ?? $prescription['服用天数'] ?? 7);
            $data['modifications'] = $prescription['modifications'] ?? $prescription['加减'] ?? '';
            $data['contraindications'] = $prescription['contraindications'] ?? $prescription['禁忌'] ?? '';
            $data['notes'] = $prescription['notes'] ?? $prescription['备注'] ?? '';
        }
        // 从文本内容中解析
        elseif (isset($aiResponse['content'])) {
            $content = $aiResponse['content'];
            
            // 提取方剂名称
            if (preg_match('/(?:方剂|处方)[：:]\s*([^\n]+)/u', $content, $matches)) {
                $data['prescription_name'] = trim($matches[1]);
            }
            
            // 提取药物组成
            if (preg_match('/药物组成[：:]\s*(.+?)(?=用法|煎服|服用|加减|$)/su', $content, $matches)) {
                $herbsText = trim($matches[1]);
                $data['herbs'] = self::parseHerbsFromText($herbsText);
            }
            
            // 提取用法用量
            if (preg_match('/(?:用法用量|服用方法)[：:]\s*(.+?)(?=煎服|加减|注意|$)/su', $content, $matches)) {
                $data['dosage'] = trim($matches[1]);
            }
            
            // 提取煎服方法
            if (preg_match('/煎服方法[：:]\s*(.+?)(?=加减|注意|$)/su', $content, $matches)) {
                $data['preparation_method'] = trim($matches[1]);
            }
            
            // 提取加减
            if (preg_match('/(?:加减|随症加减)[：:]\s*(.+?)(?=注意|禁忌|$)/su', $content, $matches)) {
                $data['modifications'] = trim($matches[1]);
            }
            
            // 提取禁忌
            if (preg_match('/(?:禁忌|注意事项)[：:]\s*(.+?)$/su', $content, $matches)) {
                $data['contraindications'] = trim($matches[1]);
            }
        }
        
        return $data;
    }
    
    /**
     * 从文本解析药物列表
     * 
     * @param string $text
     * @return array
     */
    protected static function parseHerbsFromText($text)
    {
        $herbs = [];
        
        // 尝试按常见分隔符分割
        $parts = preg_split('/[，、,]\s*/u', $text);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            // 尝试提取药名和剂量
            if (preg_match('/^(.+?)\s*(\d+(?:\.\d+)?)\s*[克g]$/u', $part, $matches)) {
                $herbs[] = [
                    'name' => trim($matches[1]),
                    'dosage' => floatval($matches[2]),
                    'unit' => '克'
                ];
            } else {
                // 无法解析剂量，只保存药名
                $herbs[] = [
                    'name' => $part,
                    'dosage' => 0,
                    'unit' => '克'
                ];
            }
        }
        
        return $herbs;
    }
    
    /**
     * 获取问诊记录
     * 
     * @return Consultation|null
     */
    public function getConsultation()
    {
        return Consultation::find($this->consultation_id);
    }
    
    /**
     * 获取格式化的处方文本
     * 
     * @return string
     */
    public function getFormattedPrescription()
    {
        $text = "【方剂名称】" . $this->prescription_name . "\n\n";
        
        $text .= "【药物组成】\n";
        foreach ($this->herbs as $herb) {
            if (is_array($herb)) {
                $text .= sprintf("  %s %g%s\n", 
                    $herb['name'], 
                    $herb['dosage'] ?? 0, 
                    $herb['unit'] ?? '克'
                );
            } else {
                $text .= "  " . $herb . "\n";
            }
        }
        
        $text .= "\n【用法用量】" . $this->dosage . "\n";
        $text .= "【煎服方法】" . $this->preparation_method . "\n";
        $text .= "【服用天数】" . $this->duration_days . "天\n";
        
        if ($this->modifications) {
            $text .= "\n【加减变化】\n" . $this->modifications . "\n";
        }
        
        if ($this->contraindications) {
            $text .= "\n【禁忌事项】\n" . $this->contraindications . "\n";
        }
        
        if ($this->notes) {
            $text .= "\n【备注】\n" . $this->notes . "\n";
        }
        
        return $text;
    }
    
    /**
     * 获取药物总剂量
     * 
     * @return float
     */
    public function getTotalDosage()
    {
        $total = 0;
        
        foreach ($this->herbs as $herb) {
            if (is_array($herb) && isset($herb['dosage'])) {
                $total += floatval($herb['dosage']);
            }
        }
        
        return $total;
    }
    
    /**
     * 获取药物数量
     * 
     * @return int
     */
    public function getHerbCount()
    {
        return count($this->herbs);
    }
    
    /**
     * 添加药物
     * 
     * @param string $name 药名
     * @param float $dosage 剂量
     * @param string $unit 单位
     * @return bool
     */
    public function addHerb($name, $dosage, $unit = '克')
    {
        $herbs = $this->herbs;
        $herbs[] = [
            'name' => $name,
            'dosage' => $dosage,
            'unit' => $unit
        ];
        
        $this->herbs = $herbs;
        return $this->save();
    }
    
    /**
     * 移除药物
     * 
     * @param string $name 药名
     * @return bool
     */
    public function removeHerb($name)
    {
        $herbs = array_filter($this->herbs, function($herb) use ($name) {
            if (is_array($herb)) {
                return $herb['name'] !== $name;
            }
            return $herb !== $name;
        });
        
        $this->herbs = array_values($herbs);
        return $this->save();
    }
    
    /**
     * 更新药物剂量
     * 
     * @param string $name 药名
     * @param float $newDosage 新剂量
     * @return bool
     */
    public function updateHerbDosage($name, $newDosage)
    {
        $herbs = $this->herbs;
        $updated = false;
        
        foreach ($herbs as &$herb) {
            if (is_array($herb) && $herb['name'] === $name) {
                $herb['dosage'] = $newDosage;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $this->herbs = $herbs;
            return $this->save();
        }
        
        return false;
    }
    
    /**
     * 获取打印格式
     * 
     * @return array
     */
    public function getPrintFormat()
    {
        $patient = null;
        $consultation = $this->getConsultation();
        
        if ($consultation) {
            $patient = $consultation->getPatient();
        }
        
        return [
            'patient_info' => $patient ? [
                'name' => $patient->name,
                'gender' => $patient->gender,
                'age' => $patient->age,
                'phone' => $patient->phone
            ] : null,
            'prescription_info' => [
                'name' => $this->prescription_name,
                'herbs' => $this->herbs,
                'dosage' => $this->dosage,
                'preparation' => $this->preparation_method,
                'duration' => $this->duration_days,
                'total_dosage' => $this->getTotalDosage(),
                'herb_count' => $this->getHerbCount()
            ],
            'date' => $this->getFormattedCreatedAt(),
            'doctor' => '中医智能诊断系统'
        ];
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
} 