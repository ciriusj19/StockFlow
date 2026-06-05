<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-section-title">Décision stratégique</p>
                <h1 class="app-page-title">Synthèse décisionnelle</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($run)
                    @can('analytics.export')
                        <a href="{{ route('analytics.export', ['format' => 'pdf']) }}" class="app-secondary-action px-3 py-2">PDF</a>
                        <a href="{{ route('analytics.export', ['format' => 'excel']) }}" class="inline-flex items-center justify-center rounded-full bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-900/15 hover:bg-emerald-800">Excel</a>
                    @endcan
                @endif
                @can('analytics.compile')
                    <form method="POST" action="{{ route('analytics.compile') }}">
                        @csrf
                        <button class="app-action">Compiler les données</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-6 lg:px-8">
            @if (! $run)
                <section class="app-panel px-5 py-8 text-center">
                    <h2 class="text-lg font-semibold text-gray-900">Aucune synthèse compilée</h2>
                    <p class="mt-2 text-sm text-gray-500">La synthèse décisionnelle apparaîtra après la première compilation.</p>
                    @can('analytics.compile')
                        <form method="POST" action="{{ route('analytics.compile') }}" class="mt-5">
                            @csrf
                            <button class="app-action">Compiler les données</button>
                        </form>
                    @endcan
                </section>
            @else
                <section class="app-panel px-5 py-5">
                    <div class="grid gap-5 lg:grid-cols-[1.4fr_1fr] lg:items-start">
                        <div>
                            <p class="app-section-title">Dernière compilation</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ $run->period_start->format('d/m/Y') }} - {{ $run->period_end->format('d/m/Y') }}</h2>
                            <p class="mt-2 text-sm font-medium text-slate-500">
                                Compilée le {{ $run->compiled_at->translatedFormat('d M Y, H:i') }} par {{ $run->user->name }}.
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Statut</p>
                                <p class="mt-2 text-lg font-semibold text-emerald-700">{{ $run->status === 'completed' ? 'Complétée' : ucfirst($run->status) }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Sources utilisées</p>
                                <p class="mt-2 text-sm font-semibold text-slate-800">Produits, mouvements, alertes, prévisions, inventaires</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section aria-label="Indicateurs décisionnels" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @php
                        $metricCards = [
                            ['label' => 'Produits actifs', 'value' => $summary['products_count'] ?? 0, 'tone' => 'text-gray-900'],
                            ['label' => 'Sous seuil critique', 'value' => $summary['critical_stock_products_count'] ?? 0, 'tone' => 'text-amber-700'],
                            ['label' => 'Alertes ouvertes', 'value' => $summary['open_alerts_count'] ?? 0, 'tone' => 'text-rose-700'],
                            ['label' => 'Risques élevés', 'value' => $summary['high_risk_products_count'] ?? 0, 'tone' => 'text-orange-700'],
                            ['label' => 'À commander', 'value' => number_format($summary['recommended_quantity_total'] ?? 0, 0, ',', ' '), 'tone' => 'text-blue-700'],
                        ];
                    @endphp

                    @foreach ($metricCards as $card)
                        <article class="app-kpi-card">
                            <p class="text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-4 text-4xl font-semibold tracking-tight {{ $card['tone'] }}">{{ $card['value'] }}</p>
                        </article>
                    @endforeach
                </section>

                <div class="grid gap-6 xl:grid-cols-3">
                    <section class="app-chart-panel">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Qualité des données</h2>
                            <p class="app-muted">Capacité des données à produire une décision exploitable.</p>
                        </div>
                        <div data-chart-kind="analytics-data-quality" data-chart='@json($charts['data_quality'])' class="mt-4 h-64"></div>
                    </section>

                    <section class="app-panel px-5 py-5 xl:col-span-2">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-950">Points de vigilance</h2>
                                <p class="app-muted">Ces indicateurs expliquent les limites et la fiabilité de la synthèse.</p>
                            </div>
                            <p class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-800">
                                Score {{ number_format((float) ($summary['data_quality_score'] ?? 0), 1, ',', ' ') }} %
                            </p>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ([
                                ['label' => 'Sans consommation', 'value' => $summary['products_without_consumption_count'] ?? 0],
                                ['label' => 'Rupture non estimable', 'value' => $summary['non_estimable_forecasts_count'] ?? 0],
                                ['label' => 'Sous seuil critique', 'value' => $summary['critical_stock_products_count'] ?? 0],
                                ['label' => 'Écarts inventaire', 'value' => $summary['inventory_sensitive_products_count'] ?? 0],
                            ] as $item)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $item['label'] }}</p>
                                    <p class="mt-3 text-2xl font-semibold text-slate-950">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    <section class="app-chart-panel">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Risque par catégorie</h2>
                            <p class="app-muted">Risque moyen consolidé par famille de produits.</p>
                        </div>
                        <div data-chart-kind="analytics-category-risk" data-chart='@json($charts['category_risk'])' class="mt-4 h-72"></div>
                    </section>

                    <section class="app-chart-panel">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Dépendance fournisseur</h2>
                            <p class="app-muted">Fournisseurs associés aux produits critiques.</p>
                        </div>
                        <div data-chart-kind="analytics-supplier-dependency" data-chart='@json($charts['supplier_dependency'])' class="mt-4 h-72"></div>
                    </section>

                    <section class="app-chart-panel">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Fiabilité inventaire</h2>
                            <p class="app-muted">Produits avec les écarts physiques les plus sensibles.</p>
                        </div>
                        <div data-chart-kind="analytics-inventory-reliability" data-chart='@json($charts['inventory_reliability'])' class="mt-4 h-72"></div>
                    </section>

                    <section class="app-chart-panel">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Tendance alertes / risques</h2>
                            <p class="app-muted">Comparaison entre compilations successives.</p>
                        </div>
                        <div data-chart-kind="analytics-alert-trend" data-chart='@json($charts['alert_trend'])' class="mt-4 h-72"></div>
                    </section>
                </div>

                <section class="app-table-panel">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Historique des compilations</h2>
                        <p class="app-muted">Trace des dernières synthèses produites pour la décision.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Période</th>
                                    <th class="px-5 py-3">Auteur</th>
                                    <th class="px-5 py-3">Alertes</th>
                                    <th class="px-5 py-3">Risques élevés</th>
                                    <th class="px-5 py-3">Qualité</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($runHistory as $history)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $history->compiled_at->translatedFormat('d M Y, H:i') }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $history->period_start->format('d/m/Y') }} - {{ $history->period_end->format('d/m/Y') }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $history->user?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $history->summary['open_alerts_count'] ?? 0 }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $history->summary['high_risk_products_count'] ?? 0 }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-blue-700">{{ number_format((float) ($history->summary['data_quality_score'] ?? 0), 1, ',', ' ') }} %</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun historique disponible.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="app-table-panel">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Produits à discuter en réunion</h2>
                        <p class="app-muted">Priorité basée sur le risque, les alertes ouvertes et le réapprovisionnement conseillé.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Produit</th>
                                    <th class="px-5 py-3">Catégorie</th>
                                    <th class="px-5 py-3">Fournisseur</th>
                                    <th class="px-5 py-3">Stock</th>
                                    <th class="px-5 py-3">Risque</th>
                                    <th class="px-5 py-3">À commander</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($priorityProducts as $product)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">{{ $product->product_name }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $product->category_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ $product->supplier_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-700">{{ number_format($product->stock, 0, ',', ' ') }} / {{ number_format($product->critical_stock, 0, ',', ' ') }} {{ $product->unit }}</td>
                                        <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->riskBadgeTone() }}">{{ $product->risk_label }}</span></td>
                                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">{{ number_format($product->recommended_quantity, 0, ',', ' ') }} {{ $product->unit }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun produit prioritaire.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
