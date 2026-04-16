window.pageLoadFiles = [
    jsMap.DataTable,
    jsMap.DialogForm,
    "Layer"
];

window.pageOnLoad = function () {
    const table = new DataTable("#proxyTable");
    const dialog = document.querySelector("#proxyDialog");
    const tableContainer = document.querySelector("#proxyTable");

    function loadPluginOptions(selectedValue) {
        $.request.get("/proxy/api/plugins", {}, function (ret) {
            if (!ret || ret.code !== 200) {
                $.toaster.error((ret && ret.msg) || "插件列表加载失败");
                return;
            }
            $.form.setSelectOptions("#pluginSelect", ret.data || []);
            if (selectedValue) {
                const pluginSelect = document.querySelector("#pluginSelect");
                if (pluginSelect) {
                    pluginSelect.value = selectedValue;
                    $(pluginSelect).trigger("change");
                }
            }
        });
    }

    function openCreateDialog() {
        dialog.setLabel("新增代理");
        $.form.reset("#editorForm");
        document.querySelector("#old_domain").value = "";
        loadPluginOptions();
        dialog.open(true);
    }

    function openEditDialog(row) {
        dialog.setLabel("编辑代理");
        $.form.reset("#editorForm");
        loadPluginOptions(row.plugin);
        dialog.setValue({
            old_domain: row.domain,
            domain: row.domain,
            plugin: row.plugin,
            target: row.target,
            username: row.username,
            password: row.password,
            timeout: row.timeout,
            proxy_prefix: row.proxy_prefix
        });
        dialog.open(false);
    }

    function removeProxy(row) {
        $.layer.confirm({
            title: "删除确认",
            msg: "确定要删除代理 <b>" + row.domain + "</b> 吗？",
            yes: function () {
                $.request.postForm("/proxy/remove", {domain: row.domain}, function (ret) {
                    if (!ret || ret.code !== 200) {
                        $.toaster.error((ret && ret.msg) || "删除失败");
                        return;
                    }
                    $.toaster.success(ret.msg || "删除成功");
                    table.reload(undefined, true);
                });
            }
        });
    }

    dialog.submit("/proxy/save", function () {
        table.reload(undefined, true);
    }, function (data) {
        const timeout = Number(data.timeout || 0);
        if (!data.domain || data.domain.trim() === "") {
            $.toaster.error("域名不能为空");
            return false;
        }
        if (!data.target || data.target.trim() === "") {
            $.toaster.error("目标地址不能为空");
            return false;
        }
        if (!Number.isFinite(timeout) || timeout < 1 || timeout > 600) {
            $.toaster.error("超时时间范围为 1-600 秒");
            return false;
        }
        return true;
    });

    table.load({
        uri: "/proxy/api/proxyList",
        mobile: true,
        selectable: false,
        lineHeight: "48px",
        height: "auto",
        break: true,
        page: false,
        requestMeta: false,
        columns: [
            {field: "domain", name: "域名", align: "left", sortable: false, width: 180},
            {field: "target", name: "目标地址", align: "left", sortable: false, width: 260},
            {field: "plugin", name: "插件", align: "left", sortable: false, width: 220},
            {field: "timeout", name: "超时(s)", align: "center", sortable: false, width: 100},
            {field: "proxy_prefix", name: "代理前缀", align: "left", sortable: false, width: 140},
            {field: "username", name: "用户名", align: "left", sortable: false, width: 140},
            {
                field: "actions",
                name: "操作",
                align: "center",
                fixed: "right",
                width: 120,
                formatter: function (value, row, index) {
                    return "<div class=\"proxy-action-buttons\">" +
                        "<mdui-button-icon icon=\"edit\" data-action=\"edit\" data-row-index=\"" + index + "\"></mdui-button-icon>" +
                        "<mdui-button-icon icon=\"delete\" data-action=\"remove\" data-row-index=\"" + index + "\"></mdui-button-icon>" +
                        "</div>";
                }
            }
        ]
    });

    if (tableContainer) {
        tableContainer.addEventListener("click", function (event) {
            const target = event.target.closest("[data-action][data-row-index]");
            if (!target) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const rowIndex = Number(target.getAttribute("data-row-index"));
            if (!Number.isInteger(rowIndex) || rowIndex < 0) {
                return;
            }

            const row = table.getRow(rowIndex);
            if (!row) {
                return;
            }

            const action = String(target.getAttribute("data-action") || "");
            if (action === "edit") {
                openEditDialog(row);
                return;
            }

            if (action === "remove") {
                removeProxy(row);
            }
        });
    }

    $("#btnAdd").on("click", function () {
        openCreateDialog();
    });

    $("#btnRefresh").on("click", function () {
        table.reload();
    });

    window.pageOnUnLoad = function () {
        table.destroy();
    };

    return false;
};