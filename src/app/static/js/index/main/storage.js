const DEFAULT_THEME = {
    wallpaper: 'https://api.ankio.net/bing'
};

class StorageManager {
    constructor(key = 'proxy_portal_settings') {
        this.key = key;
        try {
            this.data = JSON.parse(localStorage.getItem(this.key)) || { theme: DEFAULT_THEME };
        } catch (e) {
            this.data = { theme: DEFAULT_THEME };
        }
        if (!this.data.theme) {
            this.data.theme = DEFAULT_THEME;
        }
    }

    save() {
        localStorage.setItem(this.key, JSON.stringify(this.data));
    }

    update(patch) {
        this.data = { ...this.data, ...patch };
        this.save();
        return this.data;
    }

    get(key) {
        return this.data[key];
    }
}

const storage = new StorageManager();
