<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700">Anticipation</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $forecast->product->name }}</h1>
                <p class="text-sm text-gray-500">Prevision generee le {{ $forecast->generated_at->translatedFormat('d M Y, H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.show', $forecast->product) }}" class="app-secondary-action">Voir le produit</a>
                <a href="{{ route('forecasts.index') }}" class="app-action">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Stock actuel</p><p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($forecast->product->current_stock, 0, ',', ' ') }} {{ $forecast->product->unit }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">CMJ</p><p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($forecast->average_daily_usage, 2, ',', ' ') }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Rupture estimée</p><p class="mt-2 font-semibold text-gray-900">{{ $forecast->predicted_out_date?->translatedFormat('d M Y') ?? 'Non estimable' }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Risque</p><p class="mt-2 text-2xl font-semibold {{ $forecast->riskTextTone() }}">{{ $forecast->riskLabel() }}</p></article>
                <article class="rounded-lg border border-gray-200 bg-white p-4"><p class="text-sm text-gray-500">Quantité recommandée</p><p class="mt-2 text-2xl font-semibold text-blue-700">{{ number_format($forecast->recommended_quantity, 0, ',', ' ') }} {{ $forecast->product->unit }}</p></article>
            </section>

            <section class="rounded-lg border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">
                @if ((float) $forecast->average_daily_usage === 0.0)
                    Aucune consommation observee sur les 90 derniers jours. La date de rupture ne peut pas etre estimee.
                @else
                    La recommandation vise une couverture de 60 jours a partir de la consommation moyenne observee sur les 90 derniers jours.
                @endif
            </section>

            <section class="app-table-panel">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Historique des calculs</h2>
                    <p class="text-sm text-gray-500">Les recalculs successifs restent consultables.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Génération</th><th class="px-5 py-3">CMJ</th><th class="px-5 py-3">Rupture estimée</th><th class="px-5 py-3">Risque</th><th class="px-5 py-3">À commander</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($history as $item)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $item->generated_at->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ number_format($item->average_daily_usage, 2, ',', ' ') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $item->predicted_out_date?->translatedFormat('d M Y') ?? 'Non estimable' }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->riskBadgeTone() }}">{{ $item->riskLabel() }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">{{ number_format($item->recommended_quantity, 0, ',', ' ') }} {{ $forecast->product->unit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
