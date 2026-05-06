<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <div class="d-flex align-items-center w-100">
                    <x-button as="link" href="{{ route('admin.mutu.indikator-mutu') }}" variant="secondary" size="sm" class="mr-3" icon="fas fa-arrow-left" title="Kembali" />
                    <h5 class="mb-0">{{ $indicator->title }}</h5>

                    @can('mutu.indikator-mutu.delete')
                        <x-button
                            variant="danger"
                            size="sm"
                            class="ml-auto"
                            title="Hapus Indikator"
                            icon="fas fa-trash"
                            onclick="confirm('Yakin ingin menghapus indikator ini? Seluruh data penilaian terkait juga akan terhapus.') || event.stopImmediatePropagation()"
                            wire:click="delete" />
                    @endcan
                </div>
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-row>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150">Kategori</th>
                            <td>: {{ $indicator->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Unit/Bidang</th>
                            <td>: {{ $indicator->unit->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Standar</th>
                            <td>: {{ $indicator->standard }}</td>
                        </tr>
                        <tr>
                            <th>Frekuensi</th>
                            <td>: {{ $indicator->frequency }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150">Tipe Input</th>
                            <td>: {{ $indicator->inputType->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>PJ</th>
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
                <p class="text-sm text-muted">{{ $indicator->definition ?: '-' }}</p>
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
                    <x-table.th title="Catatan" />
                    <x-table.th title="Input Oleh" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($records as $record)
                        @php
                            $achievement =
                                $record->denominator_value > 0
                                    ? round(($record->numerator_value / $record->denominator_value) * 100, 2)
                                    : 0;
                        @endphp

                        <x-table.tr>
                            <x-table.td>{{ carbon($record->recorded_date)->format('d-m-Y') }}</x-table.td>
                            <x-table.td class="text-center">{{ $record->numerator_value }}</x-table.td>
                            <x-table.td class="text-center">{{ $record->denominator_value }}</x-table.td>
                            <x-table.td class="text-center font-weight-bold">{{ $achievement }}%</x-table.td>
                            <x-table.td>{{ $record->notes ?: '-' }}</x-table.td>
                            <x-table.td>{{ $this->recorders->get($record->recorded_by)->nama ?? '-' }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
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
                            <td colspan="2"></td>
                        </tr>
                    </x-slot>
                @endif
            </x-table>
        </x-slot>
    </x-card>

    <livewire:pages.mutu.modal.input-record-indikator />
</div>
