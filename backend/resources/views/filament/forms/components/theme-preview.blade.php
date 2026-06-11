@php
    $theme = \App\Support\ThemePresets::get($get('theme.default')) ?? \App\Support\ThemePresets::all()['warm-leather'];
    $textColor = $theme['is_light'] ? '#3A2E1E' : '#F5EDD8';
    $mutedColor = $theme['is_light'] ? '#8A7A60' : '#C8BAA0';
@endphp

<div style="border:1px solid rgba(0,0,0,0.08); border-radius:0.5rem; overflow:hidden; max-width:480px;">
    {{-- Fake browser bar --}}
    <div style="display:flex; align-items:center; gap:6px; padding:8px 12px; background:#e5e5e5;">
        <span style="width:10px; height:10px; border-radius:50%; background:#ff5f57; display:inline-block;"></span>
        <span style="width:10px; height:10px; border-radius:50%; background:#febc2e; display:inline-block;"></span>
        <span style="width:10px; height:10px; border-radius:50%; background:#28c840; display:inline-block;"></span>
        <span style="margin-left:8px; font-size:11px; color:#777;">artisanleatherom.com</span>
    </div>

    {{-- Mock page --}}
    <div style="background:{{ $theme['bg'] }}; padding:24px; font-family:Georgia, serif;">
        {{-- Nav --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <span style="color:{{ $theme['accent'] }}; font-weight:600; letter-spacing:2px; font-size:13px;">ARTISAN LEATHER</span>
            <span style="color:{{ $mutedColor }}; font-size:11px;">{{ $theme['emoji'] }} {{ $theme['name'] }}</span>
        </div>

        {{-- Hero --}}
        <div style="background:{{ $theme['bg_card'] }}; border:1px solid {{ $theme['bg_hover'] }}; border-radius:6px; padding:20px; text-align:center;">
            <p style="color:{{ $mutedColor }}; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0 0 8px;">
                Muscat · Sultanate of Oman
            </p>
            <h2 style="color:{{ $textColor }}; font-size:22px; font-weight:300; margin:0 0 6px;">
                Where Leather <span style="color:{{ $theme['accent'] }};">Becomes Legacy</span>
            </h2>
            <p style="color:{{ $mutedColor }}; font-size:12px; margin:0 0 16px;">
                Handcrafted premium leather goods, made in Oman.
            </p>
            <span style="display:inline-block; background:{{ $theme['accent'] }}; color:{{ $theme['bg'] }}; font-size:11px; font-weight:600; letter-spacing:1px; padding:8px 18px; border-radius:2px;">
                Explore Collection
            </span>
        </div>
    </div>
</div>
