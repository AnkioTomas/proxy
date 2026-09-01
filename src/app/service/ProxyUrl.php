<?php

declare(strict_types=1);

namespace app\service;

/**
 * 代理目标 URL 规范化与校验（仅允许标准 http/https URL）。
 */
final class ProxyUrl
{
    public static function normalize(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '' || preg_match('/\s/u', $value)) {
            return null;
        }

        if (!preg_match('/^https?:\/\//i', $value)) {
            $value = 'https://' . $value;
        }

        $parts = parse_url($value);
        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return null;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        if (!self::isValidHost((string)$parts['host'])) {
            return null;
        }

        if (isset($parts['port'])) {
            $port = (int)$parts['port'];
            if ($port < 1 || $port > 65535) {
                return null;
            }
        }

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        $url = $scheme . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= $path;
        if (!empty($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;
    }

    public static function isValidHost(string $host): bool
    {
        $host = trim($host);
        if ($host === '' || strlen($host) > 253) {
            return false;
        }

        if (str_contains($host, '@') || str_contains($host, ' ') || str_contains($host, '/')) {
            return false;
        }

        $hostOnly = $host;
        $port = null;
        if (str_starts_with($host, '[')) {
            $closing = strpos($host, ']');
            if ($closing === false) {
                return false;
            }
            $hostOnly = substr($host, 1, $closing - 1);
            if ($closing < strlen($host) - 1) {
                if ($host[$closing + 1] !== ':') {
                    return false;
                }
                $port = substr($host, $closing + 2);
            }
        } elseif (str_contains($host, ':')) {
            [$hostOnly, $port] = explode(':', $host, 2);
        }

        if ($port !== null && ($port === '' || !ctype_digit($port) || (int)$port < 1 || (int)$port > 65535)) {
            return false;
        }

        if ($hostOnly === 'localhost') {
            return true;
        }

        if (filter_var($hostOnly, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return filter_var($hostOnly, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}
