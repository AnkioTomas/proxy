<?php

declare(strict_types=1);

namespace app\plugins\Panel;

use app\plugins\BasePlugin;

class Panel extends BasePlugin
{
    public function responseInjector(string $body, array $headers, string $path, array $config): string
    {
        if (str_ends_with($path, '.js') && str_starts_with($path, '/assets/js/menu-')) {
            return $body.str_replace(
                ['[username]','[password]'],
                [$config['username'],$config['password']]
                , file_get_contents(__DIR__."/Panel.js"));
        }

        return $body;
    }

}