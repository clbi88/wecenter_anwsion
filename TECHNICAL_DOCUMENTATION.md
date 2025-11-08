# WeCenter 3.1.9 完整技术文档

**文档生成日期**: 2025-11-08
**项目版本**: 3.1.9
**构建日期**: 20160523
**项目类型**: PHP开源社交化问答系统

---

## 目录

1. [项目概述](#项目概述)
2. [技术栈](#技术栈)
3. [系统架构](#系统架构)
4. [目录结构](#目录结构)
5. [数据库设计](#数据库设计)
6. [核心模块详解](#核心模块详解)
7. [API接口](#api接口)
8. [配置说明](#配置说明)
9. [部署指南](#部署指南)
10. [开发指南](#开发指南)

---

## 项目概述

### 简介

WeCenter 是国内首个推出的基于 PHP 的社交化问答系统，为站长和企业提供完整的社交问答和知识库建设解决方案。该系统采用经典的 MVC 架构模式，代码结构清晰，模块划分合理。

### 核心特性

- **社交化问答系统**: 提问、回答、投票、评论、关注
- **话题系统**: 话题创建、关注、相关推荐
- **文章系统**: 文章发布、评论、投票
- **用户系统**: 用户注册、权限管理、声望系统
- **积分系统**: 完整的积分规则和奖励机制
- **通知系统**: 站内通知、邮件通知
- **第三方登录**: 支持QQ、微博、微信、Facebook、Google、Twitter
- **移动端支持**: 移动版界面和微信公众号集成
- **搜索功能**: 基于Zend_Search_Lucene的全文搜索

### 项目信息

- **开发语言**: PHP
- **最低PHP版本**: 5.2.2+
- **数据库**: MySQL 5.0+
- **版本**: 3.1.9
- **发布日期**: 2016年5月23日
- **官方网站**: http://www.wecenter.com
- **许可证**: WeCenter开源许可

---

## 技术栈

### 后端技术

| 技术类别 | 具体技术 | 版本/说明 |
|---------|---------|----------|
| **编程语言** | PHP | 5.2.2+ |
| **数据库** | MySQL | 5.0+ (支持MySQLi或PDO_MySQL) |
| **架构模式** | MVC | 模型-视图-控制器 |
| **数据库抽象层** | Zend_Db | Zend Framework组件 |
| **模板引擎** | Savant3 | 轻量级PHP模板引擎 |
| **Session管理** | Zend_Session | 支持DB和文件存储 |
| **缓存** | Memcache/File | 支持多种缓存方式 |

### 前端技术

| 技术 | 说明 |
|------|------|
| **JavaScript库** | jQuery 1.x / 2.x |
| **富文本编辑器** | CKEditor |
| **图表库** | ECharts |
| **移动端滚动** | iScroll |
| **文件上传** | jQuery Form Plugin |
| **响应式支持** | Respond.js (IE8支持) |
| **Canvas兼容** | ExCanvas (IE兼容) |

### 第三方组件库

| 组件 | 用途 |
|------|------|
| **Zend Framework** | 数据库、邮件、缓存、验证等核心组件 |
| **PHPAnalysis** | 中文分词，用于搜索功能 |
| **Requests** | HTTP客户端库 |
| **phpqrcode** | 二维码生成 |
| **Text_Diff** | 内容版本对比 |
| **BBCode Parser** | BBCode标记语言解析 |
| **VideoUrlParser** | 视频链接解析（优酷等） |
| **Zend_Search_Lucene** | 全文搜索引擎 |

---

## 系统架构

### MVC架构流程

```
┌─────────────────────────────────────────────────────────────┐
│                        index.php (入口文件)                   │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│               system/system.php (系统启动)                    │
│  - 引入 init.php (系统初始化)                                 │
│  - 引入 aws_app.inc.php (应用核心类)                         │
│  - 引入 aws_controller.inc.php (控制器基类)                  │
│  - 引入 aws_model.inc.php (模型基类)                         │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   AWS_APP::run() (应用运行)                  │
│  - 调用 AWS_APP::init() 初始化系统                           │
│  - 加载配置、数据库、Session、缓存等核心组件                  │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              URI路由解析 (system/core/uri.php)               │
│  - 解析URL，确定控制器、动作方法                              │
│  - 支持URL重写                                               │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│           Controller创建 (app/*/main.php 或 ajax.php)        │
│  - 继承 AWS_CONTROLLER 基类                                  │
│  - 检查用户权限和访问规则                                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   执行 Action 方法                           │
│  - 调用 Model 处理业务逻辑                                   │
│  - 准备视图数据                                              │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              Model数据处理 (models/*.php)                    │
│  - 继承 AWS_MODEL 基类                                       │
│  - 执行数据库操作（基于Zend_Db）                             │
│  - 返回数据给Controller                                      │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│          渲染View (views/default/*.tpl.htm)                  │
│  - Savant3模板引擎处理                                       │
│  - 输出HTML到浏览器                                          │
└─────────────────────────────────────────────────────────────┘
```

### 核心类说明

#### 1. AWS_APP (system/aws_app.inc.php)

**功能**: 应用程序主类，使用单例模式管理各种系统组件

**主要属性**:
```php
private static $config;      // 配置对象
private static $db;          // 数据库对象
private static $cache;       // 缓存对象
private static $session;     // Session对象
private static $user;        // 用户对象
private static $models;      // 模型实例数组
public static $settings;     // 系统设置
```

**主要方法**:
- `run()`: 系统运行入口
- `init()`: 系统初始化
- `create_controller()`: 创建控制器实例
- `model()`: 获取Model实例
- `config()`: 获取配置对象
- `db()`: 获取数据库对象
- `cache()`: 获取缓存对象
- `session()`: 获取Session对象
- `user()`: 获取用户对象

#### 2. AWS_CONTROLLER (system/aws_controller.inc.php)

**功能**: 控制器基类，所有控制器都继承此类

**主要属性**:
```php
protected $user_info;        // 当前登录用户信息
protected $user_id;          // 当前用户ID
```

**主要方法**:
- `get_access_rule()`: 获取访问规则（黑白名单）
- `model()`: 快捷访问Model
- `template()`: 设置模板文件
- `message()`: 输出消息页面

#### 3. AWS_MODEL (system/aws_model.inc.php)

**功能**: 模型基类，封装数据库操作

**主要方法**:
- `fetch_row()`: 查询单行数据
- `fetch_all()`: 查询多行数据
- `insert()`: 插入数据
- `update()`: 更新数据
- `delete()`: 删除数据
- `query_all()`: 执行自定义SQL查询
- `get_table()`: 获取表名（自动添加前缀）

### 系统核心组件 (system/core/)

| 组件文件 | 类名 | 功能说明 |
|---------|------|---------|
| **autoload.php** | Autoloader | PSR-0自动加载类 |
| **db.php** | Database | 数据库连接和操作（基于Zend_Db） |
| **cache.php** | Cache | 缓存管理（Memcache、File等） |
| **config.php** | Config | 配置文件加载和管理 |
| **user.php** | User | 用户会话和认证 |
| **uri.php** | URI | URL路由解析 |
| **form.php** | Form | 表单验证和处理 |
| **upload.php** | Upload | 文件上传处理 |
| **image.php** | Image | 图片处理（缩放、裁剪） |
| **mail.php** | Mail | 邮件发送（基于Zend_Mail） |
| **captcha.php** | Captcha | 验证码生成和验证 |
| **crypt.php** | Crypt | 数据加密解密 |
| **lang.php** | Lang | 多语言支持 |
| **pagination.php** | Pagination | 分页功能 |
| **plugins.php** | Plugins | 插件管理 |

---

## 目录结构

### 完整目录树

```
D:\wecenter\wecenter-3.1.9/
│
├── index.php                      # 系统入口文件
├── version.php                    # 版本信息 (3.1.9)
├── changelog.txt                  # 更新日志
├── README.md                      # 项目说明
├── license.txt                    # 许可协议
├── robots.txt                     # 搜索引擎爬虫配置
├── .gitignore                     # Git忽略配置
│
├── api/                           # API接口目录
│   ├── admin_notify.php           # 管理员通知API
│   └── version_check.php          # 版本检查API
│
├── app/                           # 应用控制器目录 (32个模块)
│   ├── account/                   # 账户管理模块
│   │   ├── ajax.php               # AJAX控制器
│   │   ├── main.php               # 主控制器
│   │   ├── setting.php            # 账户设置
│   │   ├── find_password.php      # 找回密码
│   │   ├── edm.php                # 邮件营销
│   │   ├── ajax/                  # AJAX处理子目录
│   │   └── openid/                # 第三方登录
│   │
│   ├── admin/                     # 后台管理模块 (15个控制器)
│   │   ├── ajax.php               # 后台AJAX (75KB)
│   │   ├── main.php               # 后台主控制器
│   │   ├── user.php               # 用户管理
│   │   ├── question.php           # 问题管理
│   │   ├── article.php            # 文章管理
│   │   ├── topic.php              # 话题管理
│   │   ├── category.php           # 分类管理
│   │   ├── approval.php           # 审核管理
│   │   ├── feature.php            # 专题管理
│   │   ├── tools.php              # 工具管理
│   │   ├── page.php               # 页面管理
│   │   ├── help.php               # 帮助中心管理
│   │   ├── edm.php                # EDM邮件营销
│   │   ├── weibo.php              # 微博集成
│   │   ├── weixin.php             # 微信集成
│   │   └── ajax/                  # AJAX处理子目录
│   │
│   ├── question/                  # 问题模块 (核心功能)
│   │   ├── main.php               # 问题主控制器 (18KB)
│   │   └── ajax.php               # 问题AJAX (36KB)
│   │
│   ├── article/                   # 文章模块
│   ├── topic/                     # 话题模块
│   ├── people/                    # 用户中心模块
│   ├── publish/                   # 发布内容模块
│   ├── search/                    # 搜索模块
│   ├── follow/                    # 关注模块
│   ├── favorite/                  # 收藏模块
│   ├── notifications/             # 通知模块
│   ├── inbox/                     # 站内信模块
│   ├── feed/                      # 动态信息流模块
│   ├── home/                      # 个人主页模块
│   ├── explore/                   # 探索发现模块
│   ├── feature/                   # 专题模块
│   ├── reader/                    # 阅读器模块
│   ├── integral/                  # 积分模块
│   ├── invitation/                # 邀请模块
│   ├── help/                      # 帮助中心模块
│   ├── page/                      # 页面模块
│   ├── file/                      # 文件管理模块
│   ├── m/                         # 手机端模块
│   ├── mobile/                    # 移动端模块
│   ├── weixin/                    # 微信集成模块
│   ├── crond/                     # 定时任务模块
│   ├── upgrade/                   # 系统升级模块
│   │   ├── db/                    # 升级SQL脚本 (35个版本)
│   │   └── script/                # 升级脚本
│   ├── lang/                      # 语言切换模块
│   ├── _sso/                      # 单点登录模块
│   ├── aws_external/              # AWS外部扩展
│   └── aws_offical_external/      # AWS官方外部扩展
│
├── models/                        # 数据模型目录 (50个模型)
│   ├── question.php               # 问题模型 (核心)
│   ├── answer.php                 # 回答模型
│   ├── article.php                # 文章模型
│   ├── topic.php                  # 话题模型
│   ├── account.php                # 账户模型
│   ├── people.php                 # 用户模型
│   ├── follow.php                 # 关注模型
│   ├── favorite.php               # 收藏模型
│   ├── notify.php                 # 通知模型
│   ├── message.php                # 消息模型
│   ├── integral.php               # 积分模型
│   ├── reputation.php             # 声望模型
│   ├── admin.php                  # 管理员模型
│   ├── setting.php                # 设置模型
│   ├── search.php                 # 搜索模型
│   ├── email.php                  # 邮件模型
│   ├── category.php               # 分类模型
│   ├── feature.php                # 专题模型
│   ├── posts.php                  # 帖子模型
│   ├── publish.php                # 发布模型
│   ├── actions.php                # 操作日志模型
│   ├── active.php                 # 激活模型
│   ├── draft.php                  # 草稿模型
│   ├── edm.php                    # 邮件营销模型
│   ├── education.php              # 教育经历模型
│   ├── geo.php                    # 地理位置模型
│   ├── help.php                   # 帮助模型
│   ├── invitation.php             # 邀请模型
│   ├── menu.php                   # 菜单模型
│   ├── module.php                 # 模块模型
│   ├── online.php                 # 在线模型
│   ├── page.php                   # 页面模型
│   ├── reader.php                 # 阅读器模型
│   ├── related.php                # 关联模型
│   ├── system.php                 # 系统模型
│   ├── ucenter.php                # UCenter集成模型
│   ├── upgrade.php                # 升级模型
│   ├── verify.php                 # 验证模型
│   ├── weixin.php                 # 微信模型
│   ├── work.php                   # 工作经历模型
│   ├── crond.php                  # 定时任务模型
│   ├── openid/                    # 第三方登录模型
│   │   ├── qq.php                 # QQ登录
│   │   ├── facebook.php           # Facebook登录
│   │   ├── google.php             # Google登录
│   │   ├── twitter.php            # Twitter登录
│   │   ├── weibo/                 # 微博登录
│   │   │   ├── oauth.php
│   │   │   └── weibo.php
│   │   └── weixin/                # 微信登录
│   │       ├── third.php
│   │       └── weixin.php
│   └── search/                    # 搜索模型
│       └── fulltext.php           # 全文搜索
│
├── views/                         # 视图模板目录 (194个模板)
│   └── default/                   # 默认主题
│       ├── account/               # 账户模板
│       ├── admin/                 # 后台管理模板
│       ├── article/               # 文章模板
│       ├── block/                 # 区块模板
│       ├── config/                # 配置模板
│       ├── explore/               # 探索模板
│       ├── favorite/              # 收藏模板
│       ├── feature/               # 专题模板
│       ├── global/                # 全局模板
│       ├── help/                  # 帮助模板
│       ├── home/                  # 主页模板
│       ├── inbox/                 # 站内信模板
│       ├── install/               # 安装模板
│       ├── integral/              # 积分模板
│       ├── invitation/            # 邀请模板
│       ├── m/                     # 手机版模板 (48个文件)
│       ├── notifications/         # 通知模板
│       ├── page/                  # 页面模板
│       ├── people/                # 用户中心模板
│       ├── publish/               # 发布模板
│       ├── question/              # 问题模板
│       ├── reader/                # 阅读器模板
│       ├── search/                # 搜索模板
│       └── topic/                 # 话题模板
│
├── static/                        # 静态资源目录
│   ├── js/                        # 前端JavaScript
│   │   ├── jquery.js              # jQuery核心库
│   │   ├── jquery.2.js            # jQuery 2.x
│   │   ├── aws.js                 # AWS核心JS
│   │   ├── app.js                 # 应用主JS
│   │   ├── aw_template.js         # 前端模板引擎
│   │   ├── fileupload.js          # 文件上传
│   │   ├── md5.js                 # MD5加密
│   │   ├── areas.js               # 地区数据
│   │   ├── compatibility.js       # 兼容性处理
│   │   ├── jquery.form.js         # jQuery表单插件
│   │   ├── respond.js             # 响应式支持
│   │   ├── excanvas.js            # Canvas兼容
│   │   ├── enterprise.js          # 企业版JS
│   │   ├── app/                   # 应用级JS
│   │   │   ├── index.js
│   │   │   ├── login.js
│   │   │   ├── question_detail.js
│   │   │   ├── publish.js
│   │   │   ├── people.js
│   │   │   ├── topic.js
│   │   │   ├── feature.js
│   │   │   ├── reader.js
│   │   │   ├── search.js
│   │   │   └── setting.js
│   │   ├── editor/                # 编辑器
│   │   │   └── ckeditor/          # CKEditor富文本编辑器
│   │   └── plug_module/           # 插件模块
│   │
│   ├── css/                       # 前端CSS
│   │   └── default/               # 默认主题CSS
│   │
│   ├── common/                    # 公共资源
│   ├── fonts/                     # 字体文件
│   │
│   ├── admin/                     # 后台静态资源
│   │   ├── css/                   # 后台CSS
│   │   ├── img/                   # 后台图片
│   │   └── js/                    # 后台JavaScript
│   │       ├── aws_admin.js
│   │       ├── aws_admin_template.js
│   │       ├── echarts.js         # ECharts图表库
│   │       ├── echarts-data.js
│   │       ├── framework.js
│   │       └── global.js
│   │
│   └── mobile/                    # 移动端静态资源
│       ├── css/                   # 移动端CSS
│       ├── img/                   # 移动端图片
│       └── js/                    # 移动端JS
│           ├── app.js
│           ├── aw-mobile-template.js
│           ├── aws-mobile.js
│           ├── framework.js
│           └── iscroll.js         # iScroll滚动库
│
├── system/                        # 系统核心目录
│   ├── system.php                 # 系统启动文件
│   ├── init.php                   # 系统初始化文件
│   ├── aws_app.inc.php            # AWS应用核心类
│   ├── aws_controller.inc.php     # AWS控制器基类
│   ├── aws_model.inc.php          # AWS模型基类
│   ├── functions.inc.php          # 系统函数库
│   ├── functions.app.php          # 应用函数库
│   ├── Savant3.php                # Savant3模板引擎入口
│   ├── config.dist.php            # 配置文件模板
│   │
│   ├── core/                      # 核心类库 (16个核心模块)
│   │   ├── autoload.php           # 自动加载类
│   │   ├── db.php                 # 数据库类
│   │   ├── cache.php              # 缓存类
│   │   ├── config.php             # 配置类
│   │   ├── user.php               # 用户类
│   │   ├── uri.php                # URI路由类
│   │   ├── form.php               # 表单类
│   │   ├── upload.php             # 上传类
│   │   ├── image.php              # 图片处理类
│   │   ├── mail.php               # 邮件类
│   │   ├── captcha.php            # 验证码类
│   │   ├── crypt.php              # 加密类
│   │   ├── lang.php               # 语言类
│   │   ├── pagination.php         # 分页类
│   │   ├── plugins.php            # 插件类
│   │   └── fonts/                 # 字体文件
│   │
│   ├── config/                    # 系统配置目录
│   │   ├── database.php           # 数据库配置 (安装后生成)
│   │   ├── system.php             # 系统常量配置
│   │   ├── admin_menu.php         # 后台菜单配置
│   │   ├── notification.php       # 通知配置
│   │   ├── weixin.php             # 微信配置
│   │   ├── email_message.php      # 邮件模板配置
│   │   └── image.php              # 图片处理配置
│   │
│   ├── class/                     # 系统类库
│   │   ├── cls_action_log_class.inc.php  # 操作日志类
│   │   ├── cls_format.inc.php            # 格式化类
│   │   ├── cls_helper.inc.php            # 助手类
│   │   ├── cls_http.inc.php              # HTTP类
│   │   └── cls_template.inc.php          # 模板类
│   │
│   ├── Savant3/                   # Savant3模板引擎
│   │   ├── resources/
│   │   ├── Error.php
│   │   ├── Exception.php
│   │   ├── Filter.php
│   │   └── Plugin.php
│   │
│   ├── Services/                  # 第三方服务类库
│   │   ├── Phpanalysis/           # PHP中文分词
│   │   │   └── dict/              # 分词词典
│   │   ├── phpqrcode/             # 二维码生成库
│   │   ├── Requests/              # HTTP请求库
│   │   ├── Text/                  # 文本处理
│   │   │   └── Diff/              # 差异对比
│   │   ├── Weixin/                # 微信SDK
│   │   ├── BBCode.php             # BBCode解析
│   │   ├── Diff.php               # 差异对比
│   │   ├── Requests.php           # HTTP请求
│   │   ├── VideoUrlParser.php     # 视频URL解析
│   │   └── XML.php                # XML处理
│   │
│   └── Zend/                      # Zend Framework组件库
│       ├── Db/                    # 数据库组件 (核心)
│       ├── Cache/                 # 缓存组件
│       ├── Mail/                  # 邮件组件
│       ├── Session/               # Session组件
│       ├── Search/                # 搜索组件 (Lucene)
│       ├── Validate/              # 验证组件
│       ├── Filter/                # 过滤器组件
│       ├── Config/                # 配置组件
│       ├── Crypt/                 # 加密组件
│       ├── Http/                  # HTTP组件
│       ├── Log/                   # 日志组件
│       ├── Captcha/               # 验证码组件
│       ├── Ldap/                  # LDAP组件
│       ├── Loader/                # 加载器组件
│       ├── Mime/                  # MIME组件
│       ├── Mobile/                # 移动推送组件
│       └── Pdf/                   # PDF组件
│
├── install/                       # 安装目录
│   ├── index.php                  # 安装引导程序
│   └── db/                        # 数据库文件
│       ├── mysql.sql              # MySQL数据库结构 (1353行)
│       └── system_setting.sql     # 系统配置SQL (128项配置)
│
├── plugins/                       # 插件目录
│   ├── aws_external/              # AWS外部插件
│   ├── aws_offical_external/      # AWS官方外部插件
│   └── index.html
│
└── language/                      # 多语言支持
    ├── en_US.php                  # 英文语言包
    └── en_US.js                   # 英文JS语言包
```

### 目录说明

| 目录 | 说明 | 文件数量 |
|------|------|---------|
| **app/** | MVC中的C层（控制器） | 61个控制器文件 |
| **models/** | MVC中的M层（模型） | 50个模型文件 |
| **views/** | MVC中的V层（视图） | 194个模板文件 |
| **system/** | 系统核心 | 16个核心模块 |
| **static/** | 静态资源 | 14个主要JS文件 |
| **install/** | 安装程序 | 2个SQL文件 |

---

## 数据库设计

### 数据库文件

1. **主数据库文件**: `install/db/mysql.sql` (1353行)
   - 包含所有表结构定义
   - 初始数据插入语句

2. **系统配置**: `install/db/system_setting.sql` (128项配置)
   - 系统默认配置

3. **升级脚本**: `app/upgrade/db/*.sql` (35个版本升级文件)
   - 从2013年4月26日到2015年11月3日的所有升级脚本

### 数据表概览 (共72张表)

#### 用户相关表 (9张)

| 表名 | 说明 | 主要字段 |
|------|------|---------|
| **users** | 用户主表 | uid, user_name, email, password, salt, avatar_file, group_id, reputation |
| **users_attrib** | 用户附加属性 | uid, introduction, signature, qq, homepage |
| **users_group** | 用户组 | group_id, group_name, reputation_lower, reputation_higer, permission |
| **users_notification_setting** | 通知设置 | uid, data |
| **users_online** | 在线用户 | uid, last_active, ip, active_url |
| **user_follow** | 用户关注 | fans_uid, friend_uid, add_time |
| **user_action_history** | 用户操作记录 | uid, associate_type, associate_action, associate_id |
| **user_action_history_data** | 操作记录数据 | history_id, associate_content |
| **user_action_history_fresh** | 新鲜动态 | history_id, associate_id, uid, add_time |

**用户组权限**:
- 游客 (group_id=99)
- 超级管理员 (group_id=1)
- 前台管理员 (group_id=2)
- 未验证会员 (group_id=3)
- 普通会员 (group_id=4)
- 注册会员 (group_id=5, 声望0-100)
- 初级会员 (group_id=6, 声望100-200)
- 中级会员 (group_id=7, 声望200-500)
- 高级会员 (group_id=8, 声望500-1000)
- 核心会员 (group_id=9, 声望1000+)

#### 第三方登录表 (6张)

| 表名 | 说明 | 绑定平台 |
|------|------|---------|
| **users_qq** | QQ登录 | QQ Connect |
| **users_sina** | 新浪微博登录 | Sina Weibo |
| **users_weixin** | 微信登录 | WeChat |
| **users_google** | Google登录 | Google OAuth |
| **users_facebook** | Facebook登录 | Facebook OAuth |
| **users_twitter** | Twitter登录 | Twitter OAuth |
| **users_ucenter** | UCenter集成 | Discuz UCenter |

#### 问答核心表 (8张)

| 表名 | 说明 | 主要字段 |
|------|------|---------|
| **question** | 问题表 (MyISAM) | question_id, question_content, question_detail, published_uid, answer_count, view_count, focus_count |
| **answer** | 回答表 | answer_id, question_id, answer_content, uid, agree_count, against_count |
| **question_focus** | 问题关注 | question_id, uid, add_time |
| **question_invite** | 邀请回答 | question_id, sender_uid, recipients_uid |
| **question_uninterested** | 不感兴趣 | question_id, uid |
| **question_thanks** | 问题感谢 | question_id, uid |
| **question_comments** | 问题评论 | question_id, uid, message |
| **answer_comments** | 回答评论 | answer_id, uid, message |

**问题表字段详解**:
```sql
question_id          INT(11)     # 问题ID
question_content     VARCHAR(255) # 问题标题
question_detail      TEXT        # 问题详细说明
add_time            INT(11)     # 创建时间
update_time         INT(11)     # 更新时间
published_uid       INT(11)     # 发布用户ID
answer_count        INT(11)     # 回答数量
view_count          INT(11)     # 浏览次数
focus_count         INT(11)     # 关注人数
agree_count         INT(11)     # 赞同总数
best_answer         INT(11)     # 最佳答案ID
category_id         INT(11)     # 分类ID
anonymous           TINYINT(1)  # 是否匿名
lock                TINYINT(1)  # 是否锁定
popular_value       DOUBLE      # 热度值
is_recommend        TINYINT(1)  # 是否推荐
```

#### 文章相关表 (3张)

| 表名 | 说明 | 引擎 |
|------|------|------|
| **article** | 文章表 | MyISAM (支持全文搜索) |
| **article_comments** | 文章评论 | InnoDB |
| **article_vote** | 文章投票 | InnoDB |

#### 话题相关表 (5张)

| 表名 | 说明 |
|------|------|
| **topic** | 话题表 |
| **topic_focus** | 话题关注 |
| **topic_merge** | 话题合并记录 |
| **topic_relation** | 话题关联 (问题/文章) |
| **related_topic** | 相关话题 |

#### 社交功能表 (7张)

| 表名 | 说明 |
|------|------|
| **favorite** | 收藏 |
| **favorite_tag** | 收藏标签 |
| **inbox** | 站内信 |
| **inbox_dialog** | 站内信对话 |
| **notification** | 系统通知 |
| **notification_data** | 通知数据 |
| **answer_vote** | 回答投票 |

#### 分类和导航表 (5张)

| 表名 | 说明 |
|------|------|
| **category** | 分类表 |
| **nav_menu** | 导航菜单 |
| **feature** | 专题 |
| **feature_topic** | 专题话题关联 |
| **pages** | 自定义页面 |

#### 积分和声望表 (3张)

| 表名 | 说明 |
|------|------|
| **integral_log** | 积分日志 |
| **reputation_topic** | 话题声望 |
| **reputation_category** | 分类声望 |

#### 附件和上传表 (2张)

| 表名 | 说明 |
|------|------|
| **attach** | 附件表 |
| **related_links** | 相关链接 |

#### 系统管理表 (10张)

| 表名 | 说明 |
|------|------|
| **system_setting** | 系统设置 (128项配置) |
| **sessions** | Session存储 |
| **search_cache** | 搜索缓存 |
| **mail_queue** | 邮件队列 |
| **draft** | 草稿 |
| **approval** | 审核 |
| **report** | 举报 |
| **redirect** | 重定向 |
| **active_data** | 激活数据 |
| **verify_apply** | 认证申请 |

#### 邀请和教育表 (5张)

| 表名 | 说明 |
|------|------|
| **invitation** | 邀请码 |
| **education_experience** | 教育经历 |
| **work_experience** | 工作经历 |
| **jobs** | 职位列表 (38个预设职位) |
| **school** | 学校列表 |

#### 微信相关表 (6张)

| 表名 | 说明 |
|------|------|
| **weixin_accounts** | 微信多账号设置 |
| **weixin_reply_rule** | 微信回复规则 |
| **weixin_qr_code** | 微信二维码 |
| **weixin_message** | 微信消息 |
| **weixin_login** | 微信登录 |
| **weixin_msg** | 微信群发 |
| **weixin_third_party_api** | 微信第三方接入 |

#### 微博相关表 (1张)

| 表名 | 说明 |
|------|------|
| **weibo_msg** | 新浪微博消息 |

#### EDM邮件营销表 (4张)

| 表名 | 说明 |
|------|------|
| **edm_task** | EDM任务 |
| **edm_taskdata** | EDM任务数据 |
| **edm_userdata** | EDM用户数据 |
| **edm_usergroup** | EDM用户组 |
| **edm_unsubscription** | 退订 |

#### 邮件接收表 (2张)

| 表名 | 说明 |
|------|------|
| **receiving_email_config** | 邮件接收配置 |
| **received_email** | 已接收邮件 |

#### 其他表 (4张)

| 表名 | 说明 |
|------|------|
| **posts_index** | 帖子索引 |
| **geo_location** | 地理位置 |
| **help_chapter** | 帮助中心章节 |
| **answer_thanks** | 回答感谢 |
| **answer_uninterested** | 回答不感兴趣 |

### 数据库索引策略

**高性能索引**:
1. 所有外键都有索引
2. 常用查询字段都有索引 (add_time, uid, question_id等)
3. 复合索引优化联合查询
4. 全文索引用于搜索 (MyISAM引擎)

**示例索引**:
```sql
-- 问题表索引
KEY `category_id` (`category_id`)
KEY `update_time` (`update_time`)
KEY `published_uid` (`published_uid`)
KEY `answer_count` (`answer_count`)
KEY `popular_value` (`popular_value`)
FULLTEXT KEY `question_content_fulltext` (`question_content_fulltext`)

-- 回答表索引
KEY `question_id` (`question_id`)
KEY `agree_count` (`agree_count`)
KEY `add_time` (`add_time`)
KEY `uid` (`uid`)
```

### 系统配置项 (128项)

**核心配置**:
```php
site_name                        # 站点名称: "WeCenter"
description                      # 站点描述
keywords                         # 站点关键词
from_email                       # 发件邮箱
upload_dir                       # 上传目录
upload_url                       # 上传URL
ui_style                         # UI样式: "default"
url_rewrite_enable               # URL重写: "N"
```

**用户相关配置**:
```php
register_type                    # 注册类型: "open"
register_valid_type              # 验证方式: "email"
register_seccode                 # 注册验证码: "Y"
username_length_min              # 用户名最小长度: 2
username_length_max              # 用户名最大长度: 14
answer_self_question             # 回答自己的问题: "Y"
anonymous_enable                 # 允许匿名: "Y"
```

**积分系统配置**:
```php
integral_system_enabled          # 积分系统启用: "N"
integral_unit                    # 积分单位: "金币"
integral_system_config_register  # 注册积分: 2000
integral_system_config_best_answer # 最佳答案: 200
integral_system_config_new_question # 新问题: -20
integral_system_config_new_answer   # 新回答: -5
```

**声望系统配置**:
```php
reputation_function              # 声望计算公式
publisher_reputation_factor      # 发布者声望系数: 10
reputation_log_factor            # 声望日志系数: 3
best_answer_reput                # 最佳答案声望: 20
```

**第三方登录配置**:
```php
qq_login_enabled                 # QQ登录启用
qq_login_app_id                  # QQ App ID
sina_weibo_enabled               # 微博登录启用
weixin_app_id                    # 微信App ID
google_login_enabled             # Google登录启用
facebook_login_enabled           # Facebook登录启用
twitter_login_enabled            # Twitter登录启用
```

---

## 核心模块详解

### 1. 问答模块 (Question)

**控制器**: `app/question/main.php`, `app/question/ajax.php`
**模型**: `models/question.php`, `models/answer.php`
**视图**: `views/default/question/*.tpl.htm`

#### 核心功能

**提问流程**:
1. 用户填写问题标题和详情
2. 选择分类和话题
3. 可选择匿名发布
4. 支持添加附件
5. 自动创建操作历史记录

**回答流程**:
1. 用户查看问题详情
2. 撰写回答内容
3. 可选择匿名回答
4. 支持富文本编辑
5. 答案支持投票（赞同/反对）

**关键方法**:

```php
// models/question.php

// 保存问题
public function save_question($question_content, $question_detail,
    $published_uid, $anonymous = 0, $ip_address = null, $from = null)

// 获取问题信息
public function get_question_info_by_id($question_id, $cache = true)

// 更新浏览次数
public function update_views($question_id)

// 发布回答
public function publish_answer($question_id, $answer_content, $uid)

// 更新问题
public function update_question($question_id, $question_content,
    $question_detail)
```

#### 数据流转

```
用户提问 → save_question() → insert question表
         ↓
      关联话题 → insert topic_relation表
         ↓
      创建动态 → insert user_action_history表
         ↓
      积分处理 → update integral_log表
```

### 2. 用户模块 (Account/People)

**控制器**: `app/account/`, `app/people/`
**模型**: `models/account.php`, `models/people.php`
**核心表**: users, users_attrib, users_group

#### 用户注册流程

```
1. 填写用户名、邮箱、密码
2. 验证码验证
3. 邮箱唯一性检查
4. 密码加密 (MD5 + Salt)
5. 插入users表
6. 发送验证邮件
7. 邮箱激活
8. 设置初始用户组
9. 赠送注册积分
```

#### 权限系统

**权限检查流程**:
```php
AWS_CONTROLLER::get_access_rule()
    → 返回访问规则（黑/白名单）
    → AWS_APP::run() 执行权限检查
    → 不通过则跳转登录页面
```

**权限配置示例**:
```php
// 黑名单模式（默认）
$access_rule = array(
    'rule_type' => 'black',
    'actions' => array('edit', 'delete')  // 这些动作需要登录
);

// 白名单模式
$access_rule = array(
    'rule_type' => 'white',
    'actions' => array('index', 'view')  // 只有这些动作不需要登录
);
```

#### 声望系统

**声望计算公式**:
```
声望 = [最佳答案] * 3
     + [赞同] * 1
     - [反对] * 1
     + [发起者赞同] * 2
     - [发起者反对] * 1
```

**用户组自动升级**:
- 声望达到阈值时自动升级用户组
- 每个用户组有不同的权限配置
- reputation_group字段记录声望对应组

### 3. 话题模块 (Topic)

**控制器**: `app/topic/`
**模型**: `models/topic.php`
**核心表**: topic, topic_focus, topic_relation

#### 话题功能

1. **话题创建**: 用户创建新话题
2. **话题关注**: 用户关注话题
3. **话题合并**: 管理员合并相似话题
4. **相关话题**: 自动推荐相关话题
5. **话题广场**: 展示热门话题

#### 话题与问题关联

```php
// 问题关联话题
$this->model('topic')->add_topics_to_item(
    $question_id,
    $topics_array,
    $uid,
    'question'
);

// 获取问题的所有话题
$topics = $this->model('topic')->get_topics_by_item_id(
    $question_id,
    'question'
);
```

### 4. 搜索模块 (Search)

**控制器**: `app/search/`
**模型**: `models/search.php`, `models/search/fulltext.php`
**搜索引擎**: Zend_Search_Lucene + PHPAnalysis中文分词

#### 搜索流程

```
用户输入关键词
    ↓
PHPAnalysis中文分词
    ↓
Zend_Search_Lucene全文搜索
    ↓
返回结果（问题、文章、话题、用户）
    ↓
结果缓存 (search_cache表)
```

#### 索引更新

**问题索引更新**:
```php
// 创建或更新问题索引
$this->model('search')->push_index(
    'question',
    $question_id,
    $question_content,
    $question_detail
);
```

### 5. 通知模块 (Notifications)

**控制器**: `app/notifications/`
**模型**: `models/notify.php`
**核心表**: notification, notification_data

#### 通知类型

| 通知类型 | action_type | 说明 |
|---------|-------------|------|
| 问题邀请 | 1 | 邀请回答问题 |
| 新回答 | 2 | 关注的问题有新回答 |
| 新评论 | 3 | 回答被评论 |
| @提到 | 4 | 被@提到 |
| 赞同 | 5 | 回答被赞同 |
| 感谢 | 6 | 问题/回答被感谢 |
| 关注 | 7 | 被关注 |

#### 通知发送流程

```php
// 发送通知
$this->model('notify')->send(
    $recipient_uid,      // 接收者UID
    $sender_uid,         // 发送者UID
    $action_type,        // 通知类型
    $source_id,          // 关联ID
    $model_type,         // 模型类型
    $data                // 附加数据
);
```

### 6. 后台管理模块 (Admin)

**控制器**: `app/admin/` (15个控制器)
**主要功能**:

#### 用户管理 (app/admin/user.php)
- 用户列表和搜索
- 用户编辑和删除
- 用户组设置
- 禁用/启用用户
- 批量操作

#### 内容管理
- **问题管理** (app/admin/question.php): 编辑、删除、锁定、推荐
- **文章管理** (app/admin/article.php): 编辑、删除、推荐
- **话题管理** (app/admin/topic.php): 编辑、合并、锁定
- **评论管理**: 审核、删除评论

#### 审核系统 (app/admin/approval.php)
- 内容审核队列
- 批量审核
- 审核统计

#### 系统设置 (app/admin/main.php)
- 站点信息设置
- 注册设置
- 邮件设置
- 积分设置
- 声望设置
- 上传设置
- URL重写设置

#### 统计分析
- 用户统计
- 内容统计
- 访问统计
- ECharts图表展示

### 7. 积分模块 (Integral)

**控制器**: `app/integral/`
**模型**: `models/integral.php`
**核心表**: integral_log

#### 积分规则

| 动作 | 积分变化 | 说明 |
|------|---------|------|
| 注册 | +2000 | 新用户注册奖励 |
| 完善资料 | +100 | 完善个人资料 |
| 邀请注册 | +200 | 邀请好友注册 |
| 最佳答案 | +200 | 回答被选为最佳 |
| 提问 | -20 | 发布新问题 |
| 回答 | -5 | 发布新回答 |
| 感谢 | -10 | 感谢回答 |
| 邀请回答 | -10 | 邀请他人回答 |
| 答案折叠 | -50 | 答案被折叠 |

#### 积分操作

```php
// 增加积分
$this->model('integral')->process(
    $uid,                  // 用户ID
    'ANSWER',              // 动作类型
    -5,                    // 积分变化
    '发布回答',             // 备注
    $answer_id             // 关联ID
);

// 查询用户积分
$user_integral = $this->model('account')->get_user_info_by_uid($uid)['integral'];
```

### 8. 微信集成模块 (Weixin)

**控制器**: `app/weixin/`
**模型**: `models/weixin.php`
**核心表**: weixin_accounts, weixin_reply_rule, weixin_qr_code

#### 功能特性

1. **公众号绑定**: 支持多个微信公众号
2. **消息回复**: 关键词自动回复
3. **菜单管理**: 自定义菜单
4. **二维码**: 生成带参数二维码
5. **用户登录**: 微信扫码登录
6. **消息群发**: 群发图文消息
7. **第三方接入**: 支持第三方API

#### 消息处理流程

```
微信服务器推送消息
    ↓
app/weixin/main.php接收
    ↓
验证Token
    ↓
解析XML消息
    ↓
关键词匹配 (weixin_reply_rule表)
    ↓
调用第三方API (可选)
    ↓
返回回复消息
    ↓
输出XML响应
```

---

## API接口

### API目录结构

```
api/
├── admin_notify.php      # 管理员通知API
└── version_check.php     # 版本检查API
```

### AJAX接口

#### 问题相关AJAX (app/question/ajax.php)

**主要接口**:
```php
// 发布问题
public function publish_action()

// 发布回答
public function publish_answer_action()

// 投票
public function ajax_vote_action()

// 评论
public function add_comment_action()

// 关注问题
public function focus_action()

// 邀请回答
public function invite_user_action()
```

#### 后台管理AJAX (app/admin/ajax.php)

**主要接口**:
```php
// 保存设置
public function save_config_action()

// 删除内容
public function remove_content_action()

// 批量操作
public function batch_action()

// 上传图片
public function upload_image_action()
```

#### 用户相关AJAX (app/account/ajax.php)

**主要接口**:
```php
// 用户名检查
public function check_username_action()

// 邮箱检查
public function check_email_action()

// 登录
public function login_action()

// 注册
public function register_action()

// 验证码
public function captcha_action()
```

### 响应格式

**成功响应**:
```json
{
    "errno": 1,
    "err": "操作成功",
    "rsm": {
        // 返回数据
    }
}
```

**失败响应**:
```json
{
    "errno": -1,
    "err": "错误信息"
}
```

---

## 配置说明

### 主配置文件

**配置模板**: `system/config.dist.php`
**实际配置**: `system/config/database.php` (安装后生成)

#### 配置项说明

**Cookie配置**:
```php
define('G_COOKIE_DOMAIN', '');           // Cookie域名
define('G_COOKIE_PREFIX', 'aw_');        // Cookie前缀
```

**加密配置**:
```php
define('G_SECUKEY', 'random_key');       // 加密密钥
define('G_COOKIE_HASH_KEY', 'random');   // Cookie哈希密钥
```

**GZIP压缩**:
```php
define('G_GZIP_COMPRESS', TRUE);         // 启用GZIP压缩
```

**Session配置**:
```php
define('G_SESSION_SAVE', 'db');          // Session存储方式: db/file
```

**URL重写**:
```php
define('G_URL_REWRITE', FALSE);          // URL重写开关
```

### 数据库配置

**配置文件**: `system/config/database.php`

```php
$config['host'] = 'localhost';           // 数据库主机
$config['username'] = 'root';            // 数据库用户名
$config['password'] = '';                // 数据库密码
$config['dbname'] = 'wecenter';          // 数据库名
$config['charset'] = 'utf8';             // 字符集
$config['driver'] = 'MySQLi';            // 驱动: MySQLi/PDO_MySQL
$config['master'] = array();             // 主从配置
$config['slave'] = array();              // 从库配置
```

### 后台菜单配置

**配置文件**: `system/config/admin_menu.php`

菜单结构:
```php
array(
    'global_setting' => array(
        'menu_title' => '全局设置',
        'page_title' => '全局设置',
        'subs' => array(
            'site' => array(
                'menu_title' => '站点信息',
                'url' => 'admin/settings/site/'
            ),
            // ...
        )
    ),
    // ...
)
```

### 通知配置

**配置文件**: `system/config/notification.php`

通知类型配置:
```php
array(
    'question_invite' => array(
        'notification_type' => 'question',
        'message' => '{actor} 邀请你回答问题: {item}'
    ),
    // ...
)
```

### 邮件模板配置

**配置文件**: `system/config/email_message.php`

邮件模板:
```php
array(
    'valid_email' => array(
        'subject' => '邮箱验证 - {sitename}',
        'message' => '您好...'
    ),
    // ...
)
```

---

## 部署指南

### 环境要求

**服务器要求**:
- Web服务器: Apache/IIS/Nginx
- PHP: 5.2.2+
- MySQL: 5.0+
- PHP扩展: MySQLi 或 PDO_MySQL
- GD库 或 ImageMagick

**目录权限** (Linux/Unix):
```bash
chmod 755 ./
chmod 755 ./system/
chmod -R 755 ./system/config/
```

### 安装步骤

#### 1. 上传文件
```bash
# 上传所有文件到服务器
rsync -avz wecenter-3.1.9/ user@server:/var/www/html/
```

#### 2. 设置目录权限
```bash
cd /var/www/html/
chmod 755 ./
chmod 755 ./system/
chmod -R 755 ./system/config/
```

#### 3. 创建数据库
```sql
CREATE DATABASE wecenter DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
```

#### 4. 运行安装程序
访问: `http://yourdomain.com/install/`

安装步骤:
1. 环境检测
2. 数据库配置
3. 管理员账号设置
4. 完成安装

#### 5. 删除安装目录 (可选)
```bash
rm -rf /var/www/html/install/
```

### URL重写配置

#### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [QSA,L]
</IfModule>
```

#### Nginx (nginx.conf)

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?$1 last;
    }
}
```

### 性能优化

#### 1. 启用缓存

**Memcache配置**:
```php
// system/config.dist.php
define('G_CACHE_LEVEL', 'file');  // 改为 'memcache'
```

#### 2. 数据库优化

**索引优化**:
- 确保所有外键都有索引
- 为常用查询字段添加索引
- 定期优化表

```sql
OPTIMIZE TABLE aws_question;
OPTIMIZE TABLE aws_answer;
```

#### 3. 静态资源CDN

配置CDN加速静态资源:
```php
// 系统设置 -> 站点设置
'img_url' => 'https://cdn.yourdomain.com/static/'
```

#### 4. GZIP压缩

确保启用GZIP压缩:
```php
define('G_GZIP_COMPRESS', TRUE);
```

### 备份策略

#### 数据库备份

```bash
# 每日备份
mysqldump -u root -p wecenter > backup_$(date +%Y%m%d).sql

# 定时备份 (crontab)
0 2 * * * /usr/bin/mysqldump -u root -ppassword wecenter > /backup/wecenter_$(date +\%Y\%m\%d).sql
```

#### 文件备份

```bash
# 备份上传文件
tar -czf uploads_$(date +%Y%m%d).tar.gz uploads/

# 备份配置文件
cp -r system/config/ backup/config_$(date +%Y%m%d)/
```

### 升级步骤

1. **备份数据**:
   - 备份数据库
   - 备份上传文件
   - 备份配置文件

2. **覆盖文件**:
   ```bash
   rsync -avz --exclude='system/config/' wecenter-new/ /var/www/html/
   ```

3. **运行升级程序**:
   访问: `http://yourdomain.com/index.php?/upgrade/`

4. **测试功能**:
   - 测试登录
   - 测试发布问题
   - 测试上传文件

---

## 开发指南

### 开发环境搭建

#### 1. 本地开发环境

**推荐工具**:
- **XAMPP** (Windows/Mac/Linux)
- **MAMP** (Mac)
- **Docker** (跨平台)

**Docker配置示例**:
```yaml
version: '3'
services:
  web:
    image: php:5.6-apache
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html/
    depends_on:
      - db
  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: wecenter
```

#### 2. IDE配置

**推荐IDE**:
- **PhpStorm** (推荐)
- **VS Code** + PHP插件
- **Eclipse** + PDT

**PhpStorm配置**:
- 设置PHP版本: 5.6+
- 配置代码风格: PSR-2
- 启用Xdebug调试

### MVC开发规范

#### 创建新模块

**1. 创建控制器**:

文件: `app/mymodule/main.php`
```php
<?php

if (!defined('IN_ANWSION'))
{
    die;
}

class main extends AWS_CONTROLLER
{
    // 访问规则
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';  // 白名单
        $rule_action['actions'] = array('index');  // index不需要登录

        return $rule_action;
    }

    // 默认动作
    public function index_action()
    {
        // 设置模板
        $this->template('mymodule/index.tpl.htm');
    }

    // 其他动作
    public function detail_action()
    {
        $id = intval($_GET['id']);

        // 调用模型
        $data = $this->model('mymodule')->get_by_id($id);

        // 传递数据到视图
        $this->assign('data', $data);

        // 渲染模板
        $this->template('mymodule/detail.tpl.htm');
    }
}
```

**2. 创建模型**:

文件: `models/mymodule.php`
```php
<?php

if (!defined('IN_ANWSION'))
{
    die;
}

class mymodule_class extends AWS_MODEL
{
    // 根据ID获取数据
    public function get_by_id($id)
    {
        return $this->fetch_row('mymodule', 'id = ' . intval($id));
    }

    // 获取列表
    public function get_list($page = 1, $per_page = 20)
    {
        return $this->fetch_page('mymodule',
            'enabled = 1',
            'id DESC',
            $page,
            $per_page
        );
    }

    // 保存数据
    public function save($data)
    {
        return $this->insert('mymodule', $data);
    }

    // 更新数据
    public function update_by_id($id, $data)
    {
        return $this->update('mymodule', $data, 'id = ' . intval($id));
    }
}
```

**3. 创建视图**:

文件: `views/default/mymodule/index.tpl.htm`
```html
<?php $this->display('global/header.tpl.htm'); ?>

<div class="container">
    <h1>我的模块</h1>

    <?php if ($this->data): ?>
        <ul>
        <?php foreach($this->data as $item): ?>
            <li><?php echo $item['title']; ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php $this->display('global/footer.tpl.htm'); ?>
```

### 数据库操作

#### 基本查询

```php
// 查询单行
$row = $this->model('mymodule')->fetch_row('table_name', 'id = 1');

// 查询多行
$rows = $this->model('mymodule')->fetch_all('table_name', 'status = 1');

// 自定义SQL
$result = $this->model('mymodule')->query_all(
    "SELECT * FROM " . $this->get_table('table_name') . " WHERE id > 10"
);
```

#### 插入数据

```php
$data = array(
    'title' => 'Test',
    'content' => 'Content',
    'add_time' => time()
);

$insert_id = $this->model('mymodule')->insert('table_name', $data);
```

#### 更新数据

```php
$data = array(
    'title' => 'New Title',
    'update_time' => time()
);

$this->model('mymodule')->update(
    'table_name',
    $data,
    'id = ' . intval($id)
);
```

#### 删除数据

```php
$this->model('mymodule')->delete('table_name', 'id = ' . intval($id));
```

### AJAX开发

**AJAX控制器**:

文件: `app/mymodule/ajax.php`
```php
<?php

class ajax extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';
        $rule_action['actions'] = array();

        return $rule_action;
    }

    // AJAX接口
    public function get_data_action()
    {
        if (!$this->user_id)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '请先登录'));
        }

        $id = intval($_POST['id']);

        $data = $this->model('mymodule')->get_by_id($id);

        if (!$data)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, '数据不存在'));
        }

        H::ajax_json_output(AWS_APP::RSM(array(
            'data' => $data
        ), 1, null));
    }
}
```

**前端调用**:
```javascript
$.post('/mymodule/ajax/get_data/', {
    id: 123
}, function(result) {
    if (result.errno == 1) {
        console.log(result.rsm.data);
    } else {
        alert(result.err);
    }
}, 'json');
```

### 钩子和过滤器

**注册钩子**:
```php
// 在模型中注册钩子
AWS_APP::fire_act_hook('save_question', $question_id, $question_data);
```

**监听钩子**:
```php
// 在插件中监听钩子
AWS_APP::add_hook('save_question', 'my_plugin_callback');

function my_plugin_callback($question_id, $question_data)
{
    // 执行自定义逻辑
}
```

### 插件开发

**插件目录结构**:
```
plugins/
└── my_plugin/
    ├── plugin.php           # 插件主文件
    ├── config.php           # 插件配置
    ├── models/              # 插件模型
    ├── views/               # 插件视图
    └── static/              # 插件静态资源
```

**插件主文件** (`plugins/my_plugin/plugin.php`):
```php
<?php

class my_plugin
{
    // 插件初始化
    public static function init()
    {
        // 注册钩子
        AWS_APP::add_hook('save_question', 'my_plugin::on_save_question');
    }

    // 钩子回调
    public static function on_save_question($question_id, $data)
    {
        // 自定义逻辑
    }
}

// 初始化插件
my_plugin::init();
```

### 安全最佳实践

#### 1. SQL注入防护

```php
// ✅ 正确: 使用参数绑定
$user = $this->fetch_row('users', 'uid = ' . intval($uid));

// ✅ 正确: 使用Zend_Db参数化查询
$this->query_all("SELECT * FROM " . $this->get_table('users') .
    " WHERE email = ?", $email);

// ❌ 错误: 直接拼接SQL
$sql = "SELECT * FROM users WHERE email = '" . $_GET['email'] . "'";
```

#### 2. XSS防护

```php
// ✅ 正确: 使用htmlspecialchars
echo htmlspecialchars($user_input);

// ✅ 正确: 在保存时转义
$data['content'] = htmlspecialchars($_POST['content']);

// ❌ 错误: 直接输出用户输入
echo $_POST['content'];
```

#### 3. CSRF防护

```php
// 生成CSRF Token
$csrf_token = AWS_APP::crypt()->encode(time() . $this->user_id);

// 验证CSRF Token
if (!AWS_APP::crypt()->decode($_POST['csrf_token']))
{
    H::ajax_json_output(AWS_APP::RSM(null, -1, '请求已过期'));
}
```

#### 4. 文件上传安全

```php
// 检查文件类型
$allowed_types = array('jpg', 'jpeg', 'png', 'gif');
$file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_types))
{
    return false;
}

// 限制文件大小
if ($_FILES['file']['size'] > 512 * 1024)  // 512KB
{
    return false;
}

// 重命名文件
$new_filename = md5(time() . rand()) . '.' . $file_ext;
```

### 调试技巧

#### 1. 开启调试模式

```php
// system/config.dist.php
define('IN_DEBUG', TRUE);  // 开启调试模式
```

#### 2. 日志记录

```php
// 记录错误日志
AWS_APP::log('error', 'Error message', __FILE__, __LINE__);

// 记录SQL查询
AWS_APP::log('sql', $sql);
```

#### 3. 变量打印

```php
// 打印变量
print_r($variable);

// 格式化打印
echo '<pre>';
var_dump($variable);
echo '</pre>';
```

---

## 附录

### 常用函数

| 函数名 | 说明 |
|-------|------|
| `fetch_ip()` | 获取用户IP |
| `human_valid()` | 人机验证 |
| `redirect()` | 页面跳转 |
| `base_url()` | 获取基础URL |
| `get_setting()` | 获取系统设置 |
| `is_mobile()` | 检测移动设备 |
| `format_date()` | 格式化日期 |
| `cjk_substr()` | 中文截取 |

### 数据库表前缀

默认前缀: `aws_`

所有表名都使用前缀，例如:
- `aws_question`
- `aws_answer`
- `aws_users`

### 目录权限说明

**需要写权限的目录**:
```
./                               # 根目录
./system/                        # 系统目录
./system/config/                 # 配置目录及其子目录
./uploads/                       # 上传目录 (如果存在)
```

### 浏览器兼容性

**支持的浏览器**:
- Chrome (最新版)
- Firefox (最新版)
- Safari (最新版)
- Edge (最新版)
- IE 8+ (部分功能)

### 第三方服务集成

**支持的第三方服务**:
1. QQ互联
2. 新浪微博开放平台
3. 微信公众平台
4. 微信开放平台
5. Google OAuth
6. Facebook OAuth
7. Twitter OAuth

### 社区资源

**官方网站**: http://www.wecenter.com
**下载中心**: http://www.wecenter.com/downloads/
**支持中心**: http://www.wecenter.com/support/
**讨论区**: http://wenda.wecenter.com

---

## 版本历史

### 3.1.9 (2016-05-23)
- 最新稳定版本
- 修复已知问题
- 性能优化

### 升级记录 (2013-2015)

根据升级SQL文件，系统经历了以下主要版本升级:
- 2013-04-26: 初始版本
- 2013-06-28 至 2013-12-13: 功能增强和bug修复
- 2014-01-24 至 2014-11-21: 重大功能更新
- 2015-03-15 至 2015-11-03: 性能优化和稳定性改进

共计35个升级版本。

---

## 许可证

WeCenter 开源许可

Copyright (c) 2011 - 2016 WeCenter, Inc.

详细许可协议见 `license.txt` 文件。

---

**文档结束**

*本技术文档由代码分析工具自动生成，详细描述了 WeCenter 3.1.9 的完整技术架构、数据库设计、核心模块和开发指南。*
