@csrf
@if (isset($product))
    @method('PUT')
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nom" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $product->name ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" class="mt-1 block w-full" value="{{ old('sku', $product->sku ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('sku')" />
    </div>

    <div>
        <x-input-label for="barcode" value="Code-barres" />
        <x-text-input id="barcode" name="barcode" class="mt-1 block w-full" value="{{ old('barcode', $product->barcode ?? '') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
    </div>

    <div>
        <x-input-label for="category_id" value="Catégorie" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" required>
            <option value="">Selectionner</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
    </div>

    <div>
        <x-input-label for="supplier_id" value="Fournisseur" />
        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" required>
            <option value="">Selectionner</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
    </div>

    <div>
        <x-input-label for="purchase_price" value="Prix d'achat" />
        <x-text-input id="purchase_price" name="purchase_price" type="number" min="0" step="0.01" class="mt-1 block w-full" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('purchase_price')" />
    </div>

    <div>
        <x-input-label for="sale_price" value="Prix de vente" />
        <x-text-input id="sale_price" name="sale_price" type="number" min="0" step="0.01" class="mt-1 block w-full" value="{{ old('sale_price', $product->sale_price ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('sale_price')" />
    </div>

    <div>
        <x-input-label for="unit" value="Unite" />
        <x-text-input id="unit" name="unit" class="mt-1 block w-full" value="{{ old('unit', $product->unit ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('unit')" />
    </div>

    <div>
        <x-input-label for="critical_stock" value="Stock critique" />
        <x-text-input id="critical_stock" name="critical_stock" type="number" min="0" step="0.01" class="mt-1 block w-full" value="{{ old('critical_stock', $product->critical_stock ?? '') }}" required />
        <x-input-error class="mt-2" :messages="$errors->get('critical_stock')" />
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="app-action">
        {{ isset($product) ? 'Enregistrer' : 'Creer le produit' }}
    </button>
    <a href="{{ isset($product) ? route('products.show', $product) : route('products.index') }}" class="app-secondary-action">Annuler</a>
</div>
