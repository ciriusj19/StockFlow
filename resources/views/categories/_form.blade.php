@csrf
@if (isset($category))
    @method('PUT')
@endif

<div>
    <x-input-label for="name" value="Nom" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $category->name ?? '') }}" required />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>
<div class="mt-5">
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('description', $category->description ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
<div class="mt-6 flex flex-wrap gap-3">
    <button class="app-action">{{ isset($category) ? 'Enregistrer' : 'Creer la categorie' }}</button>
    <a href="{{ route('categories.index') }}" class="app-secondary-action">Annuler</a>
</div>
