<?php

declare(strict_types=1);

namespace app\plugins\Panel;

use app\plugins\BasePlugin;
use nova\plugin\login\LoginManager;

class Panel extends BasePlugin
{

    public function loginPath(): array
    {
        return ['/tomas'];
    }


    public function injectLoginJs($username,$password):string
    {
        return <<<EOF
<script>
(function() {
    waitForElement("[name=username]").then(input => {
        simulateTyping(document.querySelector('[name=username]'), "$username")
        simulateTyping(document.querySelector('[name=password]'), "$password")
        simulateHumanClick(document.querySelector('.el-checkbox__label'));
        simulateHumanClick(document.querySelector('.el-form-item__content button'));
    });
})();
</script>
EOF;
    }
}