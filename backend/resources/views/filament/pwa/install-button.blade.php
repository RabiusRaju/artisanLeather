{{-- ── PWA Install Floating Button ── --}}
<div id="pwa-install-btn"
     style="display:none;position:fixed;bottom:90px;right:20px;z-index:9999;
            background:#C9A84C;color:#120D05;border:none;padding:12px 18px;
            font-family:system-ui,sans-serif;font-size:12px;font-weight:700;
            letter-spacing:.15em;text-transform:uppercase;cursor:pointer;
            box-shadow:0 4px 20px rgba(201,168,76,0.4);
            align-items:center;gap:8px;border-radius:2px;
            transition:all .2s ease;">
    <span style="font-size:18px;">📲</span>
    <span>Install App</span>
</div>

<style>
#pwa-install-btn:hover {
    background: #d4af37 !important;
    box-shadow: 0 6px 28px rgba(201,168,76,0.55) !important;
    transform: translateY(-2px);
}
/* Hide when in standalone mode (already installed) */
@media (display-mode: standalone) {
    #pwa-install-btn { display: none !important; }
}
</style>
