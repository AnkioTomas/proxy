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

use app\plugins\PluginRegistry;
use nova\framework\App;

use nova\framework\route\Route;
use nova\framework\route\RouteObject;
use function nova\framework\route;

class Application extends App
{

    public function onFrameworkStart(): void
    {
        $proxyRoute = route("index","proxy","index");
        $proxyRoute2 = route("index","proxy","indexPath");
        Route::getInstance()
            ->get('/proxy/get_ws_target', route("index","proxy","getWsTarget"))
            ->get('/proxy/api/{action}', route("index", "api", "{action}"))
            ->post('/proxy/api/{action}', route("index", "api", "{action}"))
            ->get('/proxy/{action}', route("index","main","{action}"))
            ->post('/proxy/{action}', route("index","api","{action}"))
            ->getOrPost('/', $proxyRoute)
            ->put('/', $proxyRoute)
            ->patch('/', $proxyRoute)
            ->delete('/', $proxyRoute)
            ->options('/', $proxyRoute)
            ->getOrPost('/{path}', $proxyRoute2)
            ->put('/{path}', $proxyRoute2)
            ->patch('/{path}', $proxyRoute2)
            ->delete('/{path}', $proxyRoute2)
            ->options('/{path}', $proxyRoute2);
    }


    public const string SYSTEM_NAME =  "Nova Proxy";

}
