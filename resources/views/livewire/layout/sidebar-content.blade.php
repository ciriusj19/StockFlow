@php
    $closeOnNavigate = $closeOnNavigate ?? false;
    $pilotageActive = request()->routeIs('dashboard', 'alerts.*', 'forecasts.*', 'reports.*');
    $decisionnelActive = request()->routeIs('analytics.*');
    $stockActive = request()->routeIs('products.*', 'inventories.*');
    $referentielActive = request()->routeIs('categories.*', 'suppliers.*');
    $administrationActive = request()->routeIs('users.*', 'roles.*');
@endphp

<div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-700/60 px-5">
    <a href="{{ $homeHref }}" class="flex items-center gap-3" x-on:click="open = false" wire:navigate>
        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-950/25 ring-1 ring-white/10">SF</span>
        <span>
            <span class="block text-base font-semibold leading-5 text-white">StockFlow</span>
            <span class="block text-xs font-medium text-slate-400">Gestion intelligente</span>
        </span>
    </a>

    @if ($closeOnNavigate)
        <button
            type="button"
            x-on:click="open = false"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-300 hover:bg-white/10 hover:text-white focus:bg-white/10 focus:outline-none lg:hidden"
            aria-label="Fermer la navigation"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    @endif
</div>

<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex-1 overflow-y-auto px-4 py-5">
        <div class="space-y-3">
            @if (auth()->user()->can('forecasts.view') || auth()->user()->can('alerts.view') || auth()->user()->can('reports.view'))
                <section x-data="{ expanded: @js($pilotageActive) }" class="rounded-lg">
                    <button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-semibold text-slate-100 hover:bg-white/5 focus:bg-white/5 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-100 ring-1 ring-inset ring-blue-300/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M8 17V9M12 17V7M16 17v-4M20 17V4" />
                            </svg>
                        </span>
                        <span>Pilotage</span>
                        <svg class="ms-auto h-4 w-4 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div x-show="expanded" x-transition @if (! $pilotageActive) style="display: none;" @endif>
                        <div class="mt-2 space-y-1 border-l border-white/10 pl-3">
                            @can('forecasts.view')
                                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" x-on:click="open = false" wire:navigate>
                                    Tableau de bord
                                </x-sidebar-link>
                            @endcan
                            @can('alerts.view')
                                <x-sidebar-link :href="route('alerts.index')" :active="request()->routeIs('alerts.*')" x-on:click="open = false" wire:navigate>
                                    Alertes
                                </x-sidebar-link>
                            @endcan
                            @can('forecasts.view')
                                <x-sidebar-link :href="route('forecasts.index')" :active="request()->routeIs('forecasts.*')" x-on:click="open = false" wire:navigate>
                                    Prévisions
                                </x-sidebar-link>
                            @endcan
                            @can('reports.view')
                                <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" x-on:click="open = false" wire:navigate>
                                    Rapports
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>
                </section>
            @endif

            @if (auth()->user()->can('analytics.view'))
                <section x-data="{ expanded: @js($decisionnelActive) }" class="rounded-lg">
                    <button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-semibold text-slate-100 hover:bg-white/5 focus:bg-white/5 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-100 ring-1 ring-inset ring-blue-300/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 18h16" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 14l3-3 3 2 4-5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 20h12" />
                            </svg>
                        </span>
                        <span>Décisionnel</span>
                        <svg class="ms-auto h-4 w-4 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div x-show="expanded" x-transition @if (! $decisionnelActive) style="display: none;" @endif>
                        <div class="mt-2 space-y-1 border-l border-white/10 pl-3">
                            <x-sidebar-link :href="route('analytics.index')" :active="request()->routeIs('analytics.*')" x-on:click="open = false" wire:navigate>
                                Synthèse décisionnelle
                            </x-sidebar-link>
                        </div>
                    </div>
                </section>
            @endif

            @if (auth()->user()->can('products.view') || auth()->user()->can('inventories.view'))
                <section x-data="{ expanded: @js($stockActive) }" class="rounded-lg">
                    <button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-semibold text-slate-100 hover:bg-white/5 focus:bg-white/5 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-100 ring-1 ring-inset ring-blue-300/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8l8-4 8 4-8 4-8-4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12l8 4 8-4M4 16l8 4 8-4" />
                            </svg>
                        </span>
                        <span>Stock</span>
                        <svg class="ms-auto h-4 w-4 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div x-show="expanded" x-transition @if (! $stockActive) style="display: none;" @endif>
                        <div class="mt-2 space-y-1 border-l border-white/10 pl-3">
                            @can('products.view')
                                <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" x-on:click="open = false" wire:navigate>
                                    Produits
                                </x-sidebar-link>
                            @endcan
                            @can('inventories.view')
                                <x-sidebar-link :href="route('inventories.index')" :active="request()->routeIs('inventories.*')" x-on:click="open = false" wire:navigate>
                                    Inventaires
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>
                </section>
            @endif

            @if (auth()->user()->can('categories.view') || auth()->user()->can('suppliers.view'))
                <section x-data="{ expanded: @js($referentielActive) }" class="rounded-lg">
                    <button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-semibold text-slate-100 hover:bg-white/5 focus:bg-white/5 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-100 ring-1 ring-inset ring-blue-300/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 5h12M6 12h12M6 19h12" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h.01M4 12h.01M4 19h.01" />
                            </svg>
                        </span>
                        <span>Référentiel</span>
                        <svg class="ms-auto h-4 w-4 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div x-show="expanded" x-transition @if (! $referentielActive) style="display: none;" @endif>
                        <div class="mt-2 space-y-1 border-l border-white/10 pl-3">
                            @can('categories.view')
                                <x-sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" x-on:click="open = false" wire:navigate>
                                    Catégories
                                </x-sidebar-link>
                            @endcan
                            @can('suppliers.view')
                                <x-sidebar-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')" x-on:click="open = false" wire:navigate>
                                    Fournisseurs
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>
                </section>
            @endif

            @if (auth()->user()->can('users.view') || auth()->user()->can('roles.view'))
                <section x-data="{ expanded: @js($administrationActive) }" class="rounded-lg">
                    <button type="button" x-on:click="expanded = ! expanded" x-bind:aria-expanded="expanded.toString()" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-semibold text-slate-100 hover:bg-white/5 focus:bg-white/5 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-100 ring-1 ring-inset ring-blue-300/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-5" />
                            </svg>
                        </span>
                        <span>Administration</span>
                        <svg class="ms-auto h-4 w-4 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div x-show="expanded" x-transition @if (! $administrationActive) style="display: none;" @endif>
                        <div class="mt-2 space-y-1 border-l border-white/10 pl-3">
                            @can('users.view')
                                <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')" x-on:click="open = false" wire:navigate>
                                    Utilisateurs
                                </x-sidebar-link>
                            @endcan
                            @can('roles.view')
                                <x-sidebar-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" x-on:click="open = false" wire:navigate>
                                    Rôles
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-700/60 p-4">
        <div class="rounded-2xl bg-white/[0.06] p-3 ring-1 ring-inset ring-white/10">
            <div class="text-sm font-semibold text-white" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
            <div class="mt-0.5 truncate text-xs text-slate-400">{{ auth()->user()->email }}</div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <a href="{{ route('profile') }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-center text-sm font-semibold text-slate-200 hover:bg-white/10 hover:text-white" x-on:click="open = false" wire:navigate>
                Profil
            </a>

            <button wire:click="logout" class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-950/20 hover:bg-blue-500">
                Déconnexion
            </button>
        </div>
    </div>
</div>
