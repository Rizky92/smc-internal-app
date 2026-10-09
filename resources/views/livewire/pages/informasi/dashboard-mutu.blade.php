<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script src="{{ asset('js/chart.js') }}"></script>
            <script>
                let chartPerDept = null;
                let chartStatus = null;
                let chartTrend = null;
                let chartPerKategori = null;

                // Agregat lintas indikator = % indikator tercapai dari yang bisa dinilai (dihitung di server).
                // `null` = grup/bulan tanpa indikator yang bisa dinilai: tanpa batang/titik dan label diberi "(-)".
                const persenTercapai = {
                    labels: (labels, data) => labels.map((label, i) => (data[i] === null ? `${label} (-)` : label)),
                    tooltip: (detail) => (context) => {
                        const i = context.dataIndex;
                        return `${detail.tercapai[i]} dari ${detail.dinilai[i]} indikator tercapai (${context.raw}%)`;
                    },
                    options: (detail, indexAxis) => ({
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: indexAxis,
                        scales: {
                            [indexAxis === 'y' ? 'x' : 'y']: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: (value) => value + '%',
                                },
                            },
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: persenTercapai.tooltip(detail),
                                },
                            },
                            legend: {
                                display: false,
                            },
                        },
                    }),
                };

                const renderBarTercapai = (chart, canvasId, detail, indexAxis) => {
                    const config = {
                        type: 'bar',
                        data: {
                            labels: persenTercapai.labels(detail.labels, detail.data),
                            datasets: [
                                {
                                    label: '% Indikator Tercapai',
                                    data: detail.data,
                                    backgroundColor: '#007bff',
                                    borderWidth: 1,
                                },
                            ],
                        },
                        options: persenTercapai.options(detail, indexAxis),
                    };

                    if (chart) {
                        chart.data = config.data;
                        chart.options = config.options;
                        chart.update();

                        return chart;
                    }

                    return new Chart(document.getElementById(canvasId).getContext('2d'), config);
                };

                window.addEventListener('update-chart-per-dept', (event) => {
                    chartPerDept = renderBarTercapai(chartPerDept, 'chartPerDept', event.detail, 'y');
                });

                window.addEventListener('update-chart-status', (event) => {
                    const ctx = document.getElementById('chartStatus').getContext('2d');
                    const { labels, data, colors } = event.detail;

                    if (chartStatus) {
                        chartStatus.data.labels = labels;
                        chartStatus.data.datasets[0].data = data;
                        chartStatus.data.datasets[0].backgroundColor = colors;
                        chartStatus.update();
                    } else {
                        chartStatus = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        data: data,
                                        backgroundColor: colors,
                                        borderWidth: 1,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => {
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                                return `${context.label}: ${context.raw} (${pct}%)`;
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    }
                });

                window.addEventListener('update-chart-trend', (event) => {
                    const detail = event.detail;
                    const config = {
                        type: 'line',
                        data: {
                            labels: detail.labels,
                            datasets: [
                                {
                                    label: '% Indikator Tercapai',
                                    data: detail.data,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0,
                                    spanGaps: false,
                                    pointRadius: 4,
                                },
                            ],
                        },
                        options: persenTercapai.options(detail, 'x'),
                    };

                    if (chartTrend) {
                        chartTrend.data = config.data;
                        chartTrend.options = config.options;
                        chartTrend.update();
                    } else {
                        chartTrend = new Chart(document.getElementById('chartTrend').getContext('2d'), config);
                    }
                });

                window.addEventListener('update-chart-per-kategori', (event) => {
                    chartPerKategori = renderBarTercapai(chartPerKategori, 'chartPerKategori', event.detail, 'x');
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const handleSelect2Change = (id, model) => {
                        const el = document.querySelector(id);
                        if (!el) return;
                        $(el).on('select2:select', function () {
                            const compId = this.closest('[wire\\:id]').getAttribute('wire:id');
                            const comp = window.Livewire.find(compId);
                            if (comp) {
                                comp[model] = $(this).val();
                                comp.searchData();
                            }
                        });
                    };

                    handleSelect2Change('#departemen', 'depId');
                    handleSelect2Change('#kategori', 'kategoriId');
                });
            </script>
        @endpush
    @endonce

    <x-row-col-flex class="mt-2 mb-3">
        <x-filter.range-date />
        <div class="ml-2">
            <x-filter.select2 name="Departemen" model="depId" livewire :options="$this->departemen" placeholder="SEMUA DEPARTEMEN" width="16rem" />
        </div>
        <div class="ml-2">
            <x-filter.select2 name="Kategori" model="kategoriId" livewire :options="$this->kategori" placeholder="SEMUA KATEGORI" width="16rem" />
        </div>
        <x-filter.button-reset-filters class="ml-auto" />
    </x-row-col-flex>

    <x-row>
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-xs text-muted text-uppercase font-weight-bold">Indikator Aktif</div>
                    <div class="h3 mb-0 font-weight-bold text-primary mt-2">{{ $totalActiveIndicators }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-xs text-muted text-uppercase font-weight-bold">Record Bulan Ini</div>
                    <div class="h3 mb-0 font-weight-bold text-info mt-2">{{ $monthlyRecordsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-secondary">
                <div class="card-body">
                    <div class="text-xs text-muted text-uppercase font-weight-bold">Indikator Tercapai</div>

                    @php
                        $keterangan = collect([
                            $ringkasanTercapai['persen'] === null ? 'Belum ada indikator yang bisa dinilai' : "{$ringkasanTercapai['tercapai']} dari {$ringkasanTercapai['dinilai']} indikator tercapai",
                            $ringkasanTercapai['belum'] > 0 ? "{$ringkasanTercapai['belum']} belum bisa dinilai" : null,
                        ])
                            ->filter()
                            ->implode('; ');
                    @endphp

                    <div class="h3 mb-0 font-weight-bold {{ $ringkasanTercapai['persen'] === null ? 'text-muted' : 'text-dark' }} mt-2">
                        {{ $ringkasanTercapai['persen'] === null ? '-' : $ringkasanTercapai['persen'] . '%' }}
                    </div>
                    <div class="text-xs text-muted">{{ $keterangan }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-xs text-muted text-uppercase font-weight-bold">Pending Validasi</div>
                    <div class="h3 mb-0 font-weight-bold text-warning mt-2">{{ $pendingValidationCount }}</div>
                </div>
            </div>
        </div>
    </x-row>

    <x-row class="mt-4">
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        % Indikator Tercapai per Departemen
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <div wire:ignore style="height: 320px">
                        <canvas id="chartPerDept"></canvas>
                    </div>
                </x-slot>
            </x-card>
        </div>
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Distribusi Status Record
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <div wire:ignore style="height: 320px">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </x-slot>
            </x-card>
        </div>
    </x-row>

    <x-row class="mt-3">
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Tren % Indikator Tercapai (12 Bulan)
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <div wire:ignore style="height: 280px">
                        <canvas id="chartTrend"></canvas>
                    </div>
                </x-slot>
            </x-card>
        </div>
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        % Indikator Tercapai per Kategori
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <div wire:ignore style="height: 280px">
                        <canvas id="chartPerKategori"></canvas>
                    </div>
                </x-slot>
            </x-card>
        </div>
    </x-row>

    <x-row class="mt-3">
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-arrow-up mr-2 text-success"></i>
                        5 Indikator Capaian Tertinggi
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Indikator</th>
                                <th class="text-center">Capaian</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->topIndicators as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['title'] }}</td>
                                    <td class="text-center font-weight-bold">{{ $item['avg_capaian'] === null ? '-' : round((float) $item['avg_capaian'], 2) . '%' }}</td>
                                    <td class="text-center">
                                        <x-badge :variant="\App\Models\Quality\QualityIndicatorProfile::ACHIEVEMENT_BADGES[$item['status']][1]">
                                            {{ \App\Models\Quality\QualityIndicatorProfile::ACHIEVEMENT_BADGES[$item['status']][0] }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot>
            </x-card>
        </div>
        <div class="col-md-6">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">
                        <i class="fas fa-arrow-down mr-2 text-danger"></i>
                        5 Indikator Capaian Terendah
                    </h6>
                </x-slot>
                <x-slot name="body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Indikator</th>
                                <th class="text-center">Capaian</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->bottomIndicators as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['title'] }}</td>
                                    <td class="text-center font-weight-bold">{{ $item['avg_capaian'] === null ? '-' : round((float) $item['avg_capaian'], 2) . '%' }}</td>
                                    <td class="text-center">
                                        <x-badge :variant="\App\Models\Quality\QualityIndicatorProfile::ACHIEVEMENT_BADGES[$item['status']][1]">
                                            {{ \App\Models\Quality\QualityIndicatorProfile::ACHIEVEMENT_BADGES[$item['status']][0] }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot>
            </x-card>
        </div>
    </x-row>
</div>
