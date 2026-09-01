<?php

declare(strict_types=1);

namespace app\controller\index;

use app\service\GoTarget;
use nova\framework\core\Context;
use nova\framework\exception\ErrorHandler;
use nova\framework\http\Response;
use nova\plugin\cookie\Cookie;
use nova\plugin\proxy\ProxyResponse;

class Proxy extends BaseController
{
    public function indexPath(string $path = ''): Response
    {
        $requestPath = $this->request->getPath();
        $query = parse_url($this->request->getUri(), PHP_URL_QUERY) ?? '';
        $parsed = GoTarget::parse($requestPath, is_string($query) ? $query : '');

        if ($parsed === null) {
            return Response::asText('400 Bad Request: invalid /go/ URL', [], 400);
        }

        $debug = (string)Cookie::getInstance()->get('proxy_debug', '') === '1';

        $proxy = (new ProxyResponse(
            $parsed['target'],
            $this->request->getNowAddress(),
            $parsed['prefix']
        ))
            ->setTimeout(30)
            ->setErrorHandler(function (\Throwable $e): void {
                if (Context::instance()->isDebug()) {
                    ErrorHandler::appException($e);
                } else {
                    http_response_code(502);
                    header('Content-Type: text/plain; charset=utf-8');
                    echo 'Proxy failed: ' . $e->getMessage();
                }
            });

        if ($debug) {
            $proxy->setResponseInjector(function (string $body, array $headers, string $path): string {
                return $this->injectVConsole($body);
            });
        }

        return $proxy;
    }

    private function injectVConsole(string $body): string
    {
        if ($body === '') {
            return $body;
        }

        $lower = strtolower($body);
        if (!str_contains($lower, '<html')
            && !str_contains($lower, '<!doctype')
            && !str_contains($lower, '<body')) {
            return $body;
        }

        $snippet = <<<'HTML'
<script src="https://unpkg.com/vconsole@latest/dist/vconsole.min.js"></script>
<script>window.addEventListener("DOMContentLoaded",function(){try{new VConsole()}catch(e){}});</script>
HTML;

        if (preg_match('/<\/body>/i', $body)) {
            return preg_replace('/<\/body>/i', $snippet . '</body>', $body, 1) ?? $body;
        }

        if (preg_match('/<\/html>/i', $body)) {
            return preg_replace('/<\/html>/i', $snippet . '</html>', $body, 1) ?? $body;
        }

        return $body . $snippet;
    }
}
