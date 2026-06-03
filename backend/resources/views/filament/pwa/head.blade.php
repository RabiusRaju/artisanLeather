{{-- ── Artisan Leather Admin PWA Meta Tags ── --}}

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
