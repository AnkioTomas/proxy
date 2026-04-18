<?php

declare(strict_types=1);

namespace app\controller\index;

use app\Application;
use nova\framework\http\Response;
use nova\plugin\login\LoginManager;
use nova\plugin\login\manager\PwdLoginManager;
use nova\plugin\login\manager\SSOLoginManager;
use nova\plugin\tpl\ViewResponse;

class Main extends BaseController
{

    protected ViewResponse $viewResponse;

    public function init(): ?Response
    {
        $response = parent::init();
        if ($response instanceof Response) {
            return $response;
        }
        if (empty($this->currentUser)) return $this->redirectTo(LoginManager::getInstance()->redirectLogin());

        if (!empty($this->proxyConfig)) {
            return $this->redirectTo('/403');
        }


        $this->viewResponse = new ViewResponse();
        $this->viewResponse->init('', [
            'title' => Application::SYSTEM_NAME,
            'currentUrl' => $this->request->getUri()
        ]);


        if (!$this->request->isPjax()) {
            return $this->viewResponse->asTpl('layout', [
                'menuConfig' => $this->menuConfig(),
            ]);
        }

        return null;
    }

    /**
     * 后台侧边菜单。
     *
     * @return array<int, array<string, mixed>>
     */
    protected function menuConfig(): array
    {
        return [
            [
                "title" => "代理管理",
                "url" => "/proxy/manage",
                "icon" => "library_books",
                "pjax" => true
            ],
            [
                "title" => "系统设置",
                "icon" => "settings",
                "sub" => [
                    [
                        "title" => "账户安全",
                        "url" => "/proxy/account",
                        "icon" => "security",
                        "pjax" => true
                    ],
                    [
                        "title" => "统一认证登录",
                        "url" => "/proxy/sso",
                        "icon" => "vpn_key",
                        "pjax" => true
                    ],
                ]
            ]
        ];
    }
    public function manage(): Response
    {
        return $this->viewResponse->asTpl();
    }

    public function account(): Response
    {
        return $this->viewResponse->asTpl(PwdLoginManager::TPL_PASSWORD, [
            "username" => $this->currentUser->username,
        ]);
    }

    public function sso(): Response
    {
        return $this->viewResponse->asTpl(SSOLoginManager::TPL_SSO);
    }
}