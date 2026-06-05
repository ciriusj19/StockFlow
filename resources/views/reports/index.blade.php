<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="app-section-title">Exports</p>
            <h1 class="text-xl font-semibold text-gray-900">Rapports</h1>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-5 px-6 lg:px-8">
            <section class="app-table-panel">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Documents disponibles</h2>
                    <p class="text-sm text-gray-500">Les exports reflètent les données actuellement visibles dans StockFlow.</p>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach ($reports as $report)
                        <li class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-gray-900">{{ $report['title'] }}</h3>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $report['count'] }} lignes</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $report['description'] }}</p>
                            </div>
                            @can('reports.export')
                                <div class="flex gap-2">
                                    <a href="{{ route('reports.export', ['report' => $report['slug'], 'format' => 'pdf']) }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">PDF</a>
                                    <a href="{{ route('reports.export', ['report' => $report['slug'], 'format' => 'excel']) }}" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Excel</a>
                                </div>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>
