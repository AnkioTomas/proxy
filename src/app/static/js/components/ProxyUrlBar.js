class ProxyUrlBar extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._debugOn = false;
    }

    connectedCallback() {
        this.shadowRoot.innerHTML = `
            <style>
                :host { display: block; }
                .bar {
                    background: rgba(var(--mdui-color-surface-container), 0.72) !important;
                    border: 1px solid rgba(var(--mdui-color-outline-variant), 0.45);
                    backdrop-filter: blur(16px) saturate(140%);
                    -webkit-backdrop-filter: blur(16px) saturate(140%);
                    box-shadow: var(--mdui-elevation-level1);
                    border-radius: 9999px;
                    padding: 4px 8px;
                    width: 100%;
                }
                :host-context(.mdui-theme-dark) .bar {
                    background: rgba(var(--mdui-color-surface-container), 0.5) !important;
                }
                @media (prefers-color-scheme: dark) {
                    :host-context(.mdui-theme-auto) .bar {
                        background: rgba(var(--mdui-color-surface-container), 0.5) !important;
                    }
                }
                .bar-inner {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin: 0 8px;
                }
                .input-wrap { flex: 1; min-width: 0; }
                mdui-text-field::part(container) {
                    border: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                }
                #debug { flex-shrink: 0; }
                .brand-icon {
                    width: 28px;
                    height: 28px;
                    border-radius: 8px;
                    flex-shrink: 0;
                    object-fit: cover;
                }
            </style>
            <mdui-card class="bar" variant="filled">
                <div class="bar-inner">
                    <img class="brand-icon" src="/static/icons/favicon-32x32.png" alt="" width="28" height="28">
                    <div class="input-wrap">
                        <mdui-text-field id="input" variant="outlined" type="url" inputmode="url" placeholder="https://example.com/path" clearable></mdui-text-field>
                    </div>
                    <mdui-button-icon id="debug" icon="bug_report" title="调试"></mdui-button-icon>
                    <mdui-button id="go" variant="filled" end-icon="arrow_forward">Go</mdui-button>
                </div>
            </mdui-card>
        `;

        this._input = this.shadowRoot.getElementById('input');
        this._go = this.shadowRoot.getElementById('go');
        this._debug = this.shadowRoot.getElementById('debug');

        this._go.addEventListener('click', () => this._submit());
        this._debug.addEventListener('click', () => {
            this.debug = !this.debug;
        });
        this._input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this._submit();
            }
        });

        this._syncDebugUi();
    }

    get value() {
        return this._input ? this._input.value : '';
    }

    set value(v) {
        if (this._input) {
            this._input.value = v;
        }
    }

    get debug() {
        return this._debugOn;
    }

    set debug(on) {
        this._debugOn = !!on;
        this._syncDebugUi();
    }

    _syncDebugUi() {
        if (!this._debug) {
            return;
        }
        if (this._debugOn) {
            this._debug.setAttribute('selected', '');
            this._debug.variant = 'filled-tonal';
        } else {
            this._debug.removeAttribute('selected');
            this._debug.variant = 'standard';
        }
    }

    _submit() {
        this.dispatchEvent(new CustomEvent('proxy-go', {
            bubbles: true,
            composed: true,
            detail: {
                url: this.value,
                debug: this.debug
            }
        }));
    }
}

customElements.define('proxy-url-bar', ProxyUrlBar);
