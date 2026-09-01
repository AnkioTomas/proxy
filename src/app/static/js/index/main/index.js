window.pageLoadFiles = [
    '/static/js/components/ProxyUrlBar.js',
    '/static/js/components/ProxyQuickGrid.js',
    'Layer'
];

window.pageOnLoad = function () {
    const $body = $('body');
    const urlBar = document.getElementById('proxy-url-bar');
    const quickGrid = document.getElementById('proxy-quick-grid');

    const WALLPAPER = 'https://api.ankio.net/bing';

    const API_LIST = '/api/quicklink/list';
    const API_SAVE = '/api/quicklink/save';
    const API_REMOVE = '/api/quicklink/remove';
    const LOGIN_URL = '/index/login/index';

    let quickLinks = [];

    function readCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function writeCookie(name, value) {
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; SameSite=Lax';
    }

    function applyWallpaper() {
        $body.addClass('homepage-wallpaper');
        $body.css('--homepage-wallpaper-url', 'url("' + WALLPAPER + '")');
    }

    function requireLogin() {
        if (window.__userLogged) {
            return true;
        }
        $.toaster.error('请先登录');
        location.assign(LOGIN_URL);
        return false;
    }

    function parseProxyUrl(raw) {
        let value = String(raw || '').trim();
        if (!value || /\s/.test(value)) {
            return null;
        }
        if (!/^https?:\/\//i.test(value)) {
            value = 'https://' + value;
        }
        try {
            const parsed = new URL(value);
            if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:' || !parsed.hostname) {
                return null;
            }
            if (parsed.username || parsed.password) {
                return null;
            }
            if (!isValidProxyHost(parsed.hostname)) {
                return null;
            }
            if (parsed.port !== '' && (+parsed.port < 1 || +parsed.port > 65535)) {
                return null;
            }
            parsed.hash = '';
            return parsed;
        } catch (e) {
            return null;
        }
    }

    function isValidProxyHost(hostname) {
        if (hostname === 'localhost') {
            return true;
        }
        if (/^\d{1,3}(\.\d{1,3}){3}$/.test(hostname)) {
            return hostname.split('.').every(function (part) {
                const n = Number(part);
                return n >= 0 && n <= 255;
            });
        }
        if (hostname.includes(':')) {
            return true;
        }
        if (hostname.length > 253 || !/^[a-z0-9.-]+$/i.test(hostname)) {
            return false;
        }
        if (hostname.startsWith('.') || hostname.endsWith('.') || hostname.includes('..')) {
            return false;
        }
        return true;
    }

    function buildGoPath(raw) {
        const parsed = parseProxyUrl(raw);
        if (!parsed) {
            return null;
        }
        const scheme = parsed.protocol.replace(':', '');
        return '/go/' + scheme + '/' + parsed.host + (parsed.pathname || '/') + (parsed.search || '');
    }

    function normalizeUrl(raw) {
        const parsed = parseProxyUrl(raw);
        return parsed ? parsed.href : null;
    }

    function defaultTitle(url) {
        try {
            return new URL(url).hostname.replace(/^www\./, '');
        } catch (e) {
            return url;
        }
    }

    function loadQuickLinks() {
        if (!window.__userLogged) {
            quickLinks = [];
            quickGrid.setItems([]);
            return;
        }
        $.request.get(API_LIST, {}, function (data) {
            if (!data || data.code !== 200) {
                quickLinks = [];
                quickGrid.setItems([]);
                return;
            }
            quickLinks = Array.isArray(data.data) ? data.data.map(function (row) {
                return {
                    id: row.id,
                    url: row.url,
                    title: row.title || defaultTitle(row.url)
                };
            }) : [];
            quickGrid.setItems(quickLinks);
        }, function () {
            quickLinks = [];
            quickGrid.setItems([]);
        });
    }

    function goToUrl(url, debugOn) {
        if (!requireLogin()) {
            return;
        }
        const target = buildGoPath(url);
        if (!target) {
            $.toaster.error('请输入合法的 http(s) URL');
            return;
        }
        writeCookie('proxy_debug', debugOn ? '1' : '0');
        location.assign(target);
    }

    function openAddDialog() {
        if (!requireLogin()) {
            return;
        }
        $.layer.html({
            title: '添加快速访问',
            content: [
                '<div class="col-space16" style="padding:0.5rem 0;">',
                '  <mdui-text-field id="quickAddUrl" label="网站地址" variant="outlined" clearable placeholder="https://example.com"></mdui-text-field>',
                '  <mdui-text-field id="quickAddTitle" label="显示名称" variant="outlined" clearable placeholder="留空则使用域名"></mdui-text-field>',
                '</div>'
            ].join(''),
            style: 'width:min(24rem, 92vw);',
            buttons: [
                { text: '取消' },
                {
                    text: '添加',
                    onClick: function () {
                        const url = normalizeUrl(document.querySelector('#quickAddUrl')?.value || '');
                        if (!url) {
                            $.toaster.error('请输入合法的 http(s) URL');
                            return;
                        }
                        const title = String(document.querySelector('#quickAddTitle')?.value || '').trim() || defaultTitle(url);
                        $.request.postForm(API_SAVE, { url: url, title: title }, function (data) {
                            if (data && data.code === 200) {
                                $.toaster.success(data.msg || '保存成功');
                                loadQuickLinks();
                            } else {
                                $.toaster.error((data && data.msg) || '保存失败');
                            }
                        });
                    }
                }
            ]
        });
    }

    if (urlBar) {
        urlBar.debug = readCookie('proxy_debug') === '1';
        urlBar.addEventListener('proxy-go', function (e) {
            goToUrl(e.detail.url, e.detail.debug);
        });
    }

    if (quickGrid) {
        quickGrid.addEventListener('quick-open', function (e) {
            goToUrl(e.detail.url, urlBar ? urlBar.debug : false);
        });
        quickGrid.addEventListener('quick-add', openAddDialog);
        quickGrid.addEventListener('quick-delete', function (e) {
            if (!requireLogin()) {
                return;
            }
            $.layer.confirm({
                msg: '确定删除该快速访问？',
                yes: function () {
                    $.request.postForm(API_REMOVE, { id: e.detail.id }, function (data) {
                        if (data && data.code === 200) {
                            $.toaster.success(data.msg || '删除成功');
                            loadQuickLinks();
                        } else {
                            $.toaster.error((data && data.msg) || '删除失败');
                        }
                    });
                }
            });
        });
    }

    applyWallpaper();
    loadQuickLinks();

    window.pageOnUnLoad = function () {};
    return false;
};
