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
        <div id="homepage-hero" style="padding:8px 0;margin-top:15vh;">
            <div class="mx-auto w-full max-w-sm px-2" style="box-sizing:border-box;">
                <proxy-url-bar id="proxy-url-bar"></proxy-url-bar>
            </div>
        </div>

        <section class="mb-3 w-full max-w-sm mt-4 px-2 mx-auto" style="box-sizing:border-box;">
            <proxy-quick-grid id="proxy-quick-grid"></proxy-quick-grid>
        </section>
    </div>
</div>

<script id="script" src="/static/js/index/main/index.js?v={$__v}"></script>
