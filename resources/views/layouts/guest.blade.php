<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex items-center gap-3" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 text-sm font-bold text-white shadow-lg shadow-blue-900/20">SF</span>
                    <span class="text-left">
                        <span class="block text-lg font-semibold text-slate-950">StockFlow</span>
                        <span class="block text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Pilotage stock</span>
                    </span>
                </a>
            </div>

            <div class="app-panel w-full max-w-md overflow-hidden px-6 py-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
