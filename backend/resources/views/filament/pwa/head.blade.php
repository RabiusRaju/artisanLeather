{{-- ── Artisan Leather Admin PWA Meta Tags ── --}}

{{-- Chart.js served locally — no CDN dependency --}}
<script src="/js/chart.umd.min.js"></script>
<script>
window.AlChart = {
    _t() {
        const d = document.documentElement.classList.contains('dark');
        return { text: d ? '#9ca3af' : '#6b7280', grid: d ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' };
    },
    _x(c) { if (c && c._ci) { c._ci.destroy(); c._ci = null; } },
    _j(el, key, def) { try { return JSON.parse(el.dataset[key] || def); } catch(e) { return JSON.parse(def); } },

    revTrend(el) {
        const c = el.querySelector('canvas');
        if (!c || !window.Chart) return;
        this._x(c);
        const { text, grid } = this._t();
        const days = this._j(el, 'days', '[]');
        const rev  = this._j(el, 'rev', '[]');
        const avg  = parseFloat(el.dataset.avg || 0);
        const maxV = Math.max(...rev) || 1;
        c._ci = new Chart(c, {
            type: 'bar',
            data: {
                labels: days,
                datasets: [
                    { label: 'Revenue (OMR)', data: rev,
                      backgroundColor: rev.map(v => v >= avg ? `rgba(245,158,11,${(0.5+v/maxV*0.45).toFixed(2)})` : 'rgba(245,158,11,0.2)'),
                      borderColor: rev.map(v => v >= avg ? '#f59e0b' : 'rgba(245,158,11,0.35)'),
                      borderWidth:1, borderRadius:4, order:2 },
                    { label: 'Daily Average', data: Array(rev.length).fill(avg),
                      type:'line', borderColor:'rgba(34,197,94,0.7)', borderWidth:2,
                      borderDash:[6,4], pointRadius:0, fill:false, tension:0, order:1 }
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                interaction:{ mode:'index', intersect:false },
                plugins: {
                    legend:{ display:true, position:'top', align:'end', labels:{ color:text, font:{size:10}, boxWidth:12, padding:10, usePointStyle:true } },
                    tooltip:{ callbacks:{ label: c => c.dataset.label+': OMR '+(c.parsed.y||0).toFixed(3) } }
                },
                scales: {
                    x:{ ticks:{ color:text, font:{size:9}, maxTicksLimit:10 }, grid:{display:false} },
                    y:{ ticks:{ color:text, font:{size:10}, callback: v=>'OMR '+v.toFixed(0) }, grid:{color:grid}, beginAtZero:true }
                }
            }
        });
    },

    _donut(el, fmtFn) {
        const c = el.querySelector('canvas');
        if (!c || !window.Chart) return;
        this._x(c);
        const labels = this._j(el, 'labels', '[]');
        const values = this._j(el, 'values', '[]');
        const colors = this._j(el, 'colors', '[]');
        c._ci = new Chart(c, {
            type: 'doughnut',
            data: { labels, datasets: [{ data:values, backgroundColor:colors, borderWidth:0, hoverOffset:5 }] },
            options: { responsive:true, maintainAspectRatio:false, cutout:'68%',
                plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: fmtFn||(c=>c.label+': '+c.parsed) } } }
            }
        });
    },
    revSources(el) { this._donut(el, c=>c.label+': OMR '+c.parsed.toFixed(3)); },
    orderStatus(el) { this._donut(el); },
    payMethods(el)  { this._donut(el, c=>c.label+': OMR '+c.parsed.toFixed(3)); },
    collections(el) { this._donut(el, c=>c.label+': OMR '+c.parsed.toFixed(3)); },

    topProducts(el) {
        const c = el.querySelector('canvas');
        if (!c || !window.Chart) return;
        this._x(c);
        const { text, grid } = this._t();
        const labels = this._j(el, 'labels', '[]');
        const values = this._j(el, 'values', '[]');
        const maxV = Math.max(...values) || 1;
        c._ci = new Chart(c, {
            type:'bar',
            data:{ labels, datasets:[{ label:'Revenue (OMR)', data:values,
                backgroundColor:values.map(v=>`rgba(245,158,11,${(0.4+v/maxV*0.55).toFixed(2)})`),
                borderColor:'#f59e0b', borderWidth:1.5,
                borderRadius:{topRight:6,bottomRight:6}, borderSkipped:'left' }] },
            options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}} },
                scales:{
                    x:{ ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)}, grid:{color:grid}, beginAtZero:true },
                    y:{ ticks:{color:text,font:{size:11}}, grid:{display:false} }
                }
            }
        });
    },

    weeklyTrend(el) {
        const c = el.querySelector('canvas');
        if (!c || !window.Chart) return;
        this._x(c);
        const { text, grid } = this._t();
        const labels = this._j(el, 'labels', '[]');
        const values = this._j(el, 'values', '[]');
        c._ci = new Chart(c, {
            type:'bar',
            data:{ labels, datasets:[{ data:values,
                backgroundColor:['rgba(245,158,11,0.4)','rgba(245,158,11,0.55)','rgba(245,158,11,0.7)','rgba(245,158,11,0.9)'],
                borderColor:'#f59e0b', borderWidth:1.5, borderRadius:5 }] },
            options:{ responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}} },
                scales:{
                    x:{ ticks:{color:text,font:{size:10}}, grid:{display:false} },
                    y:{ ticks:{color:text,font:{size:9},callback:v=>'OMR '+v.toFixed(0)}, grid:{color:grid}, beginAtZero:true }
                }
            }
        });
    },

    _run(el) {
        const fn = el.dataset && el.dataset.alchart;
        if (fn && this[fn] && !el._alDone) {
            el._alDone = true;
            this[fn](el);
        }
    },
    scanAll() {
        document.querySelectorAll('[data-alchart]').forEach(el => this._run(el));
    }
};

// MutationObserver — fires the moment any chart div enters the DOM
(function() {
    const obs = new MutationObserver(mutations => {
        for (const m of mutations) {
            for (const node of m.addedNodes) {
                if (node.nodeType !== 1) continue;
                if (node.dataset && node.dataset.alchart) window.AlChart._run(node);
                node.querySelectorAll && node.querySelectorAll('[data-alchart]')
                    .forEach(el => window.AlChart._run(el));
            }
        }
    });
    obs.observe(document.documentElement, { childList: true, subtree: true });

    // Fallback: also scan once the page is interactive
    document.addEventListener('DOMContentLoaded', () => window.AlChart.scanAll());
})();
</script>

{{-- Web App Manifest --}}
<link rel="manifest" href="/manifest.json">

{{-- Theme colour (address bar on Android) --}}
<meta name="theme-color" content="#C9A84C">
<meta name="msapplication-TileColor" content="#120D05">
<meta name="msapplication-TileImage" content="/icons/icon-192.png">

{{-- Apple PWA --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="AL Admin">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Apple Splash Screens (iPhone / iPad) --}}
{{-- iPhone SE --}}
<link rel="apple-touch-startup-image"
      media="(device-width:375px) and (device-height:667px) and (-webkit-device-pixel-ratio:2)"
      href="/icons/splash-750x1334.png">
{{-- iPhone 14 Pro / 15 --}}
<link rel="apple-touch-startup-image"
      media="(device-width:393px) and (device-height:852px) and (-webkit-device-pixel-ratio:3)"
      href="/icons/splash-1179x2556.png">
{{-- iPad Pro --}}
<link rel="apple-touch-startup-image"
      media="(device-width:1024px) and (device-height:1366px) and (-webkit-device-pixel-ratio:2)"
      href="/icons/splash-2048x2732.png">

{{-- Favicon --}}
<link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-192.png">

{{-- Service Worker registration --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/pwa-sw.js', { scope: '/admin' })
            .then(reg => {
                console.log('[PWA] Service worker registered:', reg.scope);
            })
            .catch(err => {
                console.warn('[PWA] SW registration failed:', err);
            });
    });
}

// PWA Install prompt — capture beforeinstallprompt
window.__pwaInstallPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    window.__pwaInstallPrompt = e;
    // Show install button
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
        btn.style.display = 'flex';
        btn.onclick = async () => {
            if (!window.__pwaInstallPrompt) return;
            window.__pwaInstallPrompt.prompt();
            const { outcome } = await window.__pwaInstallPrompt.userChoice;
            if (outcome === 'accepted') btn.style.display = 'none';
            window.__pwaInstallPrompt = null;
        };
    }
});

// Hide install button if already installed
window.addEventListener('appinstalled', () => {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) btn.style.display = 'none';
    window.__pwaInstallPrompt = null;
});
</script>
