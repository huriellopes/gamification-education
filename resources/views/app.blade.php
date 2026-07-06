<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="color-scheme: dark;">
    <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            @php
                // SEO rendered server-side so crawlers/social bots that don't
                // execute JS (WhatsApp, Facebook, LinkedIn, Googlebot) always see
                // it. Pages may override via a `seo` Inertia prop; otherwise the
                // brand defaults below apply.
                $pageSeo = data_get($page, 'props.seo', []);
                $seoTitle = $pageSeo['title'] ?? config('app.name', 'GamificaEdu') . ' — Aprenda Jogando e Conquiste o Topo';
                $seoDescription = $pageSeo['description'] ?? __('misc.seo_description');
                $seoImage = $pageSeo['image'] ?? url('/images/og-image.png');
            @endphp

            <title inertia>{{ $seoTitle }}</title>

            <!-- Favicons -->
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
            <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
            <link rel="manifest" href="/site.webmanifest">
            <meta name="theme-color" content="#6366f1">

            <!-- SEO & Canonical -->
            <meta name="description" content="{{ $seoDescription }}">
            <link rel="canonical" href="{{ url()->current() }}">

            <!-- Open Graph -->
            <meta property="og:site_name" content="{{ config('app.name', 'GamificaEdu') }}">
            <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
            <meta property="og:type" content="website">
            <meta property="og:title" content="{{ $seoTitle }}">
            <meta property="og:description" content="{{ $seoDescription }}">
            <meta property="og:url" content="{{ url()->current() }}">
            <meta property="og:image" content="{{ $seoImage }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">

            <!-- Twitter -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $seoTitle }}">
            <meta name="twitter:description" content="{{ $seoDescription }}">
            <meta name="twitter:image" content="{{ $seoImage }}">

            <!-- Structured data -->
            <script type="application/ld+json">
                {!! json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'EducationalApplication',
                    'name' => config('app.name', 'GamificaEdu'),
                    'operatingSystem' => 'All',
                    'applicationCategory' => 'EducationalApplication',
                    'description' => $seoDescription,
                    'url' => url('/'),
                    'offers' => ['@type' => 'Offer', 'price' => '0.00', 'priceCurrency' => 'BRL'],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
            </script>

            <!-- Fonts -->
            <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <x-seo::meta />

            <!-- Scripts -->
            @routes
            @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
            @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
