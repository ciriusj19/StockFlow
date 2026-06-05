<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-semibold uppercase text-blue-700">Référentiel</p><h1 class="text-xl font-semibold text-gray-900">Modifier {{ $category->name }}</h1></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-2xl px-6 lg:px-8"><section class="app-panel px-5 py-6"><form method="POST" action="{{ route('categories.update', $category) }}">@include('categories._form')</form><form method="POST" action="{{ route('categories.archive', $category) }}" class="mt-6 border-t border-gray-200 pt-5">@csrf @method('PATCH')<button class="text-sm font-semibold text-rose-700 hover:text-rose-900">Archiver cette categorie</button></form></section></div></div>
</x-app-layout>
