<?php

namespace app\controller\index;

use app\plugins\BasePlugin;
use nova\framework\core\Context;
use nova\framework\exception\ErrorHandler;
use nova\framework\http\Response;
use nova\plugin\login\LoginManager;
use nova\plugin\proxy\ProxyResponse;
use function nova\framework\dump;

class Proxy extends BaseController
{
    public function init(): ?Response
    {
        $parent =  parent::init();
        if (!empty($parent)) return  $parent;
        if (empty($this->proxyConfig)) {
            return $this->redirectTo("/proxy/manage");
        }
       return null;
    }

    function getWsTarget():Response
    {
        // 1. 获取 Lua 传过来的原始 Host 和 URI
        // 注意：如果是通过 capture 传参，从 $this->request->get() 获取
        $uri = $this->request->get('uri', '/');

        // 2. 匹配你的 proxyConfig
        // 假设你有一套逻辑：根据 $host 找到对应的目标 $target
        // ... 匹配逻辑 ...

        $targetBase = (string)$this->proxyConfig['target']; // 例如 http://10.0.0.5:8080

        // 拼接路径
        $finalUrl = rtrim($targetBase, '/') . $uri;

        // 直接输出字符串，不要带任何 HTML 标签
        return Response::asText($finalUrl);
    }


    public function index(): ?Response
    {
        return $this->indexPath('');
    }
    public function indexPath(string $path): Response
    {
        /** @var BasePlugin $plugin */
        $plugin = new $this->proxyConfig['plugin'];
        $config = $this->proxyConfig;


        $targetUri = $this->buildTargetUri((string)$this->proxyConfig['target']);
        if (!$this->isValidTargetUri($targetUri)) {
            return Response::asText('502 Bad Gateway', [], 502);
        }

        $user = $this->currentUser;

        $plugin->isLogin = !empty($user);

        // TODO 未来可能添加其他的sso功能

        return (new ProxyResponse(
            $targetUri,
            $this->request->getNowAddress(),
            (string)$this->proxyConfig['proxy_prefix']
        ))
            ->setTimeout((int)$this->proxyConfig['timeout'])
            ->setRequestInterceptor(function (string $rawRequest, array $urlInfo) use ($plugin): array {
                $path = $urlInfo['path'] ?? '';
                foreach ($plugin->loginPath() as $loginPath) {
                    if (str_starts_with($path, $loginPath)) {
                        if (!$plugin->isLogin) {
                            return [$rawRequest,$plugin->inject2LoginJs()];
                        }
                    }
                }
                return $plugin->requestInjector($rawRequest, $urlInfo);
            })
            ->setResponseInjector(function (string $body, array $headers, string $path) use ($plugin, $config): string {
               foreach ($plugin->loginPath() as $loginPath) {
                   if (str_starts_with($path, $loginPath)) {
                       return $plugin->injectLoginInfo($body,$config);
                   }
               }
                return $plugin->responseInjector($body, $headers, $path, $config);
            })
            ->setErrorHandler(function (\Throwable $e): void {
                if (Context::instance()->isDebug()){
                    ErrorHandler::appException($e);
                }else{
                    http_response_code(502);
                    header('Content-Type: text/plain; charset=utf-8');
                    echo 'Proxy failed: ' . $e->getMessage();
                }
            });
    }



    private function buildTargetUri(string $targetBase): string
    {
        // 确保 base 没有尾部斜杠，uri 始终以一个斜杠开头
        return rtrim($targetBase, '/') . '/' . ltrim($this->request->getUri(), '/');
    }


    private function isValidTargetUri(string $targetUri): bool
    {
        $parts = parse_url($targetUri);
        return is_array($parts) && isset($parts['scheme'], $parts['host']);
    }
}