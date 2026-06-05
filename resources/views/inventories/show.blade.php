<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-semibold uppercase text-blue-700">Controle physique</p><h1 class="text-xl font-semibold text-gray-900">Inventaire #{{ $inventory->id }}</h1><p class="text-sm text-gray-500">{{ $inventory->inventory_date->translatedFormat('d M Y') }} · {{ $inventory->user->name }}</p></div>
            <a href="{{ route('inventories.index') }}" class="app-action">Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-6 lg:px-8">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-5 py-4">
                <div><p class="text-sm font-medium text-gray-500">Statut</p><p class="mt-1 font-semibold text-gray-900">{{ $inventory->status->label() }}</p></div>
                @if ($inventory->validated_at)<p class="text-sm text-gray-500">Validé le {{ $inventory->validated_at->translatedFormat('d M Y, H:i') }}</p>@endif
            </div>

            <form method="POST" action="{{ route('inventories.update', $inventory) }}">
                @csrf
                @method('PUT')
                <section class="app-table-panel">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Produit</th><th class="px-5 py-3">Stock theorique</th><th class="px-5 py-3">Stock reel</th><th class="px-5 py-3">Ecart</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($inventory->items->sortBy('product.name') as $item)
                                    <tr>
                                        <td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $item->product->name }}</p><p class="text-xs text-gray-500">{{ $item->product->sku }}</p></td>
                                        <td class="px-5 py-4 text-gray-700">{{ number_format($item->expected_quantity, 0, ',', ' ') }} {{ $item->product->unit }}</td>
                                        <td class="px-5 py-4">
                                            @if ($inventory->status->value === 'draft')
                                                <label class="sr-only" for="actual-{{ $item->id }}">Quantité réelle {{ $item->product->name }}</label>
                                                <input id="actual-{{ $item->id }}" name="actual_quantities[{{ $item->id }}]" type="number" min="0" step="0.01" value="{{ old('actual_quantities.'.$item->id, $item->actual_quantity) }}" class="w-28 rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" required>
                                            @else
                                                {{ number_format($item->actual_quantity, 0, ',', ' ') }} {{ $item->product->unit }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-semibold {{ (float) $item->difference === 0.0 ? 'text-gray-500' : ((float) $item->difference > 0 ? 'text-emerald-700' : 'text-rose-700') }}">{{ number_format($item->difference, 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($inventory->status->value === 'draft')
                    <div class="mt-5"><x-input-label for="notes" value="Note" /><textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('notes', $inventory->notes) }}</textarea></div>
                    <div class="mt-5"><button class="app-secondary-action">Enregistrer le comptage</button></div>
                @endif
            </form>

            @if ($inventory->status->value === 'draft')
                @can('inventories.validate')
                    <form method="POST" action="{{ route('inventories.validate', $inventory) }}" class="border-t border-gray-200 pt-5">
                        @csrf
                        <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Valider et generer les ajustements</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>
</x-app-layout>
