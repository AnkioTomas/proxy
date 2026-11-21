class ProxySettings extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
        this._build();
    }

    _build() {
        const iconBtn = this.getAttribute('iconBtn') === 'true';
        this.shadowRoot.innerHTML = `
            <style>
                .overlay {
                    position: fixed; inset: 0; background: rgba(0,0,0,.35);
                    opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1200;
                }
                .overlay.open { opacity: 1; pointer-events: auto; }
                .panel {
                    position: fixed; top: 0; right: 0; width: min(22rem, 92vw); height: 100%;
                    background: rgb(var(--mdui-color-surface));
                    transform: translateX(100%); transition: transform .25s; z-index: 1201;
                    display: flex; flex-direction: column;
                }
                .panel.open { transform: translateX(0); }
                .panel-head {
                    display: flex; align-items: center; justify-content: space-between;
                    padding: 1rem 1rem 0.5rem;
                }
                .panel-title { font-weight: 700; }
                .panel-body { padding: 0 1rem 1rem; overflow: auto; }
                .wallpaper-preview {
                    width: 100%; aspect-ratio: 16/9; object-fit: cover;
                    border-radius: var(--mdui-shape-corner-medium);
                    margin-bottom: 0.75rem;
                }
            </style>
            ${iconBtn ? '<mdui-button-icon id="open" icon="settings"></mdui-button-icon>' : '<mdui-fab id="open" icon="settings"></mdui-fab>'}
            <div class="overlay" id="overlay"></div>
            <aside class="panel" id="panel">
                <div class="panel-head">
                    <div class="panel-title">设置</div>
                    <mdui-button-icon id="close" icon="close"></mdui-button-icon>
                </div>
                <div class="panel-body">
                    <div class="title-medium mb-2">背景图片</div>
                    <img id="preview" class="wallpaper-preview" alt="wallpaper" />
                    <mdui-text-field id="wallpaper" label="壁纸 URL" variant="outlined" clearable></mdui-text-field>
                    <mdui-button id="save" class="mt-3" variant="filled">保存</mdui-button>
                </div>
            </aside>
        `;

        const open = () => {
            const theme = storage.get('theme') || {};
            this.shadowRoot.getElementById('wallpaper').value = theme.wallpaper || '';
            this.shadowRoot.getElementById('preview').src = theme.wallpaper || '';
            this.shadowRoot.getElementById('overlay').classList.add('open');
            this.shadowRoot.getElementById('panel').classList.add('open');
        };
        const close = () => {
            this.shadowRoot.getElementById('overlay').classList.remove('open');
            this.shadowRoot.getElementById('panel').classList.remove('open');
        };

        this.shadowRoot.getElementById('open').addEventListener('click', open);
        this.shadowRoot.getElementById('close').addEventListener('click', close);
        this.shadowRoot.getElementById('overlay').addEventListener('click', close);
        this.shadowRoot.getElementById('wallpaper').addEventListener('input', (e) => {
            this.shadowRoot.getElementById('preview').src = e.target.value || '';
        });
        this.shadowRoot.getElementById('save').addEventListener('click', () => {
            const wallpaper = this.shadowRoot.getElementById('wallpaper').value.trim();
            storage.update({ theme: { wallpaper: wallpaper } });
            $.emitter.emit('proxy:settings:updated');
            close();
        });
    }
}

customElements.define('proxy-settings', ProxySettings);
