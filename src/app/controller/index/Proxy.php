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
                if (str_starts_with($path, $plugin->loginPath())) {
                    if (!$plugin->isLogin) {
                        return [$rawRequest,$plugin->inject2LoginJs()];
                    }
                }
                return $plugin->requestInjector($rawRequest, $urlInfo);
            })
            ->setResponseInjector(function (string $body, array $headers, string $path) use ($plugin, $config): string {
                if (str_starts_with($path, $plugin->loginPath())) {
                    return $plugin->injectLoginInfo($body,$config);
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