<?php

namespace app\plugins\Gitea;

use app\plugins\BasePlugin;

class Gitea extends BasePlugin
{
    public function loginPath(): array
    {
        return ['/user/login'];
    }


    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
     waitForElement("[name=user_name]").then(input => {
        simulateTyping(document.querySelector('[name=user_name]'), "$username")
        simulateTyping(document.querySelector('[name=password]'), "$password")
        simulateHumanClick(document.querySelectorAll("button")[1]);
    });
})();
</script>
EOF;
    }



}