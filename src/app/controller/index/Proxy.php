<?php

namespace app\controller\index;

use app\plugins\BasePlugin;
use nova\framework\http\Response;
use nova\plugin\proxy\ProxyResponse;

class Proxy extends BaseController
{
    public function init(): ?Response
    {
        if ($this->proxyConfig === null) {
            return $this->redirectTo("/proxy/manage");
        }
        return parent::init();
    }

    public function index(): Response
    {

        /** @var BasePlugin $plugin */
        $plugin = $this->proxyConfig['plugin'];
        $map = $this->proxyConfig['map'];


        $targetUri = $this->buildTargetUri((string)$this->proxyConfig['target']);
        if (!$this->isValidTargetUri($targetUri)) {
            return Response::asText('502 Bad Gateway', [], 502);
        }

        $user = $this->currentUser;

        // TODO 未来可能添加其他的sso功能

        return (new ProxyResponse(
            $targetUri,
            $this->request->getNowAddress(),
            (string)$this->proxyConfig['proxy_prefix']
        ))
            ->setTimeout((int)$this->proxyConfig['timeout'])
            ->setRequestInterceptor(function (string $rawRequest, array $urlInfo) use ($plugin): array {
                return $plugin->requestInjector($rawRequest, $urlInfo);
            })
            ->setResponseInjector(function (string $body, array $headers, string $path) use ($plugin, $map): string {
                return $plugin->responseInjector($body, $headers, $path, $map);
            })
            ->setErrorHandler(function (\Throwable $e): void {
                http_response_code(502);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'Proxy failed: ' . $e->getMessage();
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