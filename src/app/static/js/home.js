window.pageLoadFiles = [];

window.pageOnLoad = function () {
    const urlInput = document.querySelector("#goUrl");
    const debugBox = document.querySelector("#goDebug");
    const goBtn = document.querySelector("#btnGo");

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

    function go() {
        const target = buildGoPath(urlInput ? urlInput.value : "");
        if (!target) {
            $.toaster.error("请输入合法的 http(s) URL");
            return;
        }

        const debugOn = !!(debugBox && debugBox.checked);
        writeCookie("proxy_debug", debugOn ? "1" : "0");
        location.assign(target);
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

    window.pageOnUnLoad = function () {};

    return false;
};
