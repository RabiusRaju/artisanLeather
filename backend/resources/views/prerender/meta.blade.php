<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>{{ $title }} | Artisan Leather</title>
    <meta name="description" content="{{ $description }}" />
    <link rel="canonical" href="{{ $url }}" />

    @if (!empty($schema))
    <script type="application/ld+json">{!! $schema !!}</script>
    @endif

    <meta property="og:type" content="{{ $type }}" />
    <meta property="og:site_name" content="Artisan Leather" />
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ $description }}" />
    @if (!empty($image))
        <meta property="og:image" content="{{ $image }}" />
    @endif
    <meta property="og:url" content="{{ $url }}" />
    <meta property="og:locale" content="en_OM" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="{{ $description }}" />
    @if (!empty($image))
        <meta name="twitter:image" content="{{ $image }}" />
    @endif
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    @if (!empty($bodyContent))
    <div>{{ $bodyContent }}</div>
    @endif
    <p><a href="{{ $url }}">View on Artisan Leather →</a></p>
</body>
</html>
