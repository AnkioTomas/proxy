<?php

namespace app\plugins\Adguard;

use app\plugins\BasePlugin;

class Adguard extends BasePlugin
{
    public function loginPath(): array
    {
        return ['/login.html'];
    }


    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
     waitForElement("[name=username]").then(input => {
        simulateTyping(document.querySelector('[name=username]'), "$username")
        simulateTyping(document.querySelector('[name=password]'), "$password")
        simulateHumanClick(document.querySelector('[data-testid="sign_in"]'));
    });
})();
</script>
EOF;
    }



}