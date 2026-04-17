<?php

namespace app\plugins\Alist;

use app\plugins\BasePlugin;

class Alist extends BasePlugin
{
    public function responseInjector(string $body, array $headers, string $path, array $config): string
    {
        if (str_ends_with($path, '.js') && str_starts_with($path, '/assets/webauthn-json.browser-ponyfill')) {
            return $body.str_replace(
                    ['[username]','[password]'],
                    [$config['username'],$config['password']]
                    , file_get_contents(__DIR__."/AList.js"));
        }


        return $body;
    }

}