<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="app-section-title">Anticipation</p><h1 class="app-page-title">Prévisions de rupture</h1></div>
            <form method="POST" action="{{ route('forecasts.refresh') }}">@csrf<button class="app-action">Recalculer maintenant</button></form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-6 lg:px-8">
            <section class="rounded-2xl border border-blue-200 bg-blue-50/85 px-5 py-4 text-sm font-medium text-blue-900 shadow-sm shadow-blue-900/5">
                Les prévisions utilisent les sorties des 90 derniers jours. La quantité recommandée vise une couverture de 60 jours. Sans consommation observée, la rupture reste non estimable.
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="app-chart-panel">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Jours restants avant rupture</h2>
                        <p class="app-muted">Produits dont la date de rupture est estimable.</p>
                    </div>
                    <div data-chart-kind="forecast-days" data-chart='@json($charts['days_remaining'])' class="mt-4 h-72"></div>
                </section>

                <section class="app-chart-panel">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Quantités recommandées</h2>
                        <p class="app-muted">Réapprovisionnement calculé pour viser 60 jours de couverture.</p>
                    </div>
                    <div data-chart-kind="forecast-recommended" data-chart='@json($charts['recommended_quantities'])' class="mt-4 h-72"></div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="app-chart-panel">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Priorité de réapprovisionnement</h2>
                        <p class="app-muted">Produits à traiter en premier, selon risque et quantité conseillée.</p>
                    </div>
                    <div data-chart-kind="forecast-priority" data-chart='@json($charts['priority_restock'])' class="mt-4 h-72"></div>
                </section>

                <section class="app-chart-panel">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Produits les plus consommés</h2>
                        <p class="app-muted">Sorties cumulées sur les 90 derniers jours, base du calcul de prévision.</p>
                    </div>
                    <div data-chart-kind="top-consumption" data-chart='@json($charts['top_consumption'])' class="mt-4 h-72"></div>
                </section>
            </div>

            <section class="app-table-panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-5 py-3">Produit</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">CMJ</th><th class="px-5 py-3">Rupture estimée</th><th class="px-5 py-3">Risque</th><th class="px-5 py-3">À commander</th><th class="px-5 py-3">Génération</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($forecasts as $forecast)
                                <tr>
                                    <td class="px-5 py-4"><a href="{{ route('products.show', $forecast->product) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $forecast->product->name }}</a><p class="text-xs text-gray-500">{{ $forecast->product->sku }}</p></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ number_format($forecast->product->current_stock, 0, ',', ' ') }} {{ $forecast->product->unit }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ number_format($forecast->average_daily_usage, 2, ',', ' ') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $forecast->predicted_out_date?->translatedFormat('d M Y') ?? 'Non estimable' }}</td>
                                    <td class="px-5 py-4"><a href="{{ route('forecasts.show', $forecast) }}" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $forecast->riskBadgeTone() }} hover:opacity-80">{{ $forecast->riskLabel() }}</a></td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">{{ number_format($forecast->recommended_quantity, 0, ',', ' ') }} {{ $forecast->product->unit }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $forecast->generated_at->translatedFormat('d M, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">Aucune prévision calculée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            {{ $forecasts->links() }}
        </div>
    </div>
</x-app-layout>
