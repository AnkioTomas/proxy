<title id="title">{$title}</title>
<style id="style">
    body.homepage-wallpaper {
        position: relative;
        min-height: 100vh;
        background-image:
            linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
            var(--homepage-wallpaper-url, none);
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
</style>

<div id="container" class="container p-3">
    <div class="max-w-lg mx-auto">
        <div id="homepage-hero" style="padding:8px 0;margin-top:12vh;">
            <div class="mx-auto w-full max-w-sm px-2 text-center" style="box-sizing:border-box;">
                <a href="/" class="d-inline-flex flex-col items-center gap-2 mb-4" style="text-decoration:none;color:#fff;">
                    <img src="/static/icons/android-chrome-192x192.png?v={$__v}" alt="{$title}" width="80" height="80" style="border-radius:20px;box-shadow:0 8px 24px rgba(0,0,0,.25);">
                    <span style="font-size:1.35rem;font-weight:700;letter-spacing:.02em;text-shadow:0 1px 8px rgba(0,0,0,.35);">{$title}</span>
                </a>
                <proxy-url-bar id="proxy-url-bar"></proxy-url-bar>
            </div>
        </div>

        <section class="mb-3 w-full max-w-sm mt-4 px-2 mx-auto" style="box-sizing:border-box;">
            <proxy-quick-grid id="proxy-quick-grid"></proxy-quick-grid>
        </section>
    </div>
</div>

<script id="script" src="/static/js/index/main/index.js?v={$__v}"></script>
