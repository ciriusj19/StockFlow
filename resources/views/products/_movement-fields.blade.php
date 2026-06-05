@php
    $quantityLabel = $quantityLabel ?? 'Quantité';
    $quantityMin = $quantityMin ?? '0.01';
@endphp

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="{{ $prefix }}quantity" value="{{ $quantityLabel }}" />
        <x-text-input id="{{ $prefix }}quantity" name="quantity" type="number" min="{{ $quantityMin }}" step="0.01" class="mt-1 block w-full" required />
        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
    </div>
    <div>
        <x-input-label for="{{ $prefix }}reference" value="Reference" />
        <x-text-input id="{{ $prefix }}reference" name="reference" class="mt-1 block w-full" />
        <x-input-error class="mt-2" :messages="$errors->get('reference')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="{{ $prefix }}notes" value="Note" />
        <textarea id="{{ $prefix }}notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600"></textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>
