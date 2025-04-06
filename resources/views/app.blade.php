<!DOCTYPE html>
{{--
bg-red-400 stroke-red-400 border-orange-400
bg-orange-400 stroke-orange-400 border-yellow-400
bg-yellow-400 stroke-yellow-400 border-lime-400
bg-lime-400 stroke-lime-400 border-green-400
bg-green-400 stroke-green-400 border-emerald-400
bg-emerald-400 stroke-emerald-400 border-cyan-400
bg-cyan-400 stroke-cyan-400 border-sky-400
bg-sky-400 stroke-sky-400 border-blue-400
bg-blue-400 stroke-blue-400 border-indigo-400
bg-indigo-400 stroke-indigo-400 border-purple-400
bg-purple-400 stroke-purple-400 border-fuchsia-400
bg-fuchsia-400 stroke-fuchsia-400
bg-info stroke-info border-info
bg-success stroke-success border-success
bg-warning stroke-warning border-warning
bg-error stroke-error border-error
--}}
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
        
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-background">
        @inertia
    </body>
</html>
