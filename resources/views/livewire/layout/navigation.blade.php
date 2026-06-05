<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

@php
    $homeHref = auth()->user()->can('forecasts.view')
        ? route('dashboard')
        : (auth()->user()->can('analytics.view')
            ? route('analytics.index')
            : (auth()->user()->can('products.view') ? route('products.index') : route('profile')));
@endphp

<nav x-data="{ open: false }">
    <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-700/60 bg-slate-950 px-4 shadow-sm lg:hidden">
        <a href="{{ $homeHref }}" class="flex items-center gap-3" wire:navigate>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm shadow-blue-950/30">SF</span>
            <span class="font-semibold text-white">StockFlow</span>
        </a>

        <button
            type="button"
            x-on:click="open = true"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-200 hover:bg-white/10 hover:text-white focus:bg-white/10 focus:outline-none"
            aria-label="Ouvrir la navigation"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>
    </div>

    <div
        x-show="open"
        x-transition.opacity
        x-on:click="open = false"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        style="display: none;"
        aria-hidden="true"
    ></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-700/60 bg-slate-950 shadow-xl lg:hidden"
        style="display: none;"
    >
        @include('livewire.layout.sidebar-content', ['homeHref' => $homeHref, 'closeOnNavigate' => true])
    </aside>

    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col border-r border-slate-700/60 bg-slate-950 shadow-[18px_0_60px_rgba(15,23,42,0.10)] lg:flex">
        @include('livewire.layout.sidebar-content', ['homeHref' => $homeHref, 'closeOnNavigate' => false])
    </aside>
</nav>
