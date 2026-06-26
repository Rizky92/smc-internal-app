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

                window.addEventListener('update-chart-per-dept', (event) => {
                    const ctx = document.getElementById('chartPerDept').getContext('2d');
                    const { labels, data } = event.detail;
                    const colors = data.map(v => v >= 75 ? '#28a745' : '#dc3545');

                    if (chartPerDept) {
                        chartPerDept.data.labels = labels;
                        chartPerDept.data.datasets[0].data = data;
                        chartPerDept.data.datasets[0].backgroundColor = colors;
                        chartPerDept.update();
                    } else {
                        chartPerDept = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Rata-rata Capaian (%)',
                                    data: data,
                                    backgroundColor: colors,
                                    borderColor: colors.map(c => c),
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                scales: {
                                    x: {
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
                                            label: (context) => `Capaian: ${context.raw}%`,
                                        },
                                    },
                                    legend: {
                                        display: false,
                                    },
                                },
                            },
                        });
                    }
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
                                datasets: [{
                                    data: data,
                                    backgroundColor: colors,
                                    borderWidth: 1,
                                }],
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
                    const ctx = document.getElementById('chartTrend').getContext('2d');
                    const { labels, data } = event.detail;

                    if (chartTrend) {
                        chartTrend.data.labels = labels;
                        chartTrend.data.datasets[0].data = data;
                        chartTrend.update();
                    } else {
                        chartTrend = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Rata-rata Capaian (%)',
                                    data: data,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 3,
                                    pointBackgroundColor: (context) => {
                                        return context.dataset.data[context.dataIndex] >= 75 ? '#28a745' : '#dc3545';
                                    },
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
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
                                            label: (context) => `Capaian: ${context.raw}%`,
                                        },
                                    },
                                },
                            },
                        });
                    }
                });

                window.addEventListener('update-chart-per-kategori', (event) => {
                    const ctx = document.getElementById('chartPerKategori').getContext('2d');
                    const { labels, data } = event.detail;
                    const colors = data.map(v => v >= 75 ? '#28a745' : '#dc3545');

                    if (chartPerKategori) {
                        chartPerKategori.data.labels = labels;
                        chartPerKategori.data.datasets[0].data = data;
                        chartPerKategori.data.datasets[0].backgroundColor = colors;
                        chartPerKategori.update();
                    } else {
                        chartPerKategori = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Rata-rata Capaian (%)',
                                    data: data,
                                    backgroundColor: colors,
                                    borderColor: colors.map(c => c),
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
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
                                            label: (context) => `Capaian: ${context.raw}%`,
                                        },
                                    },
                                    legend: {
                                        display: false,
                                    },
                                },
                            },
                        });
                    }
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
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-xs text-muted text-uppercase font-weight-bold">Rata-rata Capaian</div>
                    <div class="h3 mb-0 font-weight-bold text-success mt-2">{{ $averageAchievement }}%</div>
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
                    <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Rata-rata Capaian per Departemen</h6>
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
                    <h6 class="mb-0"><i class="fas fa-chart-pie mr-2"></i>Distribusi Status Record</h6>
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
                    <h6 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Tren Capaian Bulanan (12 Bulan)</h6>
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
                    <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Rata-rata Capaian per Kategori</h6>
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
                    <h6 class="mb-0"><i class="fas fa-arrow-up mr-2 text-success"></i>5 Indikator Capaian Tertinggi</h6>
                </x-slot>
                <x-slot name="body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Indikator</th>
                                <th class="text-center">Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->topIndicators as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['title'] }}</td>
                                    <td class="text-center font-weight-bold text-success">{{ round((float) $item['avg_capaian'], 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data</td>
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
                    <h6 class="mb-0"><i class="fas fa-arrow-down mr-2 text-danger"></i>5 Indikator Capaian Terendah</h6>
                </x-slot>
                <x-slot name="body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Indikator</th>
                                <th class="text-center">Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->bottomIndicators as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['title'] }}</td>
                                    <td class="text-center font-weight-bold text-danger">{{ round((float) $item['avg_capaian'], 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot>
            </x-card>
        </div>
    </x-row>
</div>