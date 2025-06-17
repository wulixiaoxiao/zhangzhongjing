/**
 * 前端表单验证器
 * 提供实时表单验证功能
 */
class FormValidator {
    constructor(formId, rules = {}, messages = {}) {
        this.form = document.getElementById(formId);
        this.rules = rules;
        this.messages = messages;
        this.errors = {};
        
        if (this.form) {
            this.init();
        }
    }
    
    /**
     * 初始化验证器
     */
    init() {
        // 为每个需要验证的字段添加事件监听
        Object.keys(this.rules).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                // 失去焦点时验证
                field.addEventListener('blur', () => {
                    this.validateField(fieldName);
                });
                
                // 输入时清除错误
                field.addEventListener('input', () => {
                    this.clearFieldError(fieldName);
                });
            }
        });
        
        // 表单提交时验证
        this.form.addEventListener('submit', (e) => {
            if (!this.validateAll()) {
                e.preventDefault();
                this.showErrors();
            }
        });
    }
    
    /**
     * 验证单个字段
     */
    validateField(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return true;
        
        const value = field.value;
        const rules = this.rules[fieldName];
        
        if (!rules) return true;
        
        const fieldRules = typeof rules === 'string' ? rules.split('|') : rules;
        
        for (let rule of fieldRules) {
            const [ruleName, ...params] = rule.split(':');
            const ruleParams = params.join(':').split(',');
            
            if (!this.checkRule(value, ruleName, ruleParams, field)) {
                this.addError(fieldName, ruleName, ruleParams);
                this.showFieldError(fieldName);
                return false;
            }
        }
        
        this.clearFieldError(fieldName);
        return true;
    }
    
    /**
     * 检查单个规则
     */
    checkRule(value, ruleName, params, field) {
        switch (ruleName) {
            case 'required':
                return value.trim() !== '';
                
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                
            case 'numeric':
                return /^\d+$/.test(value);
                
            case 'integer':
                return /^-?\d+$/.test(value);
                
            case 'min':
                return value.length >= parseInt(params[0]);
                
            case 'max':
                return value.length <= parseInt(params[0]);
                
            case 'between':
                const len = value.length;
                return len >= parseInt(params[0]) && len <= parseInt(params[1]);
                
            case 'in':
                return params.includes(value);
                
            case 'phone':
                return /^1[3-9]\d{9}$/.test(value);
                
            case 'id_card':
                return this.validateIdCard(value);
                
            case 'chinese':
                return /^[\u4e00-\u9fa5]+$/.test(value);
                
            case 'alpha':
                return /^[a-zA-Z]+$/.test(value);
                
            case 'alpha_num':
                return /^[a-zA-Z0-9]+$/.test(value);
                
            case 'date':
                return !isNaN(Date.parse(value));
                
            default:
                return true;
        }
    }
    
    /**
     * 验证身份证号码
     */
    validateIdCard(idCard) {
        if (idCard.length !== 18) return false;
        
        // 基础格式验证
        if (!/^\d{17}[\dX]$/i.test(idCard)) return false;
        
        // 地区码验证
        const areaCodes = ['11', '12', '13', '14', '15', '21', '22', '23', '31', '32', '33', '34', '35', '36', '37', '41', '42', '43', '44', '45', '46', '50', '51', '52', '53', '54', '61', '62', '63', '64', '65'];
        if (!areaCodes.includes(idCard.substr(0, 2))) return false;
        
        // 出生日期验证
        const birthDate = idCard.substr(6, 8);
        const date = new Date(birthDate.substr(0, 4) + '-' + birthDate.substr(4, 2) + '-' + birthDate.substr(6, 2));
        if (isNaN(date.getTime())) return false;
        
        // 校验码验证
        const weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        const checkCodes = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        
        let sum = 0;
        for (let i = 0; i < 17; i++) {
            sum += parseInt(idCard[i]) * weights[i];
        }
        
        const checkCode = checkCodes[sum % 11];
        return idCard[17].toUpperCase() === checkCode;
    }
    
    /**
     * 验证所有字段
     */
    validateAll() {
        this.errors = {};
        let valid = true;
        
        Object.keys(this.rules).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                valid = false;
            }
        });
        
        return valid;
    }
    
    /**
     * 添加错误信息
     */
    addError(fieldName, rule, params) {
        if (!this.errors[fieldName]) {
            this.errors[fieldName] = [];
        }
        
        const message = this.getErrorMessage(fieldName, rule, params);
        this.errors[fieldName].push(message);
    }
    
    /**
     * 获取错误消息
     */
    getErrorMessage(fieldName, rule, params) {
        // 检查自定义消息
        const customKey = `${fieldName}.${rule}`;
        if (this.messages[customKey]) {
            return this.messages[customKey];
        }
        
        // 默认消息
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const fieldLabel = field ? (field.getAttribute('data-label') || fieldName) : fieldName;
        
        const defaultMessages = {
            'required': `${fieldLabel} 是必填项`,
            'email': `${fieldLabel} 必须是有效的邮箱地址`,
            'numeric': `${fieldLabel} 必须是数字`,
            'integer': `${fieldLabel} 必须是整数`,
            'min': `${fieldLabel} 最少需要 ${params[0]} 个字符`,
            'max': `${fieldLabel} 最多只能有 ${params[0]} 个字符`,
            'between': `${fieldLabel} 必须在 ${params[0]} 到 ${params[1]} 个字符之间`,
            'phone': `${fieldLabel} 必须是有效的手机号码`,
            'id_card': `${fieldLabel} 必须是有效的身份证号码`,
            'chinese': `${fieldLabel} 只能包含中文字符`,
            'alpha': `${fieldLabel} 只能包含字母`,
            'alpha_num': `${fieldLabel} 只能包含字母和数字`,
            'date': `${fieldLabel} 必须是有效的日期`
        };
        
        return defaultMessages[rule] || `${fieldLabel} 格式不正确`;
    }
    
    /**
     * 显示字段错误
     */
    showFieldError(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // 添加错误样式
        field.classList.add('is-invalid');
        
        // 显示错误消息
        const errorDiv = field.parentElement.querySelector('.invalid-feedback') || 
                        this.createErrorDiv(field);
        
        if (this.errors[fieldName] && this.errors[fieldName].length > 0) {
            errorDiv.textContent = this.errors[fieldName][0];
            errorDiv.style.display = 'block';
        }
    }
    
    /**
     * 清除字段错误
     */
    clearFieldError(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        field.classList.remove('is-invalid');
        
        const errorDiv = field.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        
        delete this.errors[fieldName];
    }
    
    /**
     * 创建错误提示元素
     */
    createErrorDiv(field) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentElement.appendChild(errorDiv);
        return errorDiv;
    }
    
    /**
     * 显示所有错误
     */
    showErrors() {
        Object.keys(this.errors).forEach(fieldName => {
            this.showFieldError(fieldName);
        });
        
        // 滚动到第一个错误
        const firstError = this.form.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
    
    /**
     * 清除所有错误
     */
    clearErrors() {
        Object.keys(this.errors).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });
        this.errors = {};
    }
    
    /**
     * 获取表单数据
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }
    
    /**
     * 设置规则
     */
    setRules(rules) {
        this.rules = rules;
    }
    
    /**
     * 添加规则
     */
    addRule(fieldName, rule) {
        this.rules[fieldName] = rule;
    }
    
    /**
     * 移除规则
     */
    removeRule(fieldName) {
        delete this.rules[fieldName];
    }
}

// 通用验证函数
function validateField(value, rules) {
    const validator = {
        required: (val) => val.trim() !== '',
        email: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
        phone: (val) => /^1[3-9]\d{9}$/.test(val),
        numeric: (val) => /^\d+$/.test(val),
        chinese: (val) => /^[\u4e00-\u9fa5]+$/.test(val),
        min: (val, len) => val.length >= parseInt(len),
        max: (val, len) => val.length <= parseInt(len)
    };
    
    for (let rule of rules.split('|')) {
        const [ruleName, param] = rule.split(':');
        if (validator[ruleName] && !validator[ruleName](value, param)) {
            return false;
        }
    }
    
    return true;
} 