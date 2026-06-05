<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-semibold uppercase text-blue-700">Administration</p><h1 class="text-xl font-semibold text-gray-900">Ajouter un role</h1></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl px-6 lg:px-8"><section class="app-panel px-5 py-6"><form method="POST" action="{{ route('roles.store') }}">@include('roles._form')</form></section></div></div>
</x-app-layout>
