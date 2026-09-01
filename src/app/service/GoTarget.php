<?php

declare(strict_types=1);

namespace app\service;

/**
 * 解析 /go/{scheme}/{host}/{path} 路径代理目标。
 */
final class GoTarget
{
    /**
     * @return array{scheme:string,host:string,path:string,query:string,prefix:string,target:string}|null
     */
    public static function parse(string $requestPath, string $queryString = ''): ?array
    {
        $requestPath = '/' . ltrim($requestPath, '/');
        if (!preg_match('#^/go/(https?)/([^/]+)(/.*)?$#i', $requestPath, $m)) {
            return null;
        }

        $scheme = strtolower($m[1]);
        $host = $m[2];
        $path = $m[3] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        if ($host === '' || !self::isValidHost($host)) {
            return null;
        }

        $query = $queryString;
        if ($query !== '' && !str_starts_with($query, '?')) {
            $query = '?' . $query;
        }

        $prefix = '/go/' . $scheme . '/' . $host;
        $target = $scheme . '://' . $host . $path . $query;

        return [
            'scheme' => $scheme,
            'host' => $host,
            'path' => $path,
            'query' => $query,
            'prefix' => $prefix,
            'target' => $target,
        ];
    }

    public static function isValidHost(string $host): bool
    {
        $hostOnly = explode(':', $host, 2)[0];
        if ($hostOnly === 'localhost') {
            return true;
        }
        if (filter_var($hostOnly, FILTER_VALIDATE_IP)) {
            return true;
        }

        return filter_var($hostOnly, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}
