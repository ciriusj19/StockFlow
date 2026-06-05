<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-section-title">Administration</p>
                <h1 class="app-page-title">Utilisateurs</h1>
            </div>
            @can('users.create')
                <a href="{{ route('users.create') }}" class="app-action">Ajouter un utilisateur</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-6 lg:px-8">
            <form method="GET" action="{{ route('users.index') }}" class="app-toolbar">
                <label for="search" class="sr-only">Rechercher un utilisateur</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Nom ou adresse email" class="min-w-0 flex-1 border-slate-200 shadow-none focus:border-blue-600 focus:ring-blue-600">
                <label for="status" class="sr-only">Filtrer par statut</label>
                <select id="status" name="status" class="w-48 border-slate-200 shadow-none focus:border-blue-600 focus:ring-blue-600">
                    <option value="">Tous les statuts</option>
                    <option value="active" @selected($status === 'active')>Actifs</option>
                    <option value="disabled" @selected($status === 'disabled')>Désactivés</option>
                </select>
                <button class="app-action">Rechercher</button>
            </form>

            @if ($errors->any())
                <section class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    {{ $errors->first() }}
                </section>
            @endif

            <section class="app-table-panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-5 py-3">Utilisateur</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Derniere connexion</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"><span class="sr-only">Actions</span></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $user->name }}</p><p class="text-xs text-gray-500">{{ $user->email }}</p></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Jamais' }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $user->status->label() }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('users.edit', $user) }}" class="font-semibold text-blue-700 hover:text-blue-900">Modifier</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Aucun utilisateur trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
