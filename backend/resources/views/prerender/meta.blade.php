<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>{{ $title }} | Artisan Leather</title>
    <meta name="description" content="{{ $description }}" />
    <link rel="canonical" href="{{ $url }}" />

    <meta property="og:type" content="{{ $type }}" />
    <meta property="og:site_name" content="Artisan Leather" />
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ $description }}" />
    @if ($image)
        <meta property="og:image" content="{{ $image }}" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
    @endif
    <meta property="og:url" content="{{ $url }}" />
    <meta property="og:locale" content="en_OM" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="{{ $description }}" />
    @if ($image)
        <meta name="twitter:image" content="{{ $image }}" />
    @endif

    {{-- For the rare human visitor (or a bot UA that also follows redirects), send them straight to the real page --}}
    <meta http-equiv="refresh" content="0; url={{ $url }}" />
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <p><a href="{{ $url }}">View on Artisan Leather →</a></p>
</body>
</html>
