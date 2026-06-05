<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-section-title">Catalogue</p>
                <h1 class="app-page-title">Produits</h1>
            </div>
            @can('products.create')
                <a href="{{ route('products.create') }}" class="app-action">
                    Ajouter un produit
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-6 lg:px-8">
            <form method="GET" action="{{ route('products.index') }}" class="app-toolbar">
                <label for="search" class="sr-only">Rechercher un produit</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Nom, SKU ou code-barres" class="min-w-0 flex-1 border-slate-200 shadow-none focus:border-blue-600 focus:ring-blue-600">
                <button class="app-action">Rechercher</button>
            </form>

            <section class="app-table-panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Produit</th>
                                <th class="px-5 py-3">Catégorie</th>
                                <th class="px-5 py-3">Stock</th>
                                @can('forecasts.view')
                                    <th class="px-5 py-3">Risque</th>
                                @endcan
                                <th class="px-5 py-3">Statut</th>
                                <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($products as $product)
                                @php
                                    $isCritical = (float) $product->current_stock <= (float) $product->critical_stock;
                                @endphp
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $product->category->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="{{ $isCritical ? 'font-semibold text-rose-700' : 'text-gray-700' }}">{{ number_format($product->current_stock, 0, ',', ' ') }} {{ $product->unit }}</span>
                                        <p class="text-xs text-gray-500">Seuil {{ number_format($product->critical_stock, 0, ',', ' ') }}</p>
                                    </td>
                                    @can('forecasts.view')
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                            @if ($product->latestForecast)
                                                <a href="{{ route('forecasts.show', $product->latestForecast) }}" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->latestForecast->riskBadgeTone() }} hover:opacity-80">{{ $product->latestForecast->riskLabel() }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endcan
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $product->status->label() }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('products.show', $product) }}" class="app-secondary-action px-3 py-1.5 text-xs">Consulter</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->user()->can('forecasts.view') ? 6 : 5 }}" class="px-5 py-8 text-center text-gray-500">Aucun produit trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
