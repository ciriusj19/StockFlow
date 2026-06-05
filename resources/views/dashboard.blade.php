<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-section-title">Vue opérationnelle</p>
                <h1 class="app-page-title">Pilotage des stocks</h1>
            </div>
            <p class="app-muted">{{ now()->translatedFormat('d M Y, H:i') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-6 lg:px-8">
            <section aria-label="Indicateurs principaux" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @php
                    $metricCards = [
                        ['label' => 'Produits actifs', 'value' => $metrics['products'], 'tone' => 'text-gray-900'],
                        ['label' => 'Stocks critiques', 'value' => $metrics['critical_products'], 'tone' => 'text-amber-700'],
                        ['label' => 'Ruptures', 'value' => $metrics['out_of_stock_products'], 'tone' => 'text-rose-700'],
                        ['label' => 'Alertes ouvertes', 'value' => $metrics['open_alerts'], 'tone' => 'text-rose-700'],
                        ['label' => 'Risques élevés', 'value' => $metrics['high_risk_forecasts'], 'tone' => 'text-orange-700'],
                    ];
                @endphp

                @foreach ($metricCards as $card)
                    <article class="app-kpi-card">
                        <p class="text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-4 text-4xl font-semibold tracking-tight {{ $card['tone'] }}">{{ $card['value'] }}</p>
                        <div class="mt-5 h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 w-1/2 rounded-full bg-blue-600/70"></div>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="app-panel px-5 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950">Chaîne de décision StockFlow</h2>
                        <p class="app-muted">Chaque opération de stock nourrit une alerte, une prévision, puis une synthèse exploitable en réunion.</p>
                    </div>
                    <div class="grid gap-2 text-sm font-semibold text-slate-700 sm:grid-cols-4">
                        @foreach (['Mouvements', 'Alertes', 'Prévisions', 'Synthèse'] as $step)
                            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-2 text-center text-blue-900">{{ $step }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-3">
                <section class="app-chart-panel xl:col-span-2">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Activité sur 30 jours</h2>
                        <p class="app-muted">Volumes d'entrées et de sorties enregistrés.</p>
                    </div>
                    <div data-chart-kind="movement-activity" data-chart='@json($charts['movement_activity'])' class="mt-4 h-60"></div>
                </section>

                <section class="app-chart-panel">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Répartition des risques</h2>
                        <p class="app-muted">Dernière prévision par produit.</p>
                    </div>
                    <div data-chart-kind="risk-distribution" data-chart='@json($charts['risk_distribution'])' class="mt-4 h-60"></div>
                </section>
            </div>

            <section class="app-chart-panel">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Produits les plus exposés</h2>
                    <p class="app-muted">Classement par niveau de risque de rupture.</p>
                </div>
                <div data-chart-kind="top-risks" data-chart='@json($charts['top_risks'])' class="mt-4 h-64"></div>
            </section>

            <section class="app-table-panel">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Prévisions de rupture</h2>
                        <p class="app-muted">Calcul basé sur les sorties des 90 derniers jours.</p>
                    </div>
                    <a href="{{ route('forecasts.index') }}" class="app-secondary-action px-3 py-2">Voir toutes</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Produit</th>
                                <th class="px-5 py-3">Stock</th>
                                <th class="px-5 py-3">CMJ</th>
                                <th class="px-5 py-3">Rupture estimée</th>
                                <th class="px-5 py-3">Risque</th>
                                <th class="px-5 py-3">À commander</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($forecasts as $forecast)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $forecast->product->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ number_format($forecast->product->current_stock, 0, ',', ' ') }} {{ $forecast->product->unit }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ number_format($forecast->average_daily_usage, 2, ',', ' ') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-700">
                                        {{ $forecast->predicted_out_date?->translatedFormat('d M Y') ?? 'Non estimable' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <a href="{{ route('forecasts.show', $forecast) }}" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $forecast->riskBadgeTone() }} hover:opacity-80">{{ $forecast->riskLabel() }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">{{ number_format($forecast->recommended_quantity, 0, ',', ' ') }} {{ $forecast->product->unit }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucune prévision disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-2">
                <section class="app-table-panel">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Alertes ouvertes</h2>
                            <p class="app-muted">Une seule alerte active par produit.</p>
                        </div>
                        <a href="{{ route('alerts.index') }}" class="app-secondary-action whitespace-nowrap px-3 py-2">Voir toutes</a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($alerts as $alert)
                            <li class="flex items-center justify-between gap-4 px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $alert->product->name }}</p>
                                    <p class="text-sm text-gray-500">Stock {{ number_format($alert->product->current_stock, 0, ',', ' ') }} / seuil {{ number_format($alert->product->critical_stock, 0, ',', ' ') }}</p>
                                </div>
                                <a href="{{ route('alerts.show', $alert) }}" class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 hover:bg-rose-200">{{ $alert->status->label() }}</a>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-gray-500">Aucune alerte ouverte.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="app-table-panel">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Mouvements récents</h2>
                        <p class="app-muted">Historique immutable des opérations.</p>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($movements as $movement)
                            <li class="flex items-center justify-between gap-4 px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $movement->product->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $movement->movement_date->translatedFormat('d M, H:i') }} / {{ $movement->user->name }}</p>
                                </div>
                                <p class="whitespace-nowrap text-sm font-semibold {{ $movement->type->value === 'ENTRY' ? 'text-emerald-700' : ($movement->type->value === 'EXIT' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ $movement->type->value === 'ENTRY' ? '+' : ($movement->type->value === 'EXIT' ? '-' : '') }}{{ number_format($movement->quantity, 0, ',', ' ') }}
                                </p>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-gray-500">Aucun mouvement enregistré.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
