<x-app-layout>
    <x-slot name="header">
        <div><p class="text-xs font-semibold uppercase text-blue-700">Controle physique</p><h1 class="text-xl font-semibold text-gray-900">Nouvel inventaire</h1></div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-6 lg:px-8">
            <form method="POST" action="{{ route('inventories.store') }}" class="space-y-6">
                @csrf
                <section class="app-panel px-5 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="inventory_date" value="Date d'inventaire" />
                            <x-text-input id="inventory_date" name="inventory_date" type="date" class="mt-1 block w-full" value="{{ old('inventory_date', today()->toDateString()) }}" required />
                            <x-input-error class="mt-2" :messages="$errors->get('inventory_date')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" value="Note" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="app-table-panel">
                    <div class="border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Produits a compter</h2><p class="text-sm text-gray-500">Selectionnez au moins un produit actif.</p></div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <label class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50">
                                <span><span class="block font-medium text-gray-900">{{ $product->name }}</span><span class="block text-xs text-gray-500">{{ $product->sku }}</span></span>
                                <span class="flex items-center gap-4"><span class="text-sm text-gray-600">{{ number_format($product->current_stock, 0, ',', ' ') }} {{ $product->unit }}</span><input type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, old('product_ids', []))) class="rounded border-gray-300 text-blue-700 focus:ring-blue-600"></span>
                            </label>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-gray-500">Aucun produit actif.</p>
                        @endforelse
                    </div>
                    <x-input-error class="px-5 py-3" :messages="$errors->get('product_ids')" />
                </section>

                <div class="flex flex-wrap gap-3"><button class="app-action">Creer l'inventaire</button><a href="{{ route('inventories.index') }}" class="app-secondary-action">Annuler</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
