<?php

declare(strict_types=1);

namespace app\plugins;

use ReflectionClass;

class PluginRegistry
{
    /** @var array<class-string<BasePlugin>, BasePlugin> */
    private static array $plugins = [];

    /**
     * @return array{
     *   domain: string,
     *   pattern: string,
     *   plugin: BasePlugin,
     *   map: array,
     *   target: string,
     *   username: string,
     *   password: string,
     *   timeout: int,
     *   proxy_prefix: string
     * }|null
     */
    public static function resolve(string $domain): ?array
    {
        $domain = self::normalizeDomain($domain);
        if ($domain === '') {
            return null;
        }

        $mapping = self::getMapping();
        if ($mapping === []) {
            return null;
        }

        if (array_key_exists($domain, $mapping)) {
            return self::buildResolved($domain, $domain, $mapping[$domain]);
        }

        foreach ($mapping as $pattern => $entry) {
            if (self::matchDomain((string)$pattern, $domain)) {
                return self::buildResolved($domain, (string)$pattern, $entry);
            }
        }

        return null;
    }

    private static function getMapping(): array
    {
        $proxyConfigPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'proxy.php';
        if (!file_exists($proxyConfigPath)) {
            return [];
        }

        $map = require $proxyConfigPath;
        return is_array($map) ? $map : [];
    }

    private static function buildResolved(string $domain, string $pattern, mixed $entry): ?array
    {
        $map = self::normalizeMapEntry($entry);
        $plugin = self::instantiateFromConfig($map);
        if (!$plugin instanceof BasePlugin) {
            return null;
        }

        return [
            'domain' => $domain,
            'pattern' => $pattern,
            'plugin' => $plugin,
            'map' => $map,
            'target' => (string)($map['target'] ?? ''),
            'username' => (string)($map['username'] ?? ''),
            'password' => (string)($map['password'] ?? ''),
            'timeout' => max(1, (int)($map['timeout'] ?? 30)),
            'proxy_prefix' => (string)($map['proxy_prefix'] ?? ''),
        ];
    }

    private static function normalizeMapEntry(mixed $entry): array
    {
        if (is_string($entry) && $entry !== '') {
            return ['plugin' => $entry];
        }

        if (!is_array($entry)) {
            return [];
        }

        if (!isset($entry['plugin']) && !isset($entry['class']) && isset($entry['adapter'])) {
            $entry['plugin'] = $entry['adapter'];
        }

        return $entry;
    }

    private static function instantiateFromConfig(array $entry): ?BasePlugin
    {
        $class = self::extractClassName($entry);
        if ($class === null) {
            return null;
        }

        return self::instantiateClass($class);
    }

    private static function extractClassName(array $entry): ?string
    {

        foreach (['plugin', 'class'] as $key) {
            if (!empty($entry[$key]) && is_string($entry[$key])) {
                return ltrim($entry[$key], '\\');
            }
        }

        if (!empty($entry['adapter']) && is_string($entry['adapter'])) {
            $adapter = trim($entry['adapter']);
            $candidates = [
                ltrim($adapter, '\\'),
                __NAMESPACE__ . '\\' . $adapter,
                __NAMESPACE__ . '\\' . str_replace(' ', '', ucwords(strtolower(str_replace(['-', '_'], ' ', $adapter)))),
            ];

            foreach ($candidates as $candidate) {
                if (class_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private static function instantiateClass(string $class): ?BasePlugin
    {
        $class = ltrim($class, '\\');

        if (isset(self::$plugins[$class])) {
            return self::$plugins[$class];
        }

        if (!class_exists($class) || !is_subclass_of($class, BasePlugin::class)) {
            return null;
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable() || $reflection->isAbstract()) {
            return null;
        }

        /** @var BasePlugin $plugin */
        $plugin = $reflection->newInstance();
        self::$plugins[$class] = $plugin;
        return $plugin;
    }

    private static function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        return explode(':', $domain, 2)[0];
    }

    private static function matchDomain(string $pattern, string $domain): bool
    {
        $pattern = self::normalizeDomain($pattern);
        if ($pattern === '' || $domain === '') {
            return false;
        }

        if ($pattern === '*') {
            return true;
        }

        if ($pattern === $domain) {
            return true;
        }

        if (str_starts_with($pattern, '*.')) {
            $suffix = substr($pattern, 1);
            return $suffix !== false
                && str_ends_with($domain, $suffix)
                && $domain !== ltrim($suffix, '.');
        }

        return false;
    }
}

