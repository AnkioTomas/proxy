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

        if ($host === '' || !ProxyUrl::isValidHost($host)) {
            return null;
        }

        $query = $queryString;
        if ($query !== '' && !str_starts_with($query, '?')) {
            $query = '?' . $query;
        }

        $prefix = '/go/' . $scheme . '/' . $host;
        $target = $scheme . '://' . $host . $path . $query;

        if (ProxyUrl::normalize($target) === null) {
            return null;
        }

        return [
            'scheme' => $scheme,
            'host' => $host,
            'path' => $path,
            'query' => $query,
            'prefix' => $prefix,
            'target' => $target,
        ];
    }
}
