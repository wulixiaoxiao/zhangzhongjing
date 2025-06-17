<?php

namespace App\Models;

use App\Core\Model;

/**
 * 患者信息模型
 * 
 * @property int $id
 * @property string $name 姓名
 * @property string $gender 性别
 * @property int $age 年龄
 * @property string $phone 联系电话
 * @property string $id_card 身份证号
 * @property string $occupation 职业
 * @property string $marriage 婚姻状况
 * @property string $address 地址
 * @property string $emergency_contact 紧急联系人
 * @property string $emergency_phone 紧急联系电话
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Patient extends Model
{
    /**
     * 数据表名
     */
    protected $table = 'patients';
    
    /**
     * 主键字段
     */
    protected $primaryKey = 'id';
    
    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'gender',
        'age',
        'phone',
        'id_card',
        'occupation',
        'marriage',
        'address',
        'emergency_contact',
        'emergency_phone'
    ];
    
    /**
     * 数据类型转换
     */
    protected $casts = [
        'id' => 'int',
        'age' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 验证规则
     */
    protected $rules = [
        'name' => 'required|chinese|min:2|max:20',
        'gender' => 'required|in:男,女',
        'age' => 'required|integer|between:1,150',
        'phone' => 'phone',
        'id_card' => 'id_card',
        'occupation' => 'max:50|safe_text',
        'marriage' => 'in:未婚,已婚,离异,丧偶',
        'address' => 'max:200|safe_text',
        'emergency_contact' => 'chinese|min:2|max:20',
        'emergency_phone' => 'phone'
    ];
    
    /**
     * 根据手机号查找患者
     * 
     * @param string $phone
     * @return Patient|null
     */
    public static function findByPhone($phone)
    {
        return static::findWhere(['phone' => $phone])->first();
    }
    
    /**
     * 根据身份证号查找患者
     * 
     * @param string $idCard
     * @return Patient|null
     */
    public static function findByIdCard($idCard)
    {
        return static::findWhere(['id_card' => $idCard])->first();
    }
    
    /**
     * 搜索患者
     * 
     * @param string $keyword 关键词（姓名、手机号、身份证号）
     * @return array
     */
    public static function search($keyword)
    {
        $db = static::getDb();
        
        $sql = "SELECT * FROM " . (new static)->table . " 
                WHERE name LIKE :keyword 
                OR phone LIKE :keyword 
                OR id_card LIKE :keyword 
                ORDER BY updated_at DESC";
        
        $params = [':keyword' => '%' . $keyword . '%'];
        
        return $db->select($sql, $params);
    }
    
    /**
     * 获取患者的问诊记录
     * 
     * @return array
     */
    public function getConsultations()
    {
        $db = static::getDb();
        
        $sql = "SELECT * FROM consultations 
                WHERE patient_id = :patient_id 
                ORDER BY created_at DESC";
        
        return $db->select($sql, [':patient_id' => $this->id]);
    }
    
    /**
     * 获取患者的最近一次问诊
     * 
     * @return array|null
     */
    public function getLatestConsultation()
    {
        $db = static::getDb();
        
        $sql = "SELECT * FROM consultations 
                WHERE patient_id = :patient_id 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        return $db->selectOne($sql, [':patient_id' => $this->id]);
    }
    
    /**
     * 获取患者的问诊统计
     * 
     * @return array
     */
    public function getConsultationStats()
    {
        $db = static::getDb();
        
        $sql = "SELECT 
                    COUNT(*) as total_consultations,
                    MAX(created_at) as last_consultation_date,
                    MIN(created_at) as first_consultation_date
                FROM consultations 
                WHERE patient_id = :patient_id";
        
        $stats = $db->selectOne($sql, [':patient_id' => $this->id]);
        
        // 计算常见症状
        $symptomsSql = "SELECT chief_complaint, COUNT(*) as count 
                        FROM consultations 
                        WHERE patient_id = :patient_id 
                        GROUP BY chief_complaint 
                        ORDER BY count DESC 
                        LIMIT 5";
        
        $stats['common_symptoms'] = $db->select($symptomsSql, [':patient_id' => $this->id]);
        
        return $stats;
    }
    
    /**
     * 合并重复患者记录
     * 
     * @param int $duplicateId 要合并的重复患者ID
     * @return bool
     */
    public function mergeDuplicate($duplicateId)
    {
        if ($duplicateId == $this->id) {
            return false;
        }
        
        $db = static::getDb();
        
        try {
            $db->beginTransaction();
            
            // 更新所有相关的问诊记录
            $sql = "UPDATE consultations SET patient_id = :new_id WHERE patient_id = :old_id";
            $db->execute($sql, [':new_id' => $this->id, ':old_id' => $duplicateId]);
            
            // 删除重复的患者记录
            $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
            $db->execute($sql, [':id' => $duplicateId]);
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    
    /**
     * 格式化患者信息显示
     * 
     * @return string
     */
    public function getDisplayName()
    {
        $display = $this->name;
        
        if ($this->gender) {
            $display .= ' (' . $this->gender . ')';
        }
        
        if ($this->age) {
            $display .= ' ' . $this->age . '岁';
        }
        
        return $display;
    }
    
    /**
     * 获取年龄段分类
     * 
     * @return string
     */
    public function getAgeGroup()
    {
        if ($this->age < 18) {
            return '未成年';
        } elseif ($this->age < 30) {
            return '青年';
        } elseif ($this->age < 50) {
            return '中年';
        } elseif ($this->age < 65) {
            return '中老年';
        } else {
            return '老年';
        }
    }
    
    /**
     * 保存前的钩子方法
     */
    protected function beforeSave()
    {
        // 如果身份证号存在，自动计算年龄
        if ($this->id_card && strlen($this->id_card) == 18) {
            $birthYear = substr($this->id_card, 6, 4);
            $birthMonth = substr($this->id_card, 10, 2);
            $birthDay = substr($this->id_card, 12, 2);
            
            $birthDate = new \DateTime("$birthYear-$birthMonth-$birthDay");
            $now = new \DateTime();
            $age = $now->diff($birthDate)->y;
            
            $this->age = $age;
        }
        
        return parent::beforeSave();
    }
} 