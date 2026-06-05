<section class="app-table-panel">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="text-lg font-semibold text-slate-950">{{ $title }}</h2>
        <p class="app-muted">{{ $description }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50/70 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Produit</th>
                    <th class="px-5 py-3">Stock</th>
                    <th class="px-5 py-3">Déclenchement</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($alerts as $alert)
                    @php
                        $tone = match ($alert->status->value) {
                            'resolved' => 'bg-emerald-100 text-emerald-800',
                            'viewed' => 'bg-amber-100 text-amber-800',
                            default => 'bg-rose-100 text-rose-800',
                        };
                    @endphp
                    <tr>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-950">{{ $alert->product->name }}</p>
                            <p class="text-xs text-slate-500">{{ $alert->product->sku }}</p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ number_format($alert->product->current_stock, 0, ',', ' ') }} / seuil {{ number_format($alert->product->critical_stock, 0, ',', ' ') }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $alert->triggered_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $tone }}">{{ $alert->status->label() }}</span></td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('alerts.show', $alert) }}" class="app-secondary-action px-3 py-1.5 text-xs">Consulter</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">{{ $empty }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($alerts->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $alerts->links() }}
        </div>
    @endif
</section>
