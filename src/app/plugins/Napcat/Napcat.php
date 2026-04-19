<?php

namespace app\plugins\Napcat;

use app\plugins\BasePlugin;

class Napcat extends BasePlugin
{
    public function loginPath(): array
    {
        return ['/webui/'];
    }


    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
     waitForElement("[name=password]").then(input => {
        simulateTyping(document.querySelector('[name=password]'), "$password")
          simulateHumanClick(document.querySelectorAll("button")[1]);
    });
})();
</script>
EOF;
    }



}