@csrf
@if (isset($user))
    @method('PUT')
@endif

<div>
    <x-input-label for="name" value="Nom" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $user->name ?? '') }}" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>
<div class="mt-5">
    <x-input-label for="email" value="Adresse email" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $user->email ?? '') }}" required />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>
<div class="mt-5">
    <x-input-label for="role_name" value="Role" />
    <select id="role_name" name="role_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" required>
        <option value="">Selectionner un role</option>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected(old('role_name', isset($user) ? $user->roles->first()?->name : '') === $role->name)>{{ $role->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('role_name')" />
</div>
<div class="mt-5 grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="password" :value="isset($user) ? 'Nouveau mot de passe' : 'Mot de passe'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Confirmation" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! isset($user)" autocomplete="new-password" />
    </div>
</div>
@if (isset($user))
    <p class="mt-2 text-xs text-gray-500">Laissez les champs mot de passe vides pour conserver le mot de passe actuel.</p>
@endif
<div class="mt-6 flex flex-wrap gap-3">
    <button class="app-action">{{ isset($user) ? 'Enregistrer' : 'Creer le compte' }}</button>
    <a href="{{ route('users.index') }}" class="app-secondary-action">Annuler</a>
</div>
