<?php

declare(strict_types=1);

namespace app\controller\index;

use nova\framework\http\Response;
use nova\plugin\login\controller\BaseViewController;
use nova\plugin\tpl\Pjax;

class Main extends BaseViewController
{
    public function init(): ?Response
    {
        $response = parent::init();

        if ($this->userModel !== null
            && !in_array('all', $this->userModel->role()->permissions, true)) {
            return Pjax::responseError(403);
        }

        return $response;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getMenu(): array
    {
        return [
            [
                'title' => '首页',
                'url' => '/',
                'icon' => 'home',
                'pjax' => true,
            ],
        ];
    }

    public function home(): Response
    {
        return $this->viewResponse->asTpl();
    }

    public function legacyRedirect(): Response
    {
        return Pjax::redirectTo('/');
    }
}
