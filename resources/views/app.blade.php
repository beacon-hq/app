<!DOCTYPE html>
{{--
<span class="bg-red-400"></span>
<span class="bg-orange-400"></span>
<span class="bg-yellow-400"></span>
<span class="bg-lime-400"></span>
<span class="bg-green-400"></span>
<span class="bg-emerald-400"></span>
<span class="bg-cyan-400"></span>
<span class="bg-sky-400"></span>
<span class="bg-blue-400"></span>
<span class="bg-indigo-400"></span>
<span class="bg-purple-400"></span>
<span class="bg-fuchsia-400"></span>
<span class="stroke-red-400"></span>
<span class="stroke-orange-400"></span>
<span class="stroke-yellow-400"></span>
<span class="stroke-lime-400"></span>
<span class="stroke-green-400"></span>
<span class="stroke-emerald-400"></span>
<span class="stroke-cyan-400"></span>
<span class="stroke-sky-400"></span>
<span class="stroke-blue-400"></span>
<span class="stroke-indigo-400"></span>
<span class="stroke-purple-400"></span>
<span class="stroke-fuchsia-400"></span>
<span class="border-orange-400"></span>
<span class="border-yellow-400"></span>
<span class="border-lime-400"></span>
<span class="border-green-400"></span>
<span class="border-emerald-400"></span>
<span class="border-cyan-400"></span>
<span class="border-sky-400"></span>
<span class="border-blue-400"></span>
<span class="border-indigo-400"></span>
<span class="border-purple-400"></span>
<span class="border-fuchsia-400"></span>
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
            console.log('{{asset('assets/images/favicon.ico')}}');
            console.log('{{public_path('assets/images/favicon.ico')}}');
        </script>
        
        <link rel="icon" href="{{asset('images/favicon.ico')}}" sizes="32x32">
        <link rel="icon" href="{{asset('images/icon.svg')}}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{asset('images/apple-touch-icon.png')}}">
        
        <!-- Scripts -->
        <script>
            // On page load or when changing themes, best to add inline in `head` to avoid FOUC
            document.documentElement.classList.toggle(
                "dark",
                localStorage.theme === "dark" ||
                (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches),
            );
        </script>
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-background">
        @inertia
    </body>
</html>
