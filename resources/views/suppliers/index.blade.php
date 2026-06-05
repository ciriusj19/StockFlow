<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-semibold uppercase text-blue-700">Référentiel</p><h1 class="text-xl font-semibold text-gray-900">Fournisseurs</h1></div>
            @can('suppliers.create')<a href="{{ route('suppliers.create') }}" class="app-action">Ajouter un fournisseur</a>@endcan
        </div>
    </x-slot>

    <div class="py-8"><div class="mx-auto max-w-6xl px-6 lg:px-8">
        <section class="app-table-panel"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Fournisseur</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">Produits</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppliers as $supplier)
                    <tr><td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $supplier->name }}</p><p class="text-xs text-gray-500">{{ $supplier->email }}</p></td><td class="px-5 py-4 text-gray-700">{{ $supplier->phone }}</td><td class="px-5 py-4 text-gray-700">{{ $supplier->products_count }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $supplier->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $supplier->status->label() }}</span></td><td class="px-5 py-4 text-right">@if ($supplier->status->value === 'active')<a href="{{ route('suppliers.edit', $supplier) }}" class="font-semibold text-blue-700 hover:text-blue-900">Modifier</a>@endif</td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Aucun fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table></div></section>
        <div class="mt-5">{{ $suppliers->links() }}</div>
    </div></div>
</x-app-layout>
