<?php

namespace app\plugins\Alist;

use app\plugins\BasePlugin;
use nova\plugin\login\LoginManager;

class Alist extends BasePlugin
{
    public function responseInjector(string $body, array $headers, string $path, array $config): string
    {

        return $body;
    }


    public function loginPath(): array
    {
        return ['/@login'];
    }


    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
     waitForElement("[name=username]").then(input => {
        simulateTyping(document.querySelector('[name=username]'), "$username")
        simulateTyping(document.querySelector('[name=password]'), "$password")
        simulateHumanClick(document.querySelectorAll(".hope-button")[1]);
    });
})();
</script>
EOF;
    }



}