<?php

namespace app\plugins\Authorization;

use app\plugins\BasePlugin;

class Authorization extends BasePlugin
{

    public function requestInjector(string $rawRequest, array $urlInfo): array
    {
        $username = (string)($this->config['username'] ?? '');
        $password = (string)($this->config['password'] ?? '');
        if ($username !== '') {
            // Only add if no Authorization header already present
            if (stripos($rawRequest, "Authorization:") === false) {
                $token = base64_encode($username . ':' . $password);
                // Insert header before the first blank line (i.e., before CRLF CRLF)
                $parts = preg_split("/\r?\n\r?\n/", $rawRequest, 2);
                if (is_array($parts) && count($parts) === 2) {
                    $head = $parts[0];
                    $body = $parts[1];
                    $head .= "\r\nAuthorization: Basic $token";
                    $rawRequest = $head . "\r\n\r\n" . $body;
                } else {
                    // Fallback: append header at end
                    $rawRequest = rtrim($rawRequest, "\r\n") . "\r\nAuthorization: Basic $token\r\n\r\n";
                }
            }
        }

        return [$rawRequest,''];
    }

    function injectLoginJs($username, $password): string
    {
        return '';
    }

    function loginPath(): array
    {
        return ['/'];
    }
}