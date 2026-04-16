<?php

declare(strict_types=1);

namespace app\plugins;

use nova\plugin\login\db\Model\UserModel;

abstract class BasePlugin
{


    /**
     * Allow plugin to directly mutate the outbound raw request.
     *
     * @param string $rawRequest
     * @param array<string, mixed> $urlInfo
     * @return array{0: string, 1: string}
     */
    public function requestInjector(string $rawRequest, array $urlInfo): array
    {
        return [$rawRequest, $urlInfo];
    }

    public function responseInjector(string $body, array $headers, string $path, array $map): string
    {
        return $body;
    }
}