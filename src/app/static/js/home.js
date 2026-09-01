window.pageLoadFiles = ["Layer"];

window.pageOnLoad = function () {
    const urlInput = document.querySelector("#goUrl");
    const debugBox = document.querySelector("#goDebug");
    const goBtn = document.querySelector("#btnGo");
    const quickGrid = document.querySelector("#quickGrid");

    const QUICK_LINKS_KEY = "proxy_quick_links";
    const FAVICON_API = "https://api.ankio.net/favicon?url=";
    const API_LIST = "/api/quicklink/list";
    const API_SAVE = "/api/quicklink/save";
    const API_REMOVE = "/api/quicklink/remove";

    let quickLinks = [];

    function readCookie(name) {
        const match = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()[\]\\/+^])/g, "\\$1") + "=([^;]*)"));
        return match ? decodeURIComponent(match[1]) : "";
    }

    function writeCookie(name, value) {
        document.cookie = name + "=" + encodeURIComponent(value) + "; path=/; SameSite=Lax";
    }

    if (debugBox) {
        debugBox.checked = readCookie("proxy_debug") === "1";
    }

    function buildGoPath(raw) {
        let value = String(raw || "").trim();
        if (!value) {
            return null;
        }
        if (!/^https?:\/\//i.test(value)) {
            value = "https://" + value;
        }

        let parsed;
        try {
            parsed = new URL(value);
        } catch (e) {
            return null;
        }

        if (parsed.protocol !== "http:" && parsed.protocol !== "https:") {
            return null;
        }
        if (!parsed.hostname) {
            return null;
        }

        const scheme = parsed.protocol.replace(":", "");
        const host = parsed.host;
        const path = parsed.pathname || "/";
        const search = parsed.search || "";
        return "/go/" + scheme + "/" + host + path + search;
    }

    function normalizeUrl(raw) {
        let value = String(raw || "").trim();
        if (!value) {
            return null;
        }
        if (!/^https?:\/\//i.test(value)) {
            value = "https://" + value;
        }
        try {
            const parsed = new URL(value);
            if (parsed.protocol !== "http:" && parsed.protocol !== "https:" || !parsed.hostname) {
                return null;
            }
            parsed.hash = "";
            return parsed.href;
        } catch (e) {
            return null;
        }
    }

    function defaultTitle(url) {
        try {
            return new URL(url).hostname.replace(/^www\./, "");
        } catch (e) {
            return url;
        }
    }

    function faviconUrl(url) {
        return FAVICON_API + encodeURIComponent(url);
    }

    function readLocalQuickLinks() {
        try {
            const raw = localStorage.getItem(QUICK_LINKS_KEY);
            const list = raw ? JSON.parse(raw) : [];
            return Array.isArray(list) ? list.filter(function (item) {
                return item && item.url;
            }) : [];
        } catch (e) {
            return [];
        }
    }

    function migrateLocalQuickLinks(done) {
        const localList = readLocalQuickLinks();
        if (!localList.length) {
            done();
            return;
        }

        let pending = localList.length;

        function finishOne() {
            pending -= 1;
            if (pending <= 0) {
                localStorage.removeItem(QUICK_LINKS_KEY);
                done();
            }
        }

        localList.forEach(function (item) {
            $.request.postForm(API_SAVE, {
                url: item.url,
                title: item.title || ""
            }, finishOne, finishOne);
        });
    }

    function loadQuickLinks() {
        $.request.get(API_LIST, {}, function (data) {
            if (!data || data.code !== 200) {
                $.toaster.error((data && data.msg) || "加载快速访问失败");
                quickLinks = [];
                renderQuickLinks();
                return;
            }

            quickLinks = Array.isArray(data.data) ? data.data : [];
            if (!quickLinks.length && readLocalQuickLinks().length) {
                migrateLocalQuickLinks(loadQuickLinks);
                return;
            }

            renderQuickLinks();
        }, function () {
            $.toaster.error("加载快速访问失败");
            quickLinks = [];
            renderQuickLinks();
        });
    }

    function goToUrl(url) {
        const target = buildGoPath(url);
        if (!target) {
            $.toaster.error("请输入合法的 http(s) URL");
            return;
        }
        const debugOn = !!(debugBox && debugBox.checked);
        writeCookie("proxy_debug", debugOn ? "1" : "0");
        location.assign(target);
    }

    function go() {
        goToUrl(urlInput ? urlInput.value : "");
    }

    function openAddDialog() {
        const html = [
            '<div class="col-space16" style="padding:0.5rem 0;">',
            '  <mdui-text-field id="quickAddUrl" label="网站地址" variant="outlined" clearable placeholder="https://example.com"></mdui-text-field>',
            '  <mdui-text-field id="quickAddTitle" label="显示名称" variant="outlined" clearable placeholder="留空则使用域名"></mdui-text-field>',
            "</div>"
        ].join("");

        $.layer.html({
            title: "添加快速访问",
            content: html,
            style: "width:min(24rem, 92vw);",
            buttons: [
                {
                    text: "取消"
                },
                {
                    text: "添加",
                    onClick: function () {
                        const urlField = document.querySelector("#quickAddUrl");
                        const titleField = document.querySelector("#quickAddTitle");
                        const url = normalizeUrl(urlField ? urlField.value : "");
                        if (!url) {
                            $.toaster.error("请输入合法的 http(s) URL");
                            return;
                        }
                        const title = String(titleField && titleField.value || "").trim() || defaultTitle(url);

                        $.request.postForm(API_SAVE, { url: url, title: title }, function (data) {
                            if (data && data.code === 200) {
                                $.toaster.success(data.msg || "保存成功");
                                loadQuickLinks();
                            } else {
                                $.toaster.error((data && data.msg) || "保存失败");
                            }
                        });
                    }
                }
            ]
        });
    }

    function removeQuickLink(id) {
        $.layer.confirm({
            msg: "确定删除该快速访问？",
            yes: function () {
                $.request.postForm(API_REMOVE, { id: id }, function (data) {
                    if (data && data.code === 200) {
                        $.toaster.success(data.msg || "删除成功");
                        loadQuickLinks();
                    } else {
                        $.toaster.error((data && data.msg) || "删除失败");
                    }
                });
            }
        });
    }

    function renderQuickLinks() {
        if (!quickGrid) {
            return;
        }
        quickGrid.innerHTML = "";

        quickLinks.forEach(function (item) {
            const card = document.createElement("mdui-card");
            card.className = "quick-card";
            card.dataset.url = item.url;
            card.innerHTML = [
                '<mdui-button-icon class="quick-del" icon="close"></mdui-button-icon>',
                '<img class="quick-icon" src="' + faviconUrl(item.url) + '" alt="" loading="lazy">',
                '<div class="quick-title">' + escapeHtml(item.title || defaultTitle(item.url)) + "</div>"
            ].join("");

            card.addEventListener("click", function (event) {
                if (event.target.closest(".quick-del")) {
                    return;
                }
                goToUrl(item.url);
            });

            const delBtn = card.querySelector(".quick-del");
            if (delBtn) {
                delBtn.addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    removeQuickLink(item.id);
                });
            }

            quickGrid.appendChild(card);
        });

        const addCard = document.createElement("mdui-card");
        addCard.className = "quick-card add-card";
        addCard.innerHTML = '<mdui-icon name="add"></mdui-icon><span class="quick-title">添加</span>';
        addCard.addEventListener("click", function () {
            openAddDialog();
        });
        quickGrid.appendChild(addCard);
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    if (goBtn) {
        goBtn.addEventListener("click", function (event) {
            event.preventDefault();
            go();
        });
    }

    if (urlInput) {
        urlInput.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                go();
            }
        });
    }

    loadQuickLinks();

    window.pageOnUnLoad = function () {};

    return false;
};
