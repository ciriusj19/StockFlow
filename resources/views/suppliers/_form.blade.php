@csrf
@if (isset($supplier))
    @method('PUT')
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2"><x-input-label for="name" value="Nom" /><x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $supplier->name ?? '') }}" required /><x-input-error class="mt-2" :messages="$errors->get('name')" /></div>
    <div><x-input-label for="contact_name" value="Contact" /><x-text-input id="contact_name" name="contact_name" class="mt-1 block w-full" value="{{ old('contact_name', $supplier->contact_name ?? '') }}" /><x-input-error class="mt-2" :messages="$errors->get('contact_name')" /></div>
    <div><x-input-label for="phone" value="Telephone" /><x-text-input id="phone" name="phone" class="mt-1 block w-full" value="{{ old('phone', $supplier->phone ?? '') }}" required /><x-input-error class="mt-2" :messages="$errors->get('phone')" /></div>
    <div class="sm:col-span-2"><x-input-label for="email" value="Adresse email" /><x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $supplier->email ?? '') }}" /><x-input-error class="mt-2" :messages="$errors->get('email')" /></div>
    <div class="sm:col-span-2"><x-input-label for="address" value="Adresse" /><textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('address', $supplier->address ?? '') }}</textarea><x-input-error class="mt-2" :messages="$errors->get('address')" /></div>
</div>
<div class="mt-6 flex flex-wrap gap-3"><button class="app-action">{{ isset($supplier) ? 'Enregistrer' : 'Creer le fournisseur' }}</button><a href="{{ route('suppliers.index') }}" class="app-secondary-action">Annuler</a></div>
