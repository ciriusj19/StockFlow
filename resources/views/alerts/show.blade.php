<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-semibold uppercase text-blue-700">Surveillance</p><h1 class="text-xl font-semibold text-gray-900">Alerte #{{ $alert->id }}</h1><p class="text-sm text-gray-500">{{ $alert->product->name }}</p></div>
            <a href="{{ route('alerts.index') }}" class="app-action">Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Statut</p><p class="mt-2 font-semibold text-gray-900">{{ $alert->status->label() }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Stock actuel</p><p class="mt-2 text-2xl font-semibold text-rose-700">{{ number_format($alert->product->current_stock, 0, ',', ' ') }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Seuil critique</p><p class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format($alert->product->critical_stock, 0, ',', ' ') }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Risque previsionnel</p><p class="mt-2 text-2xl font-semibold {{ $alert->product->latestForecast?->riskTextTone() ?? 'text-gray-900' }}">{{ $alert->product->latestForecast?->riskLabel() ?? '-' }}</p></article>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">Detail</h2>
                <p class="mt-3 text-gray-700">{{ $alert->message }}</p>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2"><div><dt class="text-gray-500">Déclenchée le</dt><dd class="mt-1 font-medium text-gray-900">{{ $alert->triggered_at->translatedFormat('d M Y, H:i') }}</dd></div><div><dt class="text-gray-500">Résolue le</dt><dd class="mt-1 font-medium text-gray-900">{{ $alert->resolved_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div></dl>
            </section>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products.show', $alert->product) }}" class="app-secondary-action">Voir le produit</a>
                @if ($alert->status->value !== 'resolved')
                    @can('alerts.resolve')
                        <form method="POST" action="{{ route('alerts.resolve', $alert) }}">@csrf @method('PATCH')<button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Marquer comme résolue</button></form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
