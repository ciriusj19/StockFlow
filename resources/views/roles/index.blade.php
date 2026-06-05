<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-semibold uppercase text-blue-700">Administration</p><h1 class="text-xl font-semibold text-gray-900">Rôles et droits d'accès</h1></div>
            @can('roles.manage')
                <a href="{{ route('roles.create') }}" class="app-action">Ajouter un role</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-6 lg:px-8">
            <section class="app-table-panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Role</th><th class="px-5 py-3">Utilisateurs</th><th class="px-5 py-3">Droits attribues</th><th class="px-5 py-3"><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $role->name }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $role->users_count }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $role->permissions_count }}</td>
                                    <td class="px-5 py-4 text-right">@can('roles.manage')<a href="{{ route('roles.edit', $role) }}" class="font-semibold text-blue-700 hover:text-blue-900">Configurer</a>@endcan</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
