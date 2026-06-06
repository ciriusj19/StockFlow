<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Fiche produit</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $product->name }}</h1>
                <p class="text-sm text-gray-500">{{ $product->sku }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('products.update')
                    @if ($product->status->value === 'active')
                        <a href="{{ route('products.edit', $product) }}" class="app-secondary-action">Modifier</a>
                    @endif
                @endcan
                <a href="{{ route('products.index') }}" class="app-action">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 {{ auth()->user()->can('forecasts.view') ? 'xl:grid-cols-5' : 'lg:grid-cols-4' }}">
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Stock actuel</p><p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($product->current_stock, 0, ',', ' ') }} {{ $product->unit }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Stock critique</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format($product->critical_stock, 0, ',', ' ') }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Catégorie</p><p class="mt-2 font-semibold text-gray-900">{{ $product->category->name }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Fournisseur</p><p class="mt-2 font-semibold text-gray-900">{{ $product->supplier->name }}</p></article>
                @can('forecasts.view')
                    <article class="rounded-lg border border-gray-200 bg-white p-4">
                        <p class="text-sm text-gray-500">Risque</p>
                        <p class="mt-2 text-2xl font-semibold">
                            @if ($product->latestForecast)
                                <a href="{{ route('forecasts.show', $product->latestForecast) }}" class="{{ $product->latestForecast->riskTextTone() }} hover:opacity-80">{{ $product->latestForecast->riskLabel() }}</a>
                            @else
                                -
                            @endif
                        </p>
                    </article>
                @endcan
            </section>

            @if ($product->status->value === 'active' && (auth()->user()->can('stock.entry') || auth()->user()->can('stock.exit') || auth()->user()->can('stock.adjustment')))
                <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                    @can('stock.entry')
                        <form id="stock-entry" method="POST" action="{{ route('products.movements.store', $product) }}" class="scroll-mt-24 rounded-lg border border-emerald-200 bg-white p-5">
                            @csrf
                            <input type="hidden" name="type" value="ENTRY">
                            <h2 class="font-semibold text-gray-900">Entree de stock</h2>
                            <p class="mt-1 text-sm text-gray-500">Ajouter les quantites recues.</p>
                            @include('products._movement-fields', ['prefix' => 'entry-'])
                            <button class="mt-5 rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Enregistrer l'entree</button>
                        </form>
                    @endcan

                    @can('stock.exit')
                        <form method="POST" action="{{ route('products.movements.store', $product) }}" class="rounded-lg border border-rose-200 bg-white p-5">
                            @csrf
                            <input type="hidden" name="type" value="EXIT">
                            <h2 class="font-semibold text-gray-900">Sortie de stock</h2>
                            <p class="mt-1 text-sm text-gray-500">Retirer une quantite disponible.</p>
                            @include('products._movement-fields', ['prefix' => 'exit-'])
                            <button class="mt-5 rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Enregistrer la sortie</button>
                        </form>
                    @endcan

                    @can('stock.adjustment')
                        <form method="POST" action="{{ route('products.movements.store', $product) }}" class="rounded-lg border border-sky-200 bg-white p-5">
                            @csrf
                            <input type="hidden" name="type" value="ADJUSTMENT">
                            <h2 class="font-semibold text-gray-900">Ajustement de stock</h2>
                            <p class="mt-1 text-sm text-gray-500">Saisir le stock reel observe.</p>
                            @include('products._movement-fields', ['prefix' => 'adjustment-', 'quantityLabel' => 'Stock reel', 'quantityMin' => '0'])
                            <button class="mt-5 rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Enregistrer l'ajustement</button>
                        </form>
                    @endcan
                </section>
            @endif

            <section class="app-chart-panel">
                <div>
                    <h2 class="font-semibold text-gray-900">Evolution du stock sur 90 jours</h2>
                    <p class="text-sm text-gray-500">Le seuil critique reste visible pour comparer les mouvements au risque de rupture.</p>
                </div>
                <div data-chart-kind="product-stock" data-chart='@json($stockTrend)' class="mt-4 h-80"></div>
            </section>

            <section class="app-table-panel">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Historique des mouvements</h2>
                    <p class="text-sm text-gray-500">Les mouvements valides sont immuables.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Quantité</th><th class="px-5 py-3">Avant</th><th class="px-5 py-3">Après</th><th class="px-5 py-3">Référence</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($product->stockMovements as $movement)
                                <tr><td class="whitespace-nowrap px-5 py-4">{{ $movement->movement_date->translatedFormat('d M Y, H:i') }}</td><td class="px-5 py-4 font-semibold">{{ $movement->type->label() }}</td><td class="px-5 py-4">{{ number_format($movement->quantity, 0, ',', ' ') }}</td><td class="px-5 py-4">{{ number_format($movement->stock_before, 0, ',', ' ') }}</td><td class="px-5 py-4">{{ number_format($movement->stock_after, 0, ',', ' ') }}</td><td class="px-5 py-4 text-gray-500">{{ $movement->reference ?? '-' }}</td></tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun mouvement enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @can('products.archive')
                @if ($product->status->value === 'active')
                    <form method="POST" action="{{ route('products.archive', $product) }}" class="border-t border-gray-200 pt-5">
                        @csrf
                        @method('PATCH')
                        <button class="text-sm font-semibold text-rose-700 hover:text-rose-900">Archiver ce produit</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>
