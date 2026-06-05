<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-section-title">Controle physique</p>
                <h1 class="app-page-title">Inventaires</h1>
            </div>
            @can('inventories.create')
                <a href="{{ route('inventories.create') }}" class="app-action">Nouvel inventaire</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-6 lg:px-8">
            <section class="app-chart-panel">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Ecarts d'inventaire valides</h2>
                    <p class="app-muted">Les ecarts positifs et negatifs montrent les corrections appliquees au stock.</p>
                </div>
                <div data-chart-kind="inventory-differences" data-chart='@json($differenceChart)' class="mt-4 h-80"></div>
            </section>

            <section class="app-table-panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Responsable</th><th class="px-5 py-3">Produits</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"><span class="sr-only">Actions</span></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($inventories as $inventory)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $inventory->inventory_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $inventory->user->name }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $inventory->items_count }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $inventory->status->value === 'validated' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $inventory->status->label() }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('inventories.show', $inventory) }}" class="app-secondary-action px-3 py-1.5 text-xs">Consulter</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Aucun inventaire enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-5">{{ $inventories->links() }}</div>
        </div>
    </div>
</x-app-layout>
