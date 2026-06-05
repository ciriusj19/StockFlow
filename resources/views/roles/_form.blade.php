@csrf
@if (isset($role))
    @method('PUT')
@endif
@php
    $selectedPermissions = old('permissions', isset($role) ? $role->permissions->pluck('name')->all() : []);
@endphp

<div>
    <x-input-label for="name" value="Nom du role" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $role->name ?? '') }}" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="mt-6">
    <p class="text-sm font-semibold text-gray-900">Droits d'acces</p>
    <x-input-error class="mt-2" :messages="$errors->get('permissions')" />
    <div class="mt-3 space-y-5">
        @foreach ($permissionGroups as $group)
            <section class="rounded-lg border border-gray-200 bg-gray-50/60 p-4">
                <h2 class="text-xs font-semibold uppercase text-gray-500">{{ $group['label'] }}</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($group['permissions'] as $permission)
                        <label class="flex items-start gap-3 rounded-md border border-gray-200 bg-white p-3 text-sm text-gray-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="mt-1 rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-600" @checked(in_array($permission->name, $selectedPermissions, true))>
                            <span>
                                <span class="block font-medium text-gray-900">{{ \App\Support\PermissionCatalog::permissionLabel($permission->name) }}</span>
                                <span class="mt-0.5 block text-xs leading-5 text-gray-500">{{ \App\Support\PermissionCatalog::permissionDescription($permission->name) }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
<div class="mt-6 flex flex-wrap gap-3">
    <button class="app-action">{{ isset($role) ? 'Enregistrer' : 'Creer le role' }}</button>
    <a href="{{ route('roles.index') }}" class="app-secondary-action">Annuler</a>
</div>
