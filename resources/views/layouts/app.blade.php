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
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            <livewire:layout.navigation />

            <div class="lg:pl-72">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="px-6 pt-6 lg:px-8">
                        <div class="app-shell-panel mx-auto max-w-7xl px-6 py-5">
                            <div class="flex items-center justify-between gap-6">
                                <div class="min-w-0 flex-1">
                                    {{ $header }}
                                </div>
                                <div class="hidden items-center gap-3 xl:flex">
                                    @can('products.view')
                                        <form method="GET" action="{{ route('products.index') }}" class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                                                </svg>
                                            </span>
                                            <input name="search" type="search" class="w-64 rounded-full border-slate-200 bg-slate-50/80 py-2 pl-9 pr-4 text-sm shadow-none focus:bg-white" placeholder="Recherche produit">
                                        </form>
                                    @endcan
                                    <a href="{{ route('profile') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-950 text-sm font-semibold text-white shadow-sm shadow-slate-900/20" wire:navigate>
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </header>
                @endif

                @if (session('success'))
                    <div class="mx-auto mt-4 max-w-7xl px-6 lg:px-8">
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm shadow-emerald-900/5">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="pb-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
