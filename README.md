<p align="center">
  <img src="src/app/static/icons/android-chrome-192x192.png" alt="鸭鸭Go" width="128">
</p>

<h1 align="center">鸭鸭Go</h1>

<p align="center">基于 <a href="src/nova/framework/README.md">Nova</a> 的 HTTP/HTTPS 路径代理 Web 应用</p>

首页输入目标 URL 即可通过 `/go/` 路径代理访问，支持 HTML 重写、重定向修正与 Debug 模式；登录用户可管理服务端持久化的快速访问列表。

## 功能概览

- **路径代理**：通过 `/go/{scheme}/{host}/...` 访问任意 http(s) 站点，PHP 层完成 Host 重写、HTML/JS 注入与 Location 修正。
- **首页中转**：`/` 提供 URL 输入框、Go 跳转与 Debug 开关（vConsole）；默认必应壁纸。
- **快速访问**：登录用户的常用站点保存在数据库，支持增删。
- **后台管理**：`/home` 为登录插件提供的管理入口（用户、角色、登录记录、OIDC 等）。
- **鉴权**：代理与快速访问 API 需登录且角色权限包含 `all`；首页本身可匿名浏览。

## 技术栈

| 层级 | 说明 |
|------|------|
| 后端 | Nova（PHP），MVC，模板渲染，PJAX |
| 代理 | [`nova-proxy`](src/nova/plugin/proxy/) — 请求转发、内容重写、字符集处理 |
| 认证 | [`nova-login`](src/nova/plugin/login/) — 密码 / OIDC 登录、RBAC |
| 数据 | [`nova-orm`](src/nova/plugin/orm/) — MySQL / SQLite / PostgreSQL |
| 前端 | MDUI 3、自定义元素（`proxy-url-bar`、`proxy-quick-grid`）、[`/static/bundle`](src/nova/plugin/tpl/handler/StaticHandler.php) |

## 环境要求

- **PHP**：建议 **8.3+**
- **扩展**：curl、json、mbstring、zlib 等 Nova 运行时常见扩展
- **数据库**：MySQL（推荐）或 SQLite / PostgreSQL
- **Web 服务器**：Nginx / Apache / Caddy，或 PHP 内置服务器（开发用）
- **Git**：克隆时需初始化 submodule

## 快速开始

### 1. 克隆仓库

```bash
git clone --recursive <repo-url> proxy
cd proxy
```

若已克隆但未拉取 submodule：

```bash
git submodule update --init --recursive
```

### 2. 配置

```bash
cp src/example.config.php src/config.php
```

编辑 `src/config.php`：填写 `db` 连接信息，按需调整 `debug`、`timezone` 等。  
`src/config.php` 已在 `.gitignore` 中，不会提交到版本库。

### 3. 启动（开发）

```bash
cd src/public
php -S 0.0.0.0:10211
```

浏览器访问 `http://127.0.0.1:10211/`。

### 4. 生产部署

- 将站点根指向 **`src/public/`**
- 参考根目录 [`nginx.conf`](nginx.conf) 配置 URL 重写（所有非静态请求转发到 `index.php`）
- 关闭 `config.php` 中的 `debug`
- 确保 `src/runtime/` 可写

## 目录结构（简要）

```
src/
├── app/                    # 应用代码
│   ├── controller/         # index（前台 + 代理）、home（后台壳）
│   ├── db/                 # QuickLink Model / Dao
│   ├── service/            # GoTarget 路径解析
│   ├── view/               # 模板
│   └── static/             # 前端静态资源（/static/ 对外）
├── nova/
│   ├── framework/          # Nova 框架
│   └── plugin/             # 插件（见下表）
├── public/                 # Web 入口 index.php
├── config.php              # 应用配置（本地，不入库）
├── example.config.php      # 配置模板
└── runtime/                # 日志、缓存（需可写）
```

## 插件依赖

当前项目保留的 Nova 插件：

| 插件 | 用途 |
|------|------|
| `proxy` | 核心 HTTP 代理 |
| `login` | 认证与后台菜单 |
| `tpl` | 视图编译与静态资源 |
| `orm` | 数据库 ORM |
| `cookie` | Session / Cookie |
| `device` | 登录记录 UA 解析 |
| `avatar` | 用户头像 |
| `captcha` | 登录验证码 |
| `http` | OIDC SSO 请求 |

> 本项目不使用 `workerman`、`ip` 插件，已从 submodule 中移除。

## 主要路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/` | 前台首页（URL 输入 + 快速访问） |
| GET | `/home` | 后台管理（需登录） |
| GET/POST/… | `/go/{path}` | 代理入口，如 `/go/https/www.example.com/` |
| GET | `/api/quicklink/list` | 快速访问列表（需登录） |
| POST | `/api/quicklink/save` | 保存快速访问（需登录） |
| POST | `/api/quicklink/remove` | 删除快速访问（需登录） |
| GET | `/index/login/index` | 登录页 |
| GET | `/proxy/manage` 等 | 兼容旧路径，重定向到 `/home` |

### 代理 URL 格式

```
/go/{scheme}/{host}/{path}?{query}
```

示例：`https://www.baidu.com/s?wd=test` →  
`/go/https/www.baidu.com/s?wd=test`

## 开发说明

- 路由注册：[`src/app/Application.php`](src/app/Application.php)
- 代理控制器：[`src/app/controller/index/Proxy.php`](src/app/controller/index/Proxy.php)
- 路径解析：[`src/app/service/GoTarget.php`](src/app/service/GoTarget.php)
- 首页脚本：[`src/app/static/js/index/main/index.js`](src/app/static/js/index/main/index.js)
- 快速访问 API：[`src/app/controller/index/Quicklink.php`](src/app/controller/index/Quicklink.php)
- Nova 工具链（build / test / plugin 等）：[`Nova-Usage.md`](Nova-Usage.md)

## 许可证

MIT
