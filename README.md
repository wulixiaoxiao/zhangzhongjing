# 中医智能问诊系统

基于 PHP MVC 架构和 DeepSeek AI 的中医辅助诊断平台。

## 项目特点

- 🏥 完整的中医问诊表单系统
- 🤖 集成 DeepSeek AI 进行智能辅助诊断
- 📊 详细的诊断报告生成
- 🗂️ 历史记录管理和查询
- 📱 响应式设计，支持移动端
- 🔒 安全的数据处理和存储

## 系统要求

- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx Web 服务器
- DeepSeek API 密钥

## 快速开始

### 1. 克隆项目

```bash
git clone https://github.com/yourusername/yisheng.git
cd yisheng
```

### 2. 配置环境

复制环境变量示例文件并编辑：

```bash
cp .env.example .env
```

编辑 `.env` 文件，配置数据库和 API 信息：

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=yisheng_db
DB_USER=root
DB_PASS=your_password

DEEPSEEK_API_KEY=your_deepseek_api_key_here
```

### 3. 创建数据库

运行数据库初始化脚本：

```bash
php database/init.php
```

脚本会自动：
- 创建数据库 `yisheng_db`
- 创建所有必要的表
- 提示是否插入测试数据

### 4. 启动开发服务器

使用 PHP 内置服务器：

```bash
php -S localhost:8000 -t public
```

或使用 Composer 脚本：

```bash
composer start
```

访问 http://localhost:8000 查看系统。

## 项目结构

```
yisheng/
├── app/                    # 应用核心代码
│   ├── Controllers/        # 控制器
│   ├── Models/            # 数据模型
│   ├── Views/             # 视图文件
│   └── Core/              # 核心框架类
├── config/                # 配置文件
├── database/              # 数据库相关
├── public/                # 公共访问目录
│   ├── index.php         # 入口文件
│   └── .htaccess         # URL 重写规则
├── resources/             # 资源文件
├── storage/               # 存储目录
└── .taskmaster/           # TaskMaster 项目管理
```

## 开发进度

使用 TaskMaster AI 管理项目任务，查看当前进度：

```bash
npx task-master-ai list
```

## 安全提示

- 本系统仅供参考，不能替代专业医师的诊断
- 请确保在 HTTPS 环境下部署
- 定期备份数据库
- 妥善保管 API 密钥

## 许可证

MIT License 