<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script src="{{ asset('js/chart.js') }}"></script>
            <script>
                let indicatorChart = null;

                window.addEventListener('update-chart', (event) => {
                    const ctx = document.getElementById('indicatorChart').getContext('2d');
                    const { labels, data, standard } = event.detail;

                    if (indicatorChart) {
                        indicatorChart.data.labels = labels;
                        indicatorChart.data.datasets[0].data = data;
                        indicatorChart.data.datasets[1].data = new Array(labels.length).fill(standard);
                        indicatorChart.update();
                    } else {
                        indicatorChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Capaian (%)',
                                        data: data,
                                        borderColor: '#007bff',
                                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.3,
                                        pointRadius: 4,
                                        pointBackgroundColor: (context) => {
                                            const index = context.dataIndex;
                                            const value = context.dataset.data[index];
                                            return value >= standard ? '#28a745' : '#dc3545';
                                        },
                                    },
                                    {
                                        label: 'Standar (' + standard + '%)',
                                        data: new Array(labels.length).fill(standard),
                                        borderColor: '#ffc107',
                                        borderDash: [5, 5],
                                        borderWidth: 2,
                                        pointRadius: 0,
                                        fill: false,
                                    },
                                ],
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

                function loadData(e) {
                    let { id, date } = e.dataset;
                    Livewire.emit('input-record', id, date);
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <div class="d-flex align-items-center w-100">
                    <x-button as="link" href="{{ route('admin.mutu.indikator-mutu') }}" variant="secondary" size="sm" class="mr-3" icon="fas fa-arrow-left" title="Kembali" />
                    <h5 class="mb-0">{{ $indicator->profile->title ?? '-' }}</h5>

                    <div class="ml-auto d-flex" style="gap: 0.5rem">
                        @can('mutu.indikator-mutu.update')
                            <x-button variant="warning" size="sm" title="Edit Mapping" icon="fas fa-edit" wire:click="$emit('prepare', {{ $indicatorId }})" />
                        @endcan

                        @can('mutu.indikator-mutu.delete')
                            <x-button
                                variant="danger"
                                size="sm"
                                title="Hapus Mapping"
                                icon="fas fa-trash"
                                onclick="confirm('Yakin ingin menghapus mapping indikator ini dari unit? Data penilaian terkait akan tetap tersimpan.') || event.stopImmediatePropagation()"
                                wire:click="delete" />
                        @endcan
                    </div>
                </div>
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-row>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150">Kategori</th>
                            <td>: {{ $indicator->profile->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Departemen</th>
                            <td>: {{ $indicator->departemen->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Standar</th>
                            <td>: {{ $indicator->profile->standard ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Frekuensi</th>
                            <td>: {{ $indicator->profile->frequency ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150">Tipe Input</th>
                            <td>: {{ $indicator->profile->inputType->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>PJ Unit</th>
                            <td>: {{ $indicator->person_in_charge }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                :
                                <x-badge :variant="$indicator->status === 'active' ? 'success' : 'danger'">
                                    {{ $indicator->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                        </tr>
                    </table>
                </div>
            </x-row>
            <hr />
            <div class="mt-3 p-4">
                <h6>Definisi Operasional :</h6>
                <p class="text-sm text-muted">{{ $indicator->profile->definition ?: '-' }}</p>
            </div>
        </x-slot>
    </x-card>

    <x-card class="mt-3">
        <x-slot name="header">
            <h6 class="mb-0">
                <i class="fas fa-chart-line mr-2"></i>
                Grafik Tren Capaian
            </h6>
        </x-slot>
        <x-slot name="body">
            <div wire:ignore style="height: 300px">
                <canvas id="indicatorChart"></canvas>
            </div>
        </x-slot>
    </x-card>

    <x-card class="mt-3" use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <x-filter.range-date />
                <x-button variant="primary" size="sm" title="Tambah Penilaian" icon="fas fa-plus" class="ml-auto" wire:click="$emit('input-record', {{ $indicatorId }})" />
                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Tanggal" />
                    <x-table.th title="Numerator" />
                    <x-table.th title="Denominator" />
                    <x-table.th title="Capaian (%)" />
                    <x-table.th title="Status" />
                    <x-table.th title="Catatan" />
                    <x-table.th title="Petugas" />
                    <x-table.th title="Log" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($records as $record)
                        @php
                            $achievement =
                                $record->denominator_value > 0
                                    ? round(($record->numerator_value / $record->denominator_value) * 100, 2)
                                    : 0;

                            $statusVariant =
                                [
                                    'draft' => 'secondary',
                                    'submitted' => 'info',
                                    'approved' => 'success',
                                    'approved_with_correction' => 'primary',
                                    'rejected' => 'danger',
                                ][$record->status ?? 'draft'] ?? 'secondary';

                            $statusLabel =
                                [
                                    'draft' => 'Draft',
                                    'submitted' => 'Submitted',
                                    'approved' => 'Approved',
                                    'approved_with_correction' => 'Approved w/ Correction',
                                    'rejected' => 'Rejected',
                                ][$record->status ?? 'draft'] ?? 'Draft';
                        @endphp

                        <x-table.tr>
                            <x-table.td :clickable="true" data-id="{{ $indicatorId }}" data-date="{{ $record->recorded_date }}">
                                {{ carbon($record->recorded_date)->format('d-m-Y') }}
                            </x-table.td>
                            <x-table.td class="text-center">{{ $record->numerator_value }}</x-table.td>
                            <x-table.td class="text-center">{{ $record->denominator_value }}</x-table.td>
                            <x-table.td class="text-center font-weight-bold">{{ $achievement }}%</x-table.td>
                            <x-table.td class="text-center">
                                <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
                            </x-table.td>
                            <x-table.td>{{ $record->notes ?: '-' }}</x-table.td>
                            <x-table.td>{{ $this->recorders->get($record->recorded_by)->nama ?? '-' }}</x-table.td>
                            <x-table.td class="text-center">
                                @if ($record->status === 'approved_with_correction')
                                    <x-button
                                        variant="info"
                                        size="xs"
                                        icon="fas fa-history"
                                        title="Riwayat Koreksi"
                                        wire:click="$emit('view-audit-log', {{ $indicatorId }}, '{{ $record->recorded_date }}')" />
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" padding />
                    @endforelse
                </x-slot>
                @if ($records->isNotEmpty())
                    <x-slot name="footer">
                        <tr class="bg-light font-weight-bold">
                            <td>TOTAL / RATA-RATA</td>
                            <td class="text-center">{{ $records->sum('numerator_value') }}</td>
                            <td class="text-center">{{ $records->sum('denominator_value') }}</td>
                            <td class="text-center text-primary">
                                @php
                                    $totalNum = $records->sum('numerator_value');
                                    $totalDen = $records->sum('denominator_value');
                                    $totalAch = $totalDen > 0 ? round(($totalNum / $totalDen) * 100, 2) : 0;
                                @endphp

                                {{ $totalAch }}%
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </x-slot>
                @endif
            </x-table>
        </x-slot>
    </x-card>

    <livewire:pages.mutu.modal.input-record-indikator />
    <livewire:pages.mutu.modal.input-indikator-mutu />
    <livewire:pages.mutu.modal.view-audit-log-indikator />
</div>
