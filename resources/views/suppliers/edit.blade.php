<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-semibold uppercase text-blue-700">Référentiel</p><h1 class="text-xl font-semibold text-gray-900">Modifier {{ $supplier->name }}</h1></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-6 lg:px-8"><section class="app-panel px-5 py-6"><form method="POST" action="{{ route('suppliers.update', $supplier) }}">@include('suppliers._form')</form><form method="POST" action="{{ route('suppliers.archive', $supplier) }}" class="mt-6 border-t border-gray-200 pt-5">@csrf @method('PATCH')<button class="text-sm font-semibold text-rose-700 hover:text-rose-900">Archiver ce fournisseur</button></form></section></div></div>
</x-app-layout>
