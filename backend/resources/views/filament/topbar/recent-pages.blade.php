<div
    x-data="{
        open: false,
        pages: [],
        load() {
            let stored = [];
            try {
                stored = JSON.parse(localStorage.getItem('al_recent_pages')) || [];
            } catch (e) {
                stored = [];
            }
            this.pages = stored.filter((p) => p.url !== window.location.href);
        },
        timeAgo(ts) {
            const seconds = Math.floor((Date.now() - ts) / 1000);
            if (seconds < 60) return 'just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + 'm ago';
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h ago';
            const days = Math.floor(hours / 24);
            return days + 'd ago';
        },
    }"
    x-init="load()"
    x-on:click.outside="open = false"
    style="position:relative"
>
    <button
        x-on:click="open = ! open; if (open) load()"
        title="Recently visited"
        style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:#9ca3af;background:transparent;transition:background .2s;border:none;cursor:pointer"
        onmouseover="this.style.background='rgba(0,0,0,0.05)'"
        onmouseout="this.style.background='transparent'"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        style="position:absolute;right:0;top:44px;width:300px;max-height:380px;overflow-y:auto;background:#fff;border:1px solid rgba(0,0,0,0.08);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.12);z-index:50"
    >
        <div style="padding:12px 16px;border-bottom:1px solid rgba(0,0,0,0.06);font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em">
            Recently Visited
        </div>

        <template x-if="pages.length === 0">
            <div style="padding:20px 16px;font-size:13px;color:#9ca3af;text-align:center">
                Nothing here yet — visit a few pages first.
            </div>
        </template>

        <template x-for="page in pages" :key="page.url">
            <a
                :href="page.url"
                style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 16px;text-decoration:none;border-bottom:1px solid rgba(0,0,0,0.04);transition:background .15s"
                onmouseover="this.style.background='rgba(0,0,0,0.03)'"
                onmouseout="this.style.background='transparent'"
            >
                <span x-text="page.title" style="font-size:13px;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></span>
                <span x-text="timeAgo(page.visitedAt)" style="font-size:11px;color:#9ca3af;flex-shrink:0"></span>
            </a>
        </template>
    </div>
</div>
