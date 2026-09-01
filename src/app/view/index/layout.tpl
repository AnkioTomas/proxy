<!doctype html>
<html lang="zh-CN" class="mdui-theme-auto scroll-line">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no"/>
    <meta name="renderer" content="webkit"/>
    <title id="title">{$title}</title>
    {include file="publicHeader.tpl"}
    <style>
        .top-bar {
            transition: background-color .3s;
        }
        .top-bar:not([scrolling]) {
            background: transparent !important;
            --bar-icon-color: var(--mdui-color-on-surface-variant-dark);
        }
        .top-bar:not([scrolling]) .top-bar-utils theme-switcher::part(trigger-icon),
        .top-bar:not([scrolling]) .top-bar-utils lang-switcher::part(trigger-icon),
        .top-bar:not([scrolling]) .top-bar-utils > mdui-button-icon {
            --mdui-color-on-surface-variant: var(--bar-icon-color);
            color: rgb(var(--bar-icon-color));
        }
        .top-bar:not([scrolling]) .layout-header-user-info [slot="trigger"] {
            color: rgb(var(--bar-icon-color));
        }
    </style>
    <style id="style"></style>
    <script>
        window.__userLogged = '{$isLogin}' === '1';
    </script>
</head>

<body class="bg">

<mdui-layout class="scroll-line">
    <mdui-top-app-bar scroll-behavior="elevate" scroll-target=".layout-main" class="position-fixed top-bar">
        <div style="flex-grow: 1"></div>
        <div class="layout-header-right d-flex items-center gap-2">
            <div class="top-bar-utils d-flex items-center gap-2">
                <theme-switcher iconBtn="true"></theme-switcher>
                <lang-switcher iconBtn="true"></lang-switcher>
            </div>

            {if $user == null}
                <mdui-button-icon icon="login" href="{$login}"></mdui-button-icon>
            {else}
                <mdui-dropdown class="layout-header-user-info">
                    <span slot="trigger" class="p-0">
                        {if $user->avatar != ''}
                            <mdui-avatar src="{$user->avatar}"></mdui-avatar>
                        {else}
                            <mdui-avatar icon="account_circle"></mdui-avatar>
                        {/if}
                        <span style="color:rgba(var(--bar-icon-color))">{$userDisplayName}</span>
                    </span>
                    <mdui-menu>
                        <mdui-menu-item disabled>
                            <div style="display:flex;flex-direction:column;gap:0.15rem;min-width:10rem;">
                                <span style="font-size:0.85rem;opacity:0.72;">{$userRole}</span>
                                <span style="font-size:0.8rem;opacity:0.6;">@{$user->username}</span>
                            </div>
                        </mdui-menu-item>
                        <mdui-divider></mdui-divider>
                        <mdui-menu-item icon="dashboard" href="/home">后台管理</mdui-menu-item>
                        <mdui-menu-item icon="lock" href="/login/pwd">修改密码</mdui-menu-item>
                        <mdui-menu-item icon="logout" href="/login/logout">退出登录</mdui-menu-item>
                    </mdui-menu>
                </mdui-dropdown>
            {/if}
        </div>
    </mdui-top-app-bar>

    <mdui-layout-main class="layout-main">
        <div id="bodyContainer">
            <div id="container" class="mt-0"></div>
        </div>
        <footer class="no-translate text-center flex-shrink-0 mt-auto" style="min-height: 60px; padding: 20px 0;color: white">© {date('Y')} Ankio. All rights reserved.</footer>
    </mdui-layout-main>
</mdui-layout>

{if isset($__template_file) && $__template_file}
    <article id="page" hidden>
        {include file=$__template_file}
    </article>
{/if}
<script id="script"></script>
{include file="publicScript.tpl"}
</body>
</html>
