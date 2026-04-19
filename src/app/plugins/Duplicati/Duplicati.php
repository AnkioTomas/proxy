<?php

namespace app\plugins\Duplicati;

use app\plugins\BasePlugin;
use nova\framework\core\Logger;

class Duplicati extends BasePlugin
{

    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
     waitForElement("[name=password]").then(input => {
        simulateTyping(document.querySelector('[name=password]'), "$password")
        simulateHumanClick(document.querySelector(".login-button"));
    });
})();
</script>
EOF;
    }

    function loginPath(): array
    {
        return ['/ngclient/index.html','/ngclient/login'];
    }
}