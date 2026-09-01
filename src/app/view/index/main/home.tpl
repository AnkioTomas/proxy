<title id="title">{$title}</title>
<style id="style">
    .go-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        width: 100%;
    }

    .go-bar mdui-text-field {
        flex: 1 1 16rem;
        min-width: 0;
    }

    .go-bar .go-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }
</style>

<div id="container" class="container dashboard-v2 h-fit">
    <div class="row col-space16">
        <div class="col-xs-12 title-large center-vertical mb-4">
            <mdui-icon name="travel_explore" class="refresh mr-2"></mdui-icon>
            <span>代理中转</span>
        </div>

        <div class="col-xs-12">
            <div class="go-bar">
                <mdui-text-field
                    id="goUrl"
                    label="目标地址"
                    variant="outlined"
                    clearable
                    placeholder="https://example.com/path"
                    helper="输入完整 http(s) URL，点击 Go 开始中转"
                ></mdui-text-field>
                <div class="go-actions">
                    <mdui-checkbox id="goDebug">调试</mdui-checkbox>
                    <mdui-button id="btnGo" icon="arrow_forward">Go</mdui-button>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="script" src="/static/js/home.js?v={$__v}"></script>
