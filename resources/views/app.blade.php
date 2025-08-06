@php use MagicTest\MagicTest\MagicTest; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
        
        <script>
            localStorage.setItem('ui-theme', '{{Auth::user()?->theme}}');
        </script>
        
        <link rel="icon" href="{{asset('images/favicon.ico')}}" sizes="32x32">
        <link rel="icon" href="{{asset('images/icon.svg')}}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{asset('images/apple-touch-icon.png')}}">
        
        <meta name="author" content="Beacon HQ">
        
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Beacon — Feature Flag Management for Laravel">
        <meta property="og:description" content="Manage your applications Feature Flags with Beacon, a centralized management platform for Laravel and Laravel Pennant with Gradual Rollout and A/B Testing">
        <meta property="og:image" content="{{asset('images/social.png')}}">
        
        <meta name="twitter:card" content="summary_large_image">
        <meta property="twitter:domain" content="{{ parse_url(config('app.url'), PHP_URL_HOST)  }}">
        <meta property="twitter:url" content="{{ config('app.url') }}">
        <meta name="twitter:title" content="Beacon — Feature Flag Management for Laravel">
        <meta name="twitter:description" content="Manage your applications Feature Flags with Beacon, a centralized management platform for Laravel and Laravel Pennant with Gradual Rollout and A/B Testing">
        <meta name="twitter:image" content="{{asset('images/social.png')}}">
        
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
        
        @if (str_contains(request()->userAgent(), 'Laravel/Dusk'))
            <style type="text/css">
                * {
                    overflow: auto; /* Required for scrolling */
                    -webkit-scrollbar { /* For Chrome, Safari, and Opera */
                        display: none;
                    }
                    -ms-overflow-style: none;  /* For Internet Explorer and Edge */
                    scrollbar-width: none;  /* For Firefox */
                }
            </style>
        @endif
    </head>
    <body data-dusk="body" class="font-sans antialiased bg-background">
        @inertia
    </body>
</html>
