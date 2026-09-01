<title id="title">{$title}</title>
<style id="style">
    .go-row {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .go-row mdui-text-field {
        flex: 1 1 auto;
        min-width: 0;
    }

    .go-row #btnGo {
        flex-shrink: 0;
        height: 3.5rem;
        min-width: 5rem;
    }

    .debug-row {
        margin-top: 0.75rem;
    }

    .quick-section {
        margin-top: 2rem;
    }

    .quick-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
        gap: 0.75rem;
    }

    .quick-card {
        position: relative;
        cursor: pointer;
        text-align: center;
        padding: 1rem 0.5rem 0.75rem;
        user-select: none;
    }

    .quick-card:hover .quick-del {
        opacity: 1;
    }

    .quick-icon {
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto 0.5rem;
        object-fit: contain;
        border-radius: 0.375rem;
    }

    .quick-title {
        font-size: 0.8125rem;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .quick-del {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        opacity: 0;
        transition: opacity 0.15s;
    }

    @media (hover: none) {
        .quick-del {
            opacity: 1;
        }
    }

    .quick-card.add-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 6.5rem;
        border: 2px dashed rgba(var(--mdui-color-outline), 0.5);
        background: transparent;
        box-shadow: none;
    }

    .quick-card.add-card mdui-icon {
        font-size: 2rem;
        opacity: 0.6;
        margin-bottom: 0.25rem;
    }

    .quick-empty {
        color: rgba(var(--mdui-color-on-surface), 0.5);
        font-size: 0.875rem;
        text-align: center;
        padding: 1.5rem 0;
    }
</style>

<div id="container" class="container dashboard-v2 h-fit">
    <div class="row col-space16">
        <div class="col-xs-12 title-large center-vertical mb-4">
            <mdui-icon name="travel_explore" class="refresh mr-2"></mdui-icon>
            <span>代理中转</span>
        </div>

        <div class="col-xs-12">
            <div class="go-row">
                <mdui-text-field
                    id="goUrl"
                    label="目标地址"
                    variant="outlined"
                    clearable
                    placeholder="https://example.com/path"
                ></mdui-text-field>
                <mdui-button id="btnGo" variant="filled" end-icon="arrow_forward">Go</mdui-button>
            </div>
            <div class="debug-row">
                <mdui-checkbox id="goDebug">调试</mdui-checkbox>
            </div>
        </div>

        <div class="col-xs-12 quick-section">
            <div class="quick-section-head">
                <span class="title-medium">快速访问</span>
            </div>
            <div id="quickGrid" class="quick-grid"></div>
        </div>
    </div>
</div>

<script id="script" src="/static/js/home.js?v={$__v}"></script>
