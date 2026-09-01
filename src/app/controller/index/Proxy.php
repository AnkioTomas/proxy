<?php

declare(strict_types=1);

namespace app\controller\index;

use app\service\GoTarget;
use nova\framework\core\Context;
use nova\framework\exception\ErrorHandler;
use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\cookie\Cookie;
use nova\plugin\proxy\ProxyResponse;

class Proxy extends BaseController
{
    public function init(): ?Response
    {
        if (self::isStaticResource($this->request->getPath())) {
            return Controller::init();
        }

        return parent::init();
    }

    public function indexPath(string $path = ''): Response
    {
        $requestPath = $this->request->getPath();
        $query = parse_url($this->request->getUri(), PHP_URL_QUERY) ?? '';
        $parsed = GoTarget::parse($requestPath, is_string($query) ? $query : '');

        if ($parsed === null) {
            return Response::asText('400 Bad Request: invalid /go/ URL', [], 400);
        }

        $debug = $this->isProxyDebugEnabled();

        return (new ProxyResponse(
            $parsed['target'],
            $this->request->getNowAddress(),
            $parsed['prefix']
        ))
            ->setTimeout(30)
            ->setResponseInjector(function (string $body, array $headers, string $path) use ($debug): string {
                return $this->injectProxyChrome($body, $headers, $debug);
            })
            ->setErrorHandler(function (\Throwable $e): void {
                if (Context::instance()->isDebug()) {
                    ErrorHandler::appException($e);
                } else {
                    http_response_code(502);
                    header('Content-Type: text/plain; charset=utf-8');
                    echo 'Proxy failed: ' . $e->getMessage();
                }
            });
    }

    private static function isStaticResource(string $requestPath): bool
    {
        if (!preg_match('#^/go/(?:https?)/[^/]+(/.*)?$#i', $requestPath, $m)) {
            return false;
        }

        $path = strtolower($m[1] ?? '/');
        if ($path === '/' || $path === '') {
            return false;
        }

        static $extensions = [
            '.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.ico',
            '.woff', '.woff2', '.ttf', '.eot', '.otf', '.map', '.wasm', '.webmanifest',
            '.mp4', '.webm', '.mp3', '.ogg', '.wav', '.pdf', '.zip', '.avif',
        ];

        foreach ($extensions as $ext) {
            if (str_ends_with($path, $ext)) {
                return true;
            }
        }

        $base = basename($path);

        return $base === 'manifest.json'
            || $base === 'sw.js'
            || str_starts_with($base, 'service-worker');
    }

    private function isProxyDebugEnabled(): bool
    {
        $raw = Cookie::getInstance()->get('proxy_debug', '');
        if (!is_scalar($raw)) {
            return false;
        }

        return trim((string)$raw) === '1';
    }

    /**
     * @param string[] $headerLines
     */
    private function injectProxyChrome(string $body, array $headerLines, bool $debug): string
    {
        if ($body === '' || !$this->isHtmlResponse($headerLines) || !$this->looksLikeHtml($body)) {
            return $body;
        }

        // 注入内容随 cookie 变化；禁止缓存旧 HTML（否则关 debug 仍看到 Eruda）
        header('Cache-Control: private, no-store, no-cache, must-revalidate', true);
        header('Pragma: no-cache', true);

        $snippet = $this->backButtonHtml();
        if ($debug) {
            $snippet .= $this->debugToolkitHtml();
        }

        return $this->insertBeforeCloseBody($body, $snippet);
    }

    /**
     * @param string[] $headerLines
     */
    private function isHtmlResponse(array $headerLines): bool
    {
        foreach ($headerLines as $line) {
            if (stripos($line, 'Content-Type:') !== 0) {
                continue;
            }
            $type = strtolower(trim(substr($line, strlen('Content-Type:'))));
            $type = strtok($type, ';') ?: $type;

            return str_contains($type, 'text/html');
        }

        return false;
    }

    private function looksLikeHtml(string $body): bool
    {
        $head = strtolower(ltrim(substr($body, 0, 256)));
        if (str_starts_with($head, '<!doctype html') || str_starts_with($head, '<html')) {
            return true;
        }

        return (bool) preg_match('/<html[\s>]/i', $body)
            && (bool) preg_match('/<\/body>/i', $body);
    }

    private function backButtonHtml(): string
    {
        return <<<'HTML'
<a id="duckgo-back" href="/" title="返回鸭鸭Go"
   style="position:fixed;left:16px;bottom:16px;z-index:2147483646;display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:#111;color:#fff;border-radius:999px;font:600 13px/1.2 system-ui,-apple-system,sans-serif;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.28);opacity:.92">← 返回鸭鸭Go</a>
HTML;
    }

    /**
     * 调试套件：Eruda 核心 + 官方插件全套。
     * vConsole 内置面板能力已被 Eruda 覆盖，双开只会抢入口，不值得。
     */
    private function debugToolkitHtml(): string
    {
        return <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/eruda"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-features"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-timing"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-code"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-benchmark"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-geolocation"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-orientation"></script>
<script src="https://cdn.jsdelivr.net/npm/eruda-touches"></script>
<script>
(function () {
  function boot() {
    try {
      if (!window.eruda) return;
      eruda.init();
      var plugins = [
        window.erudaFeatures,
        window.erudaTiming,
        window.erudaCode,
        window.erudaBenchmark,
        window.erudaGeolocation,
        window.erudaOrientation,
        window.erudaTouches
      ];
      for (var i = 0; i < plugins.length; i++) {
        if (plugins[i]) eruda.add(plugins[i]);
      }
    } catch (e) {}
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
</script>
HTML;
    }

    private function insertBeforeCloseBody(string $body, string $snippet): string
    {
        if (preg_match('/<\/body>/i', $body)) {
            return preg_replace('/<\/body>/i', $snippet . '</body>', $body, 1) ?? $body;
        }
        if (preg_match('/<\/html>/i', $body)) {
            return preg_replace('/<\/html>/i', $snippet . '</html>', $body, 1) ?? $body;
        }

        return $body;
    }
}
