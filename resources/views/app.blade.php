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

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-background">
        @inertia
    </body>
</html>
