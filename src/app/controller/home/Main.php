<?php

declare(strict_types=1);

namespace app\controller\home;

use nova\framework\http\Response;
use nova\plugin\login\controller\BaseViewController;
use nova\plugin\tpl\Pjax;

class Main extends BaseViewController
{
    public function index(): Response
    {
        return Pjax::redirectTo($this->firstUri());
    }

    protected function getMenu(): array
    {
        return [];
    }
}
