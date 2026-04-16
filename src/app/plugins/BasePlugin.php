<?php

declare(strict_types=1);

namespace app\plugins;

use nova\plugin\login\db\Model\UserModel;

abstract class BasePlugin
{


    public function tryInjectRequest(): bool
    {
        return false;
    }

    public function tryInjectResponse(): bool
    {
        return false;
    }

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
}