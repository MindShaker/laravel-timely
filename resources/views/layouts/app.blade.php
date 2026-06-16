<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/mindshaker-icon.png') }}">

        <!-- Figtree font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-content">
        <div class="min-h-screen bg-background relative">
            <div class="relative z-10 pt-3">
                @include('layouts.navigation')

                @isset($header)
                    <header class="mx-3 sm:mx-4 mt-0 bg-base border border-neutral-700 border-t-0 rounded-b-xl px-4 py-4 sm:px-6 lg:px-8 text-nav-fg">
                        {{ $header }}
                    </header>
                @endisset

                <main>
                    <div class="max-w-[1570px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
