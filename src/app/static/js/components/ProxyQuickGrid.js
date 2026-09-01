class ProxyQuickGrid extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._items = [];
    }

    connectedCallback() {
        this.shadowRoot.innerHTML = `
            <style>
                :host { display: block; }
                .group {
                    padding: 1rem;
                    width: 100%;
                    margin-top: 0.5rem;
                    box-sizing: border-box;
                    background: rgba(var(--mdui-color-surface-container), 0.72) !important;
                    border: 1px solid rgba(var(--mdui-color-outline-variant), 0.45);
                    backdrop-filter: blur(16px) saturate(140%);
                    -webkit-backdrop-filter: blur(16px) saturate(140%);
                    box-shadow: var(--mdui-elevation-level1);
                }
                :host-context(.mdui-theme-dark) .group {
                    background: rgba(var(--mdui-color-surface-container), 0.5) !important;
                }
                @media (prefers-color-scheme: dark) {
                    :host-context(.mdui-theme-auto) .group {
                        background: rgba(var(--mdui-color-surface-container), 0.5) !important;
                    }
                }
                .group-title {
                    font-size: var(--mdui-typescale-title-small-size);
                    line-height: var(--mdui-typescale-title-small-line-height);
                    letter-spacing: var(--mdui-typescale-title-small-tracking);
                    font-weight: 700;
                    margin: 0 0 0.5rem;
                }
                .grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
                .item {
                    padding: 0;
                    border-radius: 0.5rem;
                    width: 72px;
                    height: 82px;
                    position: relative;
                }
                .item-inner {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: flex-start;
                    gap: 0.25rem;
                    padding: 0.25rem;
                    box-sizing: border-box;
                }
                .icon-circle {
                    width: 40px;
                    height: 40px;
                    border-radius: 9999px;
                    background: rgba(var(--mdui-color-surface-container-high), 0.65);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .item-name {
                    font-size: var(--mdui-typescale-body-small-size);
                    line-height: var(--mdui-typescale-body-small-line-height);
                    letter-spacing: var(--mdui-typescale-body-small-tracking);
                    text-align: center;
                    color: rgba(var(--mdui-color-on-surface-variant));
                    width: 100%;
                    max-width: 72px;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                }
                .del {
                    position: absolute;
                    top: 0;
                    right: 0;
                    opacity: 0;
                    transition: opacity 0.15s;
                }
                .item:hover .del { opacity: 1; }
                @media (hover: none) { .del { opacity: 1; } }
                .add .icon-circle {
                    border: 2px dashed rgba(var(--mdui-color-outline), 0.55);
                    background: transparent;
                }
            </style>
            <mdui-card class="group" variant="filled">
                <h3 class="group-title">快速访问</h3>
                <div class="grid" id="grid"></div>
            </mdui-card>
        `;
        this._grid = this.shadowRoot.getElementById('grid');
        this._grid.addEventListener('click', (e) => {
            const addBtn = e.target.closest('[data-action="add"]');
            if (addBtn) {
                this.dispatchEvent(new CustomEvent('quick-add', { bubbles: true, composed: true }));
                return;
            }
            const delBtn = e.target.closest('[data-action="delete"]');
            if (delBtn) {
                e.stopPropagation();
                this.dispatchEvent(new CustomEvent('quick-delete', {
                    bubbles: true,
                    composed: true,
                    detail: { id: delBtn.dataset.id }
                }));
                return;
            }
            const itemBtn = e.target.closest('[data-url]');
            if (itemBtn) {
                this.dispatchEvent(new CustomEvent('quick-open', {
                    bubbles: true,
                    composed: true,
                    detail: { url: itemBtn.dataset.url }
                }));
            }
        });
    }

    setItems(items) {
        this._items = Array.isArray(items) ? items : [];
        this._render();
    }

    _favicon(url) {
        return 'https://api.ankio.net/favicon?url=' + encodeURIComponent(url);
    }

    _render() {
        if (!this._grid) {
            return;
        }
        const items = this._items.map((item) => {
            const name = $.escapeHtml(item.title || item.url || '');
            const url = $.escapeHtml(item.url || '');
            const id = $.escapeHtml(String(item.id || ''));
            const favicon = $.escapeHtml(this._favicon(item.url));
            return `<mdui-button variant="text" class="item" data-url="${url}">
                <mdui-button-icon class="del" data-action="delete" data-id="${id}" icon="close"></mdui-button-icon>
                <div class="item-inner">
                    <div class="icon-circle">
                        <image-loader style="width:22px;height:22px" src="${favicon}" alt="${name}" loading="lazy"></image-loader>
                    </div>
                    <div class="item-name">${name}</div>
                </div>
            </mdui-button>`;
        }).join('');

        const add = `<mdui-button variant="text" class="item add" data-action="add">
            <div class="item-inner">
                <div class="icon-circle"><mdui-icon name="add"></mdui-icon></div>
                <div class="item-name">添加</div>
            </div>
        </mdui-button>`;

        this._grid.innerHTML = items + add;
        $.emitter.emit('imageLoader', this.shadowRoot);
    }
}

customElements.define('proxy-quick-grid', ProxyQuickGrid);
