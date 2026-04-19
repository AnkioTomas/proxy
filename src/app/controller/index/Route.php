<?php

declare(strict_types=1);

namespace app\controller\index;

use app\plugins\BasePlugin;
use app\service\ProxyConfigService;
use nova\framework\http\Response;
use nova\plugin\login\LoginManager;

/**
 * 轻量路由决策接口，专供 Nginx Lua 内部 subrequest 调用。
 *
 * 响应 JSON：
 *   { "action": "direct",    "target": "http://backend/path?q" }
 *   { "action": "websocket", "target": "http://backend/path?q" }
 *   { "action": "php",       "target": "http://backend/path?q", "rewrite": true }
 *   { "action": "reject",    "redirect": "https://login-url" }
 *
 * action 含义：
 *   direct    → Nginx 直接 proxy_pass，不经 PHP 代理
 *   websocket → Nginx WebSocket proxy_pass
 *   php       → 需要 PHP 处理（HTML 注入、URL 重写等）
 *   reject    → 未登录，Nginx 302 跳转到 redirect
 */
class Route extends BaseController
{
    public function init(): ?Response
    {
        // 不调用父类 init，避免 403 检查影响这里的逻辑；
        // 我们自己做轻量鉴权。

        $this->domain      = $this->request->getDomainNoPort();

        $svc = ProxyConfigService::getInstance();
        $this->proxyConfig = $svc->getByDomain($this->domain);

        return null; // 不拦截，继续到 action 方法
    }

    /**
     * GET /proxy/route_check?uri=/some/path&ws=1
     * 由 Nginx Lua subrequest 调用（internal location）。
     * ws=1 表示 WebSocket 升级请求。
     */
    public function check(): Response
    {
        // 1. 无代理配置 → 走管理面板（PHP 处理）
        if (empty($this->proxyConfig)) {
            return $this->jsonAction('php', '', false);
        }

        $uri = $this->request->get('uri', '/');
        // 只保留路径+查询部分，防止注入完整 URL
        $parsed = parse_url($uri);
        $path   = $parsed['path'] ?? '/';
        $query  = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $uri    = $path . $query;

        $targetBase  = rtrim((string)($this->proxyConfig['target'] ?? ''), '/');
        $target      = $targetBase . '/' . ltrim($uri, '/');
        $isWebSocket = $this->request->get('ws', '') === '1';

        // 2. WebSocket：已登录 → websocket（Nginx 直连），未登录 → reject
        if ($isWebSocket) {
            return $this->jsonAction('websocket', $target, false);
        }

        // 静态资源直接走 Nginx，不需要登录也不需要 PHP 处理
        if (preg_match('/\.(?:css|js|mjs|json|xml|map|png|jpe?g|gif|svg|ico|webp|avif|bmp|tiff?|woff2?|ttf|otf|eot|mp4|webm|ogg|mp3|wav|flac|aac|pdf|zip|gz|br|wasm|txt|md|csv)$/i', $path)) {
            return $this->jsonAction('direct', $target, false);
        }

        // 3. 实例化插件，判断当前 path 是否属于"登录注入路径"
        $pluginClass = (string)($this->proxyConfig['plugin'] ?? '');
        /** @var BasePlugin $plugin */
        $plugin = new $pluginClass($this->proxyConfig);
        $this->currentUser = LoginManager::getInstance()->checkLogin();
        $loginPaths = $plugin->loginPath();
        foreach ($loginPaths as $loginPath) {
            if ($loginPath === '') {
                continue;
            }
            if (str_starts_with($path, $loginPath)) {
                if (!$this->currentUser) {
                    $redirectUrl = LoginManager::getInstance()->redirectLogin();
                    return $this->jsonReject($redirectUrl);
                }
                return $this->jsonAction('php', $target, true);
            }
        }
        // 4. 普通路径： direct
        return $this->jsonAction('direct', $target, false);
    }

    // ------------------------------------------------------------------ //

    private function jsonAction(string $action, string $target, bool $rewrite): Response
    {
        return Response::asJson([
            'action'  => $action,
            'target'  => $target,
            'rewrite' => $rewrite,
        ]);
    }

    private function jsonReject(string $redirectUrl): Response
    {
        // 确保 Session 已启动并获取 session id，以便把 session cookie 一并返回给 Nginx
        $cookies = [];

        // 把已有通过 headers_list() 输出的 Set-Cookie 也收集到返回值里
        foreach (headers_list() as $h) {
            if (stripos($h, 'Set-Cookie:') === 0) {
                $cookies[] = trim(substr($h, strlen('Set-Cookie:')));
            }
        }

        /*// 主动启动 Session 并获取当前 session 名称/ID，确保客户端能收到会话 cookie
        try {
            $session = \nova\plugin\cookie\Session::getInstance();
            // start() 内部会检查是否已启动
            $session->start();
            $sid = $session->id();
            $sname = session_name();
            $sessionCookie = $sname . '=' . $sid . '; Path=/; HttpOnly';
            // 避免重复添加
            if (!in_array($sessionCookie, $cookies, true)) {
                array_unshift($cookies, $sessionCookie);
            }
        } catch (\Throwable $e) {
            // 不要因为 session 问题阻塞路由决策；只记录到日志（如果可用）
            if (function_exists('error_log')) {
                error_log('Route::jsonReject session error: ' . $e->getMessage());
            }
        }*/

        return Response::asJson([
            'action'   => 'reject',
            'redirect' => $redirectUrl,
            'cookies'  => $cookies,
        ]);
    }
}

