import './bootstrap';

let mountedCharts = [];
let chartRequest = 0;

const chartPalette = {
    blue: '#2563eb',
    blueDeep: '#1d4ed8',
    blueSoft: '#60a5fa',
    indigo: '#4338ca',
    emerald: '#059669',
    rose: '#e11d48',
    orange: '#f97316',
    amber: '#d97706',
    amberSoft: '#fbbf24',
    sky: '#0ea5e9',
    gray: '#64748b',
    slate: '#0f172a',
    muted: '#e5e7eb',
};

async function mountCharts() {
    const request = ++chartRequest;

    mountedCharts.forEach((chart) => chart.destroy());
    mountedCharts = [];

    const elements = [...document.querySelectorAll('[data-chart-kind]')];

    if (elements.length === 0) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');

    if (request !== chartRequest) {
        return;
    }

    const compactNumber = (value) => new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value) || 0);

    const sharedChart = {
        animations: { enabled: false },
        background: 'transparent',
        foreColor: chartPalette.gray,
        fontFamily: 'Figtree, sans-serif',
        parentHeightOffset: 0,
        toolbar: { show: false },
    };

    const sharedGrid = {
        borderColor: '#edf1f7',
        padding: { bottom: 0, left: 8, right: 14, top: 8 },
        strokeDashArray: 5,
    };

    const sharedTooltip = {
        fillSeriesColor: false,
        marker: { show: false },
        style: { fontSize: '12px' },
        theme: 'light',
    };

    const sharedXAxis = {
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            hideOverlappingLabels: true,
            trim: true,
            style: { colors: chartPalette.gray, fontSize: '12px', fontWeight: 500 },
        },
        tooltip: { enabled: false },
    };

    const sharedYAxis = {
        labels: {
            minWidth: 0,
            style: { colors: chartPalette.gray, fontSize: '12px', fontWeight: 500 },
        },
    };

    const sharedLegend = {
        fontSize: '12px',
        fontWeight: 600,
        markers: { height: 8, radius: 8, width: 8 },
        position: 'top',
    };

    const horizontalBar = {
        bar: {
            backgroundBarColors: ['#f3f4f8'],
            backgroundBarOpacity: 1,
            backgroundBarRadius: 14,
            borderRadius: 14,
            borderRadiusApplication: 'end',
            horizontal: true,
            barHeight: '44%',
        },
    };

    const verticalBar = {
        bar: {
            borderRadius: 12,
            borderRadiusApplication: 'end',
            columnWidth: '38%',
        },
    };

    const numberAxis = {
        ...sharedYAxis,
        labels: {
            ...sharedYAxis.labels,
            formatter: (value) => compactNumber(value),
        },
    };

    const render = (element, options) => {
        if (!document.contains(element)) {
            return;
        }

        const chart = new ApexCharts(element, options);
        chart.render();
        mountedCharts.push(chart);
    };

    const total = (values = []) => values.reduce((sum, value) => sum + (Number(value) || 0), 0);

    const toPercentSeries = (values = []) => {
        const seriesTotal = total(values);

        if (seriesTotal === 0) {
            return values.map(() => 0);
        }

        return values.map((value) => Math.round(((Number(value) || 0) / seriesTotal) * 100));
    };

    const highlightLargest = (values = [], accent = chartPalette.blue) => {
        const numericValues = values.map((value) => Math.abs(Number(value) || 0));
        const largest = Math.max(...numericValues, 0);

        return numericValues.map((value) => value === largest && largest > 0 ? accent : chartPalette.muted);
    };

    const riskColors = (values = []) => values.map((value) => {
        const score = Number(value) || 0;

        if (score >= 75) {
            return chartPalette.rose;
        }

        if (score >= 50) {
            return chartPalette.orange;
        }

        if (score >= 35) {
            return chartPalette.amber;
        }

        return chartPalette.emerald;
    });

    const solidFill = (opacity = 1) => ({
        opacity,
        type: 'solid',
    });

    const chartOptions = {
        'risk-distribution': (data) => ({
            chart: { ...sharedChart, height: 255, type: 'radialBar' },
            colors: [chartPalette.emerald, chartPalette.amberSoft, chartPalette.orange, chartPalette.rose],
            dataLabels: { enabled: false },
            labels: data.labels,
            legend: {
                ...sharedLegend,
                formatter: (label, opts) => `${label} ${compactNumber(data.series?.[opts.seriesIndex] || 0)}`,
                position: 'bottom',
            },
            plotOptions: {
                radialBar: {
                    hollow: { margin: 5, size: '34%' },
                    track: {
                        background: '#e5e7eb',
                        margin: 8,
                        strokeWidth: '100%',
                    },
                    dataLabels: {
                        name: { color: chartPalette.gray, fontSize: '12px', fontWeight: 700, offsetY: 20 },
                        total: {
                            show: true,
                            color: chartPalette.slate,
                            fontSize: '24px',
                            fontWeight: 800,
                            label: 'Produits',
                            formatter: () => compactNumber(total(data.series || [])),
                        },
                        value: { show: false },
                    },
                },
            },
            series: toPercentSeries(data.series || []),
            stroke: { lineCap: 'round' },
            tooltip: {
                ...sharedTooltip,
                y: {
                    formatter: (value, opts) => `${compactNumber(data.series?.[opts.seriesIndex] || value)} produit(s)`,
                },
            },
        }),
        'movement-activity': (data) => ({
            chart: { ...sharedChart, height: 255, type: 'area', zoom: { enabled: false } },
            colors: [chartPalette.emerald, chartPalette.rose],
            grid: sharedGrid,
            legend: sharedLegend,
            series: [
                { name: 'Entrées', data: data.entries },
                { name: 'Sorties', data: data.exits },
            ],
            fill: solidFill([0.16, 0.12]),
            markers: { hover: { sizeOffset: 4 }, size: 0, strokeColors: '#ffffff', strokeWidth: 3 },
            stroke: { curve: 'smooth', lineCap: 'round', width: [3.5, 3.5] },
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: { ...sharedXAxis, categories: data.labels },
            yaxis: numberAxis,
        }),
        'top-risks': (data) => ({
            chart: { ...sharedChart, height: 260, type: 'bar' },
            colors: riskColors(data.series || []),
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Risque', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} / 100` } },
            xaxis: { ...sharedXAxis, categories: data.labels, max: 100 },
            yaxis: sharedYAxis,
        }),
        'forecast-days': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'bar' },
            colors: data.colors?.length ? data.colors : [chartPalette.orange],
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            legend: { show: false },
            noData: { text: 'Aucune rupture estimable' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Jours restants', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} jour(s)` } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'forecast-recommended': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'bar' },
            colors: highlightLargest(data.series || [], chartPalette.blue),
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            noData: { text: 'Aucune recommandation active' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'À commander', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'forecast-priority': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'bar' },
            colors: data.colors?.length ? data.colors : [chartPalette.blue],
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            noData: { text: 'Aucune priorité active' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Quantité conseillée', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} unité(s)` } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'top-consumption': (data) => ({
            chart: { ...sharedChart, height: 280, type: 'bar' },
            colors: highlightLargest(data.series || [], chartPalette.rose),
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            noData: { text: 'Aucune sortie recente' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Sorties', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'product-stock': (data) => ({
            chart: { ...sharedChart, height: 310, type: 'area', zoom: { enabled: false } },
            colors: [chartPalette.blue, chartPalette.amber],
            dataLabels: { enabled: false },
            fill: solidFill([0.14, 0.08]),
            grid: sharedGrid,
            legend: sharedLegend,
            markers: { hover: { sizeOffset: 3 }, size: [4, 0], strokeColors: '#ffffff', strokeWidth: 2 },
            series: [
                { name: 'Stock reel', data: data.stock },
                { name: 'Seuil critique', data: data.threshold },
            ],
            stroke: { curve: 'smooth', dashArray: [0, 6], lineCap: 'round', width: [4, 2] },
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: { ...sharedXAxis, categories: data.labels },
            yaxis: numberAxis,
        }),
        'inventory-differences': (data) => ({
            chart: { ...sharedChart, height: 315, type: 'bar' },
            colors: data.colors?.length ? data.colors : [chartPalette.emerald, chartPalette.rose],
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            legend: { show: false },
            noData: { text: 'Aucun ecart valide' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Ecart', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'analytics-category-risk': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'bar' },
            colors: riskColors(data.series || []),
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            noData: { text: 'Aucune compilation disponible' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Risque moyen', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} / 100` } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
                max: 100,
            },
            yaxis: sharedYAxis,
        }),
        'analytics-supplier-dependency': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'bar' },
            colors: highlightLargest(data.series || [], chartPalette.orange),
            dataLabels: { enabled: false },
            fill: solidFill(),
            grid: sharedGrid,
            noData: { text: 'Aucune dependance fournisseur' },
            plotOptions: { bar: { ...horizontalBar.bar, distributed: true } },
            series: [{ name: 'Produits critiques', data: data.series }],
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} produit(s)` } },
            xaxis: {
                ...sharedXAxis,
                categories: data.labels,
                labels: { ...sharedXAxis.labels, formatter: (value) => compactNumber(value) },
            },
            yaxis: sharedYAxis,
        }),
        'analytics-inventory-reliability': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'radialBar' },
            colors: [chartPalette.emerald, chartPalette.blue, chartPalette.sky],
            dataLabels: { enabled: false },
            fill: solidFill(),
            noData: { text: 'Aucun inventaire valide' },
            labels: data.labels,
            plotOptions: {
                radialBar: {
                    hollow: { margin: 4, size: '26%' },
                    track: { background: '#e5e7eb', margin: 7, strokeWidth: '100%' },
                    dataLabels: {
                        name: { color: chartPalette.gray, fontSize: '12px', fontWeight: 700 },
                        value: {
                            color: chartPalette.slate,
                            fontSize: '24px',
                            fontWeight: 800,
                            formatter: (value) => `${compactNumber(value)}%`,
                        },
                    },
                },
            },
            series: data.series,
            stroke: { lineCap: 'round' },
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} / 100` } },
        }),
        'analytics-data-quality': (data) => ({
            chart: { ...sharedChart, height: 250, type: 'radialBar' },
            colors: [chartPalette.blue],
            dataLabels: { enabled: false },
            fill: solidFill(),
            labels: data.labels,
            noData: { text: 'Aucune compilation disponible' },
            plotOptions: {
                radialBar: {
                    hollow: { size: '64%' },
                    track: { background: '#e5e7eb', strokeWidth: '100%' },
                    dataLabels: {
                        name: { color: chartPalette.gray, fontSize: '12px', fontWeight: 700 },
                        value: {
                            color: chartPalette.slate,
                            fontSize: '34px',
                            fontWeight: 800,
                            formatter: (value) => `${compactNumber(value)}%`,
                        },
                    },
                },
            },
            series: data.series,
            stroke: { lineCap: 'round' },
            tooltip: { ...sharedTooltip, y: { formatter: (value) => `${compactNumber(value)} %` } },
        }),
        'analytics-alert-trend': (data) => ({
            chart: { ...sharedChart, height: 290, type: 'area', zoom: { enabled: false } },
            colors: [chartPalette.blue, chartPalette.rose],
            dataLabels: { enabled: false },
            fill: solidFill([0.14, 0.1]),
            grid: sharedGrid,
            legend: sharedLegend,
            markers: { hover: { sizeOffset: 4 }, size: 0, strokeColors: '#ffffff', strokeWidth: 3 },
            noData: { text: 'Aucun historique de compilation' },
            series: data.series || [
                { name: 'Alertes ouvertes', data: data.openAlerts || [] },
                { name: 'Produits critiques', data: data.criticalProducts || [] },
            ],
            stroke: { curve: 'smooth', lineCap: 'round', width: [3.5, 3.5] },
            tooltip: { ...sharedTooltip, y: { formatter: (value) => compactNumber(value) } },
            xaxis: { ...sharedXAxis, categories: data.labels },
            yaxis: numberAxis,
        }),
    };

    elements.forEach((element) => {
        const makeOptions = chartOptions[element.dataset.chartKind];

        if (!makeOptions) {
            return;
        }

        render(element, makeOptions(JSON.parse(element.dataset.chart || '{}')));
    });
}

document.addEventListener('DOMContentLoaded', mountCharts);
document.addEventListener('livewire:navigated', mountCharts);
