<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-semibold uppercase text-blue-700">Référentiel</p><h1 class="text-xl font-semibold text-gray-900">Ajouter un fournisseur</h1></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-6 lg:px-8"><section class="app-panel px-5 py-6"><form method="POST" action="{{ route('suppliers.store') }}">@include('suppliers._form')</form></section></div></div>
</x-app-layout>
