<?php

declare(strict_types=1);

namespace app\service;

use app\plugins\BasePlugin;
use nova\framework\core\Instance;

class ProxyConfigService extends Instance
{
    private const int DEFAULT_TIMEOUT = 30;

    private const int MIN_TIMEOUT = 1;

    private const int MAX_TIMEOUT = 600;

    public function list(): array
    {
        $rows = $this->allRows();

        return [
            'data' => $rows,
            'count' => count($rows),
        ];
    }

    public function save(array $input): array
    {
        $domain = $this->normalizeDomain((string)($input['domain'] ?? ''));
        $oldDomain = $this->normalizeDomain((string)($input['old_domain'] ?? ''));
        $target = trim((string)($input['target'] ?? ''));
        $plugin = ltrim(trim((string)($input['plugin'] ?? '')), '\\');
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $proxyPrefix = trim((string)($input['proxy_prefix'] ?? ''));
        $timeout = (int)($input['timeout'] ?? self::DEFAULT_TIMEOUT);

        if ($domain === '') {
            return [false, '域名不能为空'];
        }
        if (!$this->isValidDomainRule($domain)) {
            return [false, '域名格式错误'];
        }
        if ($target === '') {
            return [false, '目标地址不能为空'];
        }
        if (!$this->isValidTarget($target)) {
            return [false, '目标地址必须是 http(s)://host[:port] 格式'];
        }
        if ($plugin === '') {
            return [false, '插件不能为空'];
        }
        if (!class_exists($plugin) || !is_subclass_of($plugin, BasePlugin::class)) {
            return [false, '插件类不存在或不是有效代理插件'];
        }
        if ($timeout < self::MIN_TIMEOUT || $timeout > self::MAX_TIMEOUT) {
            return [false, '超时时间范围必须为 1-600 秒'];
        }
        if ($proxyPrefix !== '' && !str_starts_with($proxyPrefix, '/')) {
            return [false, '代理前缀必须以 / 开头'];
        }

        $map = $this->readConfig();

        if ($oldDomain === '') {
            $oldDomain = $domain;
        }

        if ($oldDomain !== $domain && array_key_exists($domain, $map)) {
            return [false, '新域名已存在'];
        }

        $base = [];
        if (array_key_exists($oldDomain, $map) && is_array($map[$oldDomain])) {
            $base = $map[$oldDomain];
        }

        $base['plugin'] = $plugin;
        $base['target'] = $target;
        $base['username'] = $username;
        $base['password'] = $password;
        $base['timeout'] = $timeout;
        $base['proxy_prefix'] = $proxyPrefix;

        if ($oldDomain !== $domain) {
            unset($map[$oldDomain]);
        }
        $map[$domain] = $base;

        ksort($map);

        if (!$this->writeConfig($map)) {
            return [false, '写入配置失败'];
        }

        return [true, '保存成功'];
    }

    public function remove(string $domain): array
    {
        $domain = $this->normalizeDomain($domain);
        if ($domain === '') {
            return [false, '域名不能为空'];
        }

        $map = $this->readConfig();
        if (!array_key_exists($domain, $map)) {
            return [false, '代理记录不存在'];
        }

        unset($map[$domain]);

        if (!$this->writeConfig($map)) {
            return [false, '写入配置失败'];
        }

        return [true, '删除成功'];
    }

    public function pluginOptions(): array
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins';
        if (!is_dir($dir)) {
            return [];
        }

        $options = [];
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.php') ?: [];

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            if ($className === 'BasePlugin' ) {
                continue;
            }

            $fqcn = 'app\\plugins\\' . $className.'\\'.$className;
            if (!class_exists($fqcn) || !is_subclass_of($fqcn, BasePlugin::class)) {
                continue;
            }

            $options[] = [
                'value' => $fqcn,
                'label' => $className,
            ];
        }

        usort($options, static fn(array $a, array $b): int => strcmp((string)$a['label'], (string)$b['label']));

        return $options;
    }

    private function allRows(): array
    {
        $map = $this->readConfig();
        $rows = [];

        foreach ($map as $domain => $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $rows[] = ['domain'=>$domain, ...$entry];
        }

        return $rows;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        return explode(':', $domain, 2)[0];
    }

    private function isValidDomainRule(string $domain): bool
    {
        if ($domain === '*') {
            return true;
        }

        $host = $domain;
        if (str_starts_with($host, '*.')) {
            $host = substr($host, 2);
        }

        if ($host === 'localhost') {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    private function isValidTarget(string $target): bool
    {
        $parsed = parse_url($target);
        if (!is_array($parsed)) {
            return false;
        }

        $scheme = strtolower((string)($parsed['scheme'] ?? ''));
        return in_array($scheme, ['http', 'https'], true) && !empty($parsed['host']);
    }

    private function readConfig(): array
    {
        $path = $this->configPath();
        if (!is_file($path)) {
            return [];
        }

        $map = require $path;
        return is_array($map) ? $map : [];
    }

    private function writeConfig(array $map): bool
    {
        $path = $this->configPath();
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($map, true) . ";\n";
        $tmpPath = $path . '.tmp.' . uniqid('', true);

        $written = @file_put_contents($tmpPath, $content, LOCK_EX);
        if ($written === false) {
            return false;
        }

        if (!@rename($tmpPath, $path)) {
            @unlink($tmpPath);
            return false;
        }

        return true;
    }

    private function configPath(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'proxy.php';
    }


    public function getByDomain(string $domain): array
    {
        $domain = $this->normalizeDomain($domain);


        if ($domain === '') {
            return [];
        }

        $map = $this->readConfig();

        if (!isset($map[$domain])) {
            return [];
        }



        return $map[$domain];
    }
}
