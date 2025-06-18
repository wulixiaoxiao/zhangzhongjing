<?php

namespace App\Models;

use App\Core\Model;

/**
 * 诊断结果模型
 * 
 * @property int $id
 * @property int $consultation_id 问诊ID
 * @property string $syndrome_analysis 辩证分析
 * @property string $treatment_principle 治疗原则
 * @property string $diagnosis_result 诊断结果
 * @property string $suggestions 医嘱建议
 * @property string $precautions 注意事项
 * @property string $prognosis 预后评估
 * @property array $ai_response 原始AI响应
 * @property float $confidence_score 诊断置信度
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Diagnosis extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'diagnoses';
    
    /**
     * 主键字段
     */
    protected $primaryKey = 'id';
    
    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'consultation_id',
        'syndrome_analysis',
        'treatment_principle',
        'diagnosis_result',
        'suggestions',
        'precautions',
        'prognosis',
        'ai_response',
        'confidence_score'
    ];
    
    /**
     * 数据类型转换
     */
    protected $casts = [
        'id' => 'int',
        'consultation_id' => 'int',
        'ai_response' => 'json',
        'confidence_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 验证规则
     */
    protected $rules = [
        'consultation_id' => 'required|integer',
        'syndrome_analysis' => 'required|safe_text',
        'treatment_principle' => 'required|safe_text',
        'diagnosis_result' => 'required|safe_text',
        'suggestions' => 'safe_text',
        'precautions' => 'safe_text',
        'prognosis' => 'safe_text',
        'confidence_score' => 'numeric|between:0,100'
    ];
    
    /**
     * 根据问诊ID查找诊断
     * 
     * @param int $consultationId
     * @return Diagnosis|null
     */
    public static function findByConsultationId($consultationId)
    {
        return static::findWhere(['consultation_id' => $consultationId]);
    }
    
    /**
     * 解析AI响应并创建诊断
     * 
     * @param int $consultationId
     * @param array $aiResponse
     * @return Diagnosis|null
     */
    public static function createFromAiResponse($consultationId, $aiResponse)
    {
        // 从AI响应中提取诊断信息
        $diagnosisData = self::parseAiResponseStatic($aiResponse);
        $diagnosisData['consultation_id'] = $consultationId;
        $diagnosisData['ai_response'] = $aiResponse;
        
        $diagnosis = new self($diagnosisData);
        
        return $diagnosis->save() ? $diagnosis : null;
    }
    
    /**
     * 解析AI响应（实例方法）
     * 
     * @param string|array $aiResponse
     * @return void
     */
    public function parseAIResponse($aiResponse)
    {
        // 如果是字符串，尝试解析为数组
        if (is_string($aiResponse)) {
            $aiResponse = ['content' => $aiResponse];
        }
        
        $data = self::parseAiResponseStatic($aiResponse);
        
        // 设置属性
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * 解析AI响应（静态方法）
     * 
     * @param array $aiResponse
     * @return array
     */
    protected static function parseAiResponseStatic($aiResponse)
    {
        $data = [
            'syndrome_analysis' => '',
            'treatment_principle' => '',
            'diagnosis_result' => '',
            'suggestions' => '',
            'precautions' => '',
            'prognosis' => '',
            'confidence_score' => 85.0 // 默认置信度
        ];
        
        // 尝试从AI响应中提取结构化数据
        if (isset($aiResponse['diagnosis'])) {
            $diagnosis = $aiResponse['diagnosis'];
            
            $data['syndrome_analysis'] = $diagnosis['syndrome_analysis'] ?? $diagnosis['辩证分析'] ?? '';
            $data['treatment_principle'] = $diagnosis['treatment_principle'] ?? $diagnosis['治疗原则'] ?? '';
            $data['diagnosis_result'] = $diagnosis['diagnosis_result'] ?? $diagnosis['诊断结果'] ?? '';
            $data['suggestions'] = $diagnosis['suggestions'] ?? $diagnosis['医嘱建议'] ?? '';
            $data['precautions'] = $diagnosis['precautions'] ?? $diagnosis['注意事项'] ?? '';
            $data['prognosis'] = $diagnosis['prognosis'] ?? $diagnosis['预后评估'] ?? '';
            
            if (isset($diagnosis['confidence_score'])) {
                $data['confidence_score'] = floatval($diagnosis['confidence_score']);
            }
        } 
        // 如果是纯文本响应，解析文本内容
        elseif (isset($aiResponse['content'])) {
            $content = $aiResponse['content'];
            
            // 使用正则表达式提取各部分内容
            if (preg_match('/辩证分析[：:]\s*(.+?)(?=治疗原则|诊断结果|$)/s', $content, $matches)) {
                $data['syndrome_analysis'] = trim($matches[1]);
            }
            
            if (preg_match('/治疗原则[：:]\s*(.+?)(?=诊断结果|处方建议|$)/s', $content, $matches)) {
                $data['treatment_principle'] = trim($matches[1]);
            }
            
            if (preg_match('/诊断结果[：:]\s*(.+?)(?=处方建议|医嘱建议|$)/s', $content, $matches)) {
                $data['diagnosis_result'] = trim($matches[1]);
            }
            
            if (preg_match('/(?:医嘱|建议)[：:]\s*(.+?)(?=注意事项|预后|$)/s', $content, $matches)) {
                $data['suggestions'] = trim($matches[1]);
            }
            
            if (preg_match('/注意事项[：:]\s*(.+?)(?=预后|$)/s', $content, $matches)) {
                $data['precautions'] = trim($matches[1]);
            }
            
            if (preg_match('/预后[：:]\s*(.+?)$/s', $content, $matches)) {
                $data['prognosis'] = trim($matches[1]);
            }
            
            // 如果整体解析失败，将全部内容作为诊断结果
            if (empty($data['diagnosis_result']) && empty($data['syndrome_analysis'])) {
                $data['diagnosis_result'] = $content;
            }
        }
        
        return $data;
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
        
        return \App\Core\Database::selectOne($sql, [':consultation_id' => $this->consultation_id]);
    }
    
    /**
     * 获取格式化的诊断报告
     * 
     * @return array
     */
    public function getFormattedReport()
    {
        return [
            'diagnosis_id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'sections' => [
                [
                    'title' => '辩证分析',
                    'content' => $this->syndrome_analysis,
                    'icon' => 'bi-diagram-3'
                ],
                [
                    'title' => '治疗原则',
                    'content' => $this->treatment_principle,
                    'icon' => 'bi-bullseye'
                ],
                [
                    'title' => '诊断结果',
                    'content' => $this->diagnosis_result,
                    'icon' => 'bi-clipboard-check'
                ],
                [
                    'title' => '医嘱建议',
                    'content' => $this->suggestions,
                    'icon' => 'bi-chat-left-text'
                ],
                [
                    'title' => '注意事项',
                    'content' => $this->precautions,
                    'icon' => 'bi-exclamation-triangle'
                ],
                [
                    'title' => '预后评估',
                    'content' => $this->prognosis,
                    'icon' => 'bi-graph-up'
                ]
            ],
            'confidence' => $this->confidence_score,
            'created_at' => $this->getFormattedCreatedAt()
        ];
    }
    
    /**
     * 获取诊断摘要
     * 
     * @return string
     */
    public function getSummary()
    {
        $summary = '';
        
        if ($this->diagnosis_result) {
            $summary = mb_substr($this->diagnosis_result, 0, 100);
            if (mb_strlen($this->diagnosis_result) > 100) {
                $summary .= '...';
            }
        } elseif ($this->syndrome_analysis) {
            $summary = mb_substr($this->syndrome_analysis, 0, 100);
            if (mb_strlen($this->syndrome_analysis) > 100) {
                $summary .= '...';
            }
        }
        
        return $summary;
    }
    
    /**
     * 更新诊断置信度
     * 
     * @param float $score
     * @return bool
     */
    public function updateConfidenceScore($score)
    {
        $this->confidence_score = max(0, min(100, $score));
        return $this->save();
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