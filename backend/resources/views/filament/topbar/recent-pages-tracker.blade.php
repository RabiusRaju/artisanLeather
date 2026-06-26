<script>
(function () {
    var STORAGE_KEY = 'al_recent_pages';
    var MAX_ITEMS = 7;

    function recordVisit() {
        // Skip the login screen — not a "module" worth remembering.
        if (location.pathname.endsWith('/login')) return;

        var title = document.title.replace(/\s*[-—]\s*Artisan Leather.*$/, '').trim() || location.pathname;

        var entry = {
            url: location.href,
            title: title,
            visitedAt: Date.now(),
        };

        var pages = [];
        try {
            pages = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        } catch (e) {
            pages = [];
        }

        // Move this URL to the front instead of duplicating it.
        pages = pages.filter(function (p) { return p.url !== entry.url; });
        pages.unshift(entry);
        pages = pages.slice(0, MAX_ITEMS);

        localStorage.setItem(STORAGE_KEY, JSON.stringify(pages));
    }

    // Small delay so document.title (set by Filament/Livewire) has settled.
    function recordVisitSoon() {
        setTimeout(recordVisit, 150);
    }

    recordVisitSoon();
    document.addEventListener('livewire:navigated', recordVisitSoon);
})();
</script>
