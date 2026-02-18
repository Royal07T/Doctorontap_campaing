<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" wire:ignore>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            Vitals Trend ({{ $days }}-Day)
        </h3>
        <div class="flex space-x-1">
            @foreach([7 => '7d', 14 => '14d', 30 => '30d'] as $d => $label)
            <button wire:click="setDays({{ $d }})"
                    class="px-2 py-1 text-xs rounded-md {{ $days === $d ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    <div x-data="vitalsChartComponent(@js($chartData))" x-init="initChart()">
        {{-- Tab selector --}}
        <div class="flex space-x-1 mb-4 border-b border-gray-200 pb-2">
            <template x-for="tab in tabs" :key="tab.key">
                <button @click="switchTab(tab.key)"
                        :class="activeTab === tab.key ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 text-xs font-medium border-b-2 rounded-t-lg transition-colors"
                        x-text="tab.label">
                </button>
            </template>
        </div>

        <div class="relative" style="height: 260px;">
            <canvas x-ref="chartCanvas"></canvas>
        </div>

        @if(empty($chartData['labels']))
        <div class="absolute inset-0 flex items-center justify-center">
            <p class="text-sm text-gray-400">No vitals data in this period</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vitalsChartComponent', (chartData) => ({
        chart: null,
        activeTab: 'bp',
        data: chartData,
        tabs: [
            { key: 'bp', label: 'Blood Pressure' },
            { key: 'hr', label: 'Heart Rate' },
            { key: 'spo2', label: 'SpO2' },
            { key: 'temp', label: 'Temperature' },
            { key: 'sugar', label: 'Blood Sugar' },
        ],

        initChart() {
            this.renderChart();
        },

        switchTab(key) {
            this.activeTab = key;
            this.renderChart();
        },

        getDatasets() {
            const colors = {
                purple: { bg: 'rgba(147, 51, 234, 0.1)', border: '#9333EA' },
                blue: { bg: 'rgba(59, 130, 246, 0.1)', border: '#3B82F6' },
                red: { bg: 'rgba(239, 68, 68, 0.1)', border: '#EF4444' },
                emerald: { bg: 'rgba(16, 185, 129, 0.1)', border: '#10B981' },
                amber: { bg: 'rgba(245, 158, 11, 0.1)', border: '#F59E0B' },
            };

            switch (this.activeTab) {
                case 'bp':
                    return [
                        { label: 'Systolic', data: this.data.systolic, ...this.lineOpts(colors.red) },
                        { label: 'Diastolic', data: this.data.diastolic, ...this.lineOpts(colors.blue) },
                    ];
                case 'hr':
                    return [{ label: 'Heart Rate (bpm)', data: this.data.heartRate, ...this.lineOpts(colors.purple) }];
                case 'spo2':
                    return [{ label: 'SpO2 (%)', data: this.data.spo2, ...this.lineOpts(colors.emerald) }];
                case 'temp':
                    return [{ label: 'Temp (°C)', data: this.data.temperature, ...this.lineOpts(colors.amber) }];
                case 'sugar':
                    return [{ label: 'Blood Sugar (mg/dL)', data: this.data.bloodSugar, ...this.lineOpts(colors.purple) }];
            }
        },

        lineOpts(color) {
            return {
                borderColor: color.border,
                backgroundColor: color.bg,
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2,
            };
        },

        renderChart() {
            if (this.chart) this.chart.destroy();

            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.data.labels,
                    datasets: this.getDatasets(),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleFont: { size: 11 },
                            bodyFont: { size: 11 },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
                    },
                },
            });
        },
    }));
});
</script>
@endpush
