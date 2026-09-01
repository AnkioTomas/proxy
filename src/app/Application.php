<?php

declare(strict_types=1);

/*
 * Copyright (c) 2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace app;

use nova\framework\App;
use nova\framework\route\Route;
use nova\plugin\login\LoginTpl;
use function nova\framework\route;

class Application extends App
{

    public function onFrameworkStart(): void
    {
        LoginTpl::getInstance()->registerRouter('home', 'main');

        $goRoute = route('index', 'proxy', 'indexPath');

        Route::getInstance()
            ->get('/', route('index', 'main', 'index'))
            ->get('/home', route('home', 'main', 'index'))
            ->get('/api/quicklink/list', route('index', 'quicklink', 'list'))
            ->post('/api/quicklink/save', route('index', 'quicklink', 'save'))
            ->post('/api/quicklink/remove', route('index', 'quicklink', 'remove'))
            ->get('/proxy/manage', route('home', 'main', 'index'))
            ->get('/proxy/account', route('home', 'main', 'index'))
            ->get('/proxy/sso', route('home', 'main', 'index'))
            ->getOrPost('/go/{path}', $goRoute)
            ->put('/go/{path}', $goRoute)
            ->patch('/go/{path}', $goRoute)
            ->delete('/go/{path}', $goRoute)
            ->options('/go/{path}', $goRoute);
    }


    public const string SYSTEM_NAME = 'Nova Proxy';

}
