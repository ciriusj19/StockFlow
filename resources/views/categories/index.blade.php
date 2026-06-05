<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Référentiel</p>
                <h1 class="text-xl font-semibold text-gray-900">Catégories</h1>
            </div>
            @can('categories.create')
                <a href="{{ route('categories.create') }}" class="app-action">Ajouter une categorie</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-6 lg:px-8">
            <section class="app-table-panel">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Catégorie</th><th class="px-5 py-3">Produits</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $category->name }}</p><p class="text-xs text-gray-500">{{ $category->description }}</p></td>
                                <td class="px-5 py-4 text-gray-700">{{ $category->products_count }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $category->status->label() }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    @if ($category->status->value === 'active')
                                        <a href="{{ route('categories.edit', $category) }}" class="font-semibold text-blue-700 hover:text-blue-900">Modifier</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Aucune categorie.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            <div class="mt-5">{{ $categories->links() }}</div>
        </div>
    </div>
</x-app-layout>
