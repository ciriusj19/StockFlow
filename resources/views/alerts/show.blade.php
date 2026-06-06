<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Surveillance</p>
                <h1 class="text-xl font-semibold text-gray-900">Alerte #{{ $alert->id }}</h1>
                <p class="text-sm text-gray-500">{{ $alert->product->name }}</p>
            </div>
            <a href="{{ route('alerts.index') }}" class="app-action">Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Statut</p>
                    <p class="mt-2 font-semibold text-gray-900">{{ $alert->status->label() }}</p>
                </article>
                <article class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Stock actuel</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700">{{ number_format($alert->product->current_stock, 0, ',', ' ') }}</p>
                </article>
                <article class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Seuil critique</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format($alert->product->critical_stock, 0, ',', ' ') }}</p>
                </article>
                <article class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Risque prévisionnel</p>
                    <p class="mt-2 text-2xl font-semibold {{ $alert->product->latestForecast?->riskTextTone() ?? 'text-gray-900' }}">{{ $alert->product->latestForecast?->riskLabel() ?? '-' }}</p>
                </article>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">Détail</h2>
                <p class="mt-3 text-gray-700">{{ $alert->message }}</p>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">Déclenchée le</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $alert->triggered_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Résolue le</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $alert->resolved_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            @if ($alert->status->value !== 'resolved' && (auth()->user()->can('stock.entry') || auth()->user()->can('inventories.create')))
                <section class="rounded-lg border border-blue-100 bg-blue-50/70 p-5">
                    <h2 class="font-semibold text-gray-900">Corriger le stock</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Une alerte se résout quand le stock repasse au-dessus du seuil critique. Choisissez l'action qui correspond à la situation réelle.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @can('stock.entry')
                            <a href="{{ route('products.show', $alert->product) }}#stock-entry" class="inline-flex items-center justify-center rounded-full bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:bg-emerald-800">
                                Entrer du stock
                            </a>
                        @endcan

                        @can('inventories.create')
                            <a href="{{ route('inventories.create', ['product_id' => $alert->product_id]) }}" class="app-secondary-action">
                                Faire un inventaire
                            </a>
                        @endcan
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
