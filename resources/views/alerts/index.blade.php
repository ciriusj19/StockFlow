<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="app-section-title">Surveillance</p>
            <h1 class="app-page-title">Alertes de stock</h1>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-6 lg:px-8">
            <section aria-label="Résumé des alertes" class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Alertes ouvertes', 'value' => $alertSummary['open_alerts'] ?? 0, 'tone' => 'text-rose-700'],
                    ['label' => 'Produits concernés', 'value' => $alertSummary['open_products'] ?? 0, 'tone' => 'text-orange-700'],
                    ['label' => 'Résolues sur 30 jours', 'value' => $alertSummary['resolved_recently'] ?? 0, 'tone' => 'text-emerald-700'],
                ] as $item)
                    <article class="app-kpi-card min-h-24">
                        <p class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight {{ $item['tone'] }}">{{ $item['value'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="app-chart-panel">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Alertes par produit</h2>
                        <p class="app-muted">Chaque ligne verticale compare le stock restant au seuil critique du produit.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-rose-800 ring-1 ring-inset ring-rose-200">Critique</span>
                        <span class="rounded-full bg-orange-100 px-2.5 py-1 text-orange-800 ring-1 ring-inset ring-orange-200">Élevé</span>
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-800 ring-1 ring-inset ring-amber-200">Modéré</span>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-800 ring-1 ring-inset ring-emerald-200">Faible</span>
                    </div>
                </div>

                @if (count($alertBands) === 0)
                    <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Aucune alerte à visualiser.
                    </div>
                @else
                    @php
                        $threshold = $alertBands[0]['threshold'] ?? 66.67;
                        $thresholdTop = 100 - $threshold;
                    @endphp

                    <div class="mt-5 space-y-4">
                        <div class="overflow-x-auto pb-1">
                            <div class="min-w-[48rem]">
                                <div class="relative h-64 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-5">
                                    <div class="pointer-events-none absolute left-4 right-4 border-t border-dashed border-slate-400" style="top: {{ $thresholdTop }}%"></div>
                                    <span class="absolute right-5 rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-600 shadow-sm ring-1 ring-inset ring-slate-200" style="top: calc({{ $thresholdTop }}% - 0.75rem)">Seuil critique</span>
                                    <div class="grid h-full items-end gap-3" style="grid-template-columns: repeat({{ count($alertBands) }}, minmax(4.5rem, 1fr));">
                                        @foreach ($alertBands as $band)
                                            <div class="flex h-full items-end justify-center" title="{{ $band['product'] }}">
                                                <div class="relative flex w-full items-end justify-center" style="height: {{ $band['height'] }}%">
                                                    <div class="h-full w-3 rounded-t-full shadow-sm" style="background-color: {{ $band['barColor'] }}"></div>
                                                    <span class="absolute -top-1.5 left-1/2 h-3 w-3 -translate-x-1/2 rounded-full ring-2 ring-white" style="background-color: {{ $band['barColor'] }}"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3" style="grid-template-columns: repeat({{ count($alertBands) }}, minmax(4.5rem, 1fr));">
                                    @foreach ($alertBands as $band)
                                        <div class="min-w-0 text-center">
                                            <a href="{{ route('alerts.show', $band['alertId']) }}" class="block truncate text-xs font-semibold text-slate-950 hover:text-blue-700" title="{{ $band['product'] }}">
                                                {{ $band['product'] }}
                                            </a>
                                            <p class="mt-0.5 truncate text-[11px] text-slate-500" title="{{ $band['sku'] }}">{{ $band['sku'] }}</p>
                                            @if ($band['risk'])
                                                <span class="mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $band['riskTone'] }}">{{ $band['risk'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            @include('alerts._table', [
                'title' => 'Nouvelles alertes',
                'description' => 'Alertes qui demandent une première consultation.',
                'alerts' => $newAlerts,
                'empty' => 'Aucune nouvelle alerte.',
            ])

            @include('alerts._table', [
                'title' => 'Alertes consultées',
                'description' => 'Alertes déjà ouvertes, mais pas encore résolues.',
                'alerts' => $viewedAlerts,
                'empty' => 'Aucune alerte consultée en attente.',
            ])

            @include('alerts._table', [
                'title' => 'Alertes résolues',
                'description' => 'Historique des alertes validées après retour au-dessus du seuil ou résolution manuelle.',
                'alerts' => $resolvedAlerts,
                'empty' => 'Aucune alerte résolue.',
            ])
        </div>
    </div>
</x-app-layout>
