<?php

declare(strict_types=1);

namespace app\plugins;

use nova\plugin\login\LoginManager;

abstract class BasePlugin
{


    public bool $isLogin = false;

    /**
     * Allow plugin to directly mutate the outbound raw request.
     *
     * @param string $rawRequest
     * @param array<string, mixed> $urlInfo
     * @return array{0: string, 1: string}
     */
    public function requestInjector(string $rawRequest, array $urlInfo): array
    {
        return [$rawRequest, ''];
    }

    public function responseInjector(string $body, array $headers, string $path, array $config): string
    {
        return $body;
    }

    public function injectLoginInfo(string $body,array $config): string
    {
        if ($this->isLogin){
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
            return $body.$this->injectLoginJs($username,$password);

        }else{
            return $this->inject2LoginJs();
        }
    }

    abstract function injectLoginJs($username,$password):string;

    public function inject2LoginJs(): string
    {
        $url = LoginManager::getInstance()->redirectLogin();
        return '<script>location.replace("'.$url.'")</script>';
    }


    abstract function loginPath():array;
}