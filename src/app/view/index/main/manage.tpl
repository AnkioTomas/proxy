<title id="title">代理管理 - {$title}</title>
<style id="style">
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .table-wrapper {
        width: 100%;
        overflow: hidden;
    }

    .proxy-action-buttons {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .proxy-action-buttons [data-action="remove"] {
        color: rgb(var(--mdui-color-error));
    }
</style>

<div id="container" class="container dashboard-v2 h-fit">
    <div class="row col-space16">
        <div class="col-xs-12 title-large center-vertical mb-4">
            <mdui-icon name="badge" class="refresh mr-2"></mdui-icon>
            <span>代理管理</span>
        </div>

        <div class="col-xs-12">
            <div class="toolbar mb-4">
                <mdui-button id="btnAdd" icon="add">新增代理</mdui-button>
                <mdui-button id="btnRefresh" variant="outlined" icon="refresh">刷新</mdui-button>
            </div>
            <div id="proxyTable" class="table-wrapper"></div>
        </div>
    </div>

    <mdui-dialog-form id="proxyDialog" label="代理配置" saveName="保存"  closeOnSave>
        <form id="editorForm" class="row col-space16">
            <input type="hidden" name="old_domain" id="old_domain" value="">

            <div class="col-xs-12 col-md-6">
                <mdui-text-field label="域名" name="domain" variant="outlined" required helper="支持 example.com 或 *.example.com"></mdui-text-field>
            </div>

            <div class="col-xs-12 col-md-6">
                <mdui-text-field label="目标地址" name="target" variant="outlined" required helper="示例: http://127.0.0.1:8080"></mdui-text-field>
            </div>

            <div class="col-xs-12 col-md-6">
                <mdui-select label="插件" name="plugin" id="pluginSelect" variant="outlined" required></mdui-select>
            </div>

            <div class="col-xs-12 col-md-6">
                <mdui-text-field label="超时时间(秒)" name="timeout" type="number" min="1" max="600" value="30" variant="outlined" required></mdui-text-field>
            </div>

            <div class="col-xs-12 col-md-6">
                <mdui-text-field label="代理前缀" name="proxy_prefix" variant="outlined" helper="可留空，例如 /admin"></mdui-text-field>
            </div>

            <div class="col-xs-12 col-md-6">
                <mdui-text-field label="用户名" name="username" variant="outlined"></mdui-text-field>
            </div>

            <div class="col-xs-12">
                <mdui-text-field label="密码" name="password" type="password" variant="outlined"></mdui-text-field>
            </div>
        </form>
    </mdui-dialog-form>

</div>

<script id="script" src="/static/js/manage.js?v={$__v}"></script>
