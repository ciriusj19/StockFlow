<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-semibold uppercase text-blue-700">Administration</p><h1 class="text-xl font-semibold text-gray-900">Modifier {{ $user->name }}</h1><p class="text-sm text-gray-500">{{ $user->email }}</p></div></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-6 lg:px-8">
            <section class="app-panel px-5 py-6">
                <form method="POST" action="{{ route('users.update', $user) }}">@include('users._form')</form>
                @can('users.disable')
                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="mt-6 border-t border-gray-200 pt-5">
                        @csrf
                        @method('PATCH')
                        <button class="text-sm font-semibold {{ $user->status->value === 'active' ? 'text-rose-700 hover:text-rose-900' : 'text-emerald-700 hover:text-emerald-900' }}">{{ $user->status->value === 'active' ? 'Désactiver ce compte' : 'Réactiver ce compte' }}</button>
                    </form>
                @endcan
            </section>
        </div>
    </div>
</x-app-layout>
