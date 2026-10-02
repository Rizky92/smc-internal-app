<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                <x-filter.label constant-width>Status :</x-filter.label>
                <x-filter.select
                    model="statusFilter"
                    :options="[
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_SUBMITTED => 'Submitted (Perlu Validasi)',
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_APPROVED => 'Approved (Disetujui)',
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_APPROVED_WITH_CORRECTION => 'Approved w/ Correction',
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_REJECTED => 'Rejected (Ditolak)',
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_DRAFT => 'Draft',
                        \App\Models\Quality\QualityIndicatorRecord::STATUS_VOIDED => 'Voided (Dibatalkan)',
                        \App\Livewire\Pages\Mutu\ValidasiData::FILTER_KOREKSI => 'Koreksi Diajukan',
                        'all' => 'Semua Status',
                    ]" />
            </x-row-col-flex>

            <x-row-col-flex class="mt-2" style="gap: 1rem; flex-wrap: wrap">
                <x-filter.label class="mr-2">Departemen :</x-filter.label>
                <x-filter.select2 name="Departemen" model="depId" livewire :options="$this->departemen" placeholder="SEMUA" />
                <div class="ml-md-auto">
                    <x-filter.range-date />
                </div>
            </x-row-col-flex>

            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>

        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="No." />
                    <x-table.th title="Tanggal" />
                    <x-table.th title="Indikator" />
                    <x-table.th title="Departemen" />
                    <x-table.th title="Numerator" />
                    <x-table.th title="Denominator" />
                    <x-table.th title="Capaian" />
                    <x-table.th title="Petugas" />
                    <x-table.th title="Catatan" />
                    <x-table.th title="Status" />
                    <x-table.th title="Aksi" />
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
                            <x-table.td>{{ $loop->iteration + ($records->currentPage() - 1) * $records->perPage() }}</x-table.td>
                            <x-table.td>{{ carbon($record->recorded_date)->format('d-m-Y') }}</x-table.td>
                            <x-table.td>
                                <span class="font-weight-bold">{{ $record->indicator->profile->title ?? '-' }}</span>
                                <br />
                                <small class="text-muted">{{ $record->indicator->profile->category->name ?? '-' }}</small>
                            </x-table.td>
                            <x-table.td>{{ $record->indicator->departemen->nama ?? '-' }}</x-table.td>
                            <x-table.td class="text-center">{{ $record->numerator_value }}</x-table.td>
                            <x-table.td class="text-center">{{ $record->denominator_value }}</x-table.td>
                            <x-table.td class="text-center font-weight-bold text-primary">{{ $achievement }}%</x-table.td>
                            <x-table.td>{{ $this->recorders->get($record->recorded_by)->nama ?? '-' }}</x-table.td>
                            <x-table.td>
                                <span class="text-sm" title="{{ $record->notes }}">{{ Str::limit($record->notes ?: '-', 30) }}</span>
                                @if ($record->pendingCorrection)
                                    <div class="text-xs text-warning mt-1">
                                        <i class="fas fa-hourglass-half"></i>
                                        Koreksi diajukan: {{ $record->pendingCorrection->numerator_value }} / {{ $record->pendingCorrection->denominator_value }}
                                        @if ((string) $record->pendingCorrection->notes !== (string) $record->notes)
                                            <br />
                                            Catatan: {{ Str::limit($record->pendingCorrection->notes ?: '-', 30) }}
                                        @endif

                                        <br />
                                        <span class="text-muted">Alasan: {{ $record->pendingCorrection->reason }}</span>
                                    </div>
                                @endif
                            </x-table.td>
                            <x-table.td class="text-center">
                                <x-badge :variant="$record->statusBadgeVariant()">{{ $record->statusLabel() }}</x-badge>
                            </x-table.td>
                            <x-table.td>
                                <div class="d-flex" style="gap: 0.25rem">
                                    @if ($record->status === \App\Models\Quality\QualityIndicatorRecord::STATUS_SUBMITTED)
                                        @can('mutu.validasi-data.approve')
                                            <x-button
                                                variant="success"
                                                size="xs"
                                                icon="fas fa-check"
                                                title="Setuju"
                                                wire:click="approve({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan

                                        @can('mutu.validasi-data.approve')
                                            <x-button
                                                variant="primary"
                                                size="xs"
                                                icon="fas fa-pen"
                                                title="Edit & Setuju"
                                                wire:click="editAndApprove({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan

                                        @can('mutu.validasi-data.reject')
                                            <x-button
                                                variant="danger"
                                                size="xs"
                                                icon="fas fa-times"
                                                title="Tolak"
                                                wire:click="bukaFormAlasan('reject', {{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan
                                    @elseif (in_array($record->status, [\App\Models\Quality\QualityIndicatorRecord::STATUS_APPROVED, \App\Models\Quality\QualityIndicatorRecord::STATUS_APPROVED_WITH_CORRECTION, \App\Models\Quality\QualityIndicatorRecord::STATUS_REJECTED], true))
                                        @canany(['mutu.validasi-data.approve', 'mutu.validasi-data.reject'])
                                            <x-button
                                                variant="warning"
                                                size="xs"
                                                icon="fas fa-undo"
                                                title="Batal Validasi"
                                                wire:click="resetStatus({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />

                                            @if (in_array($record->status, \App\Models\Quality\QualityIndicatorRecord::STATUSES_BISA_DIVOID, true))
                                                <x-button
                                                    variant="dark"
                                                    size="xs"
                                                    icon="fas fa-ban"
                                                    title="Void"
                                                    wire:click="bukaFormAlasan('void', {{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                            @endif
                                        @endcanany
                                    @elseif (! $record->punyaRiwayat())
                                        <span class="text-muted text-xs">-</span>
                                    @endif

                                    @if ($record->pendingCorrection)
                                        @can('mutu.validasi-data.approve')
                                            <x-button
                                                variant="success"
                                                size="xs"
                                                icon="fas fa-check-double"
                                                title="Setujui Koreksi"
                                                wire:click="setujuiKoreksi({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan

                                        @can('mutu.validasi-data.reject')
                                            <x-button
                                                variant="danger"
                                                size="xs"
                                                icon="fas fa-times-circle"
                                                title="Tolak Koreksi"
                                                wire:click="bukaFormAlasan('tolakKoreksi', {{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan
                                    @endif

                                    @if ($record->punyaRiwayat())
                                        <x-button
                                            variant="info"
                                            size="xs"
                                            icon="fas fa-history"
                                            title="Riwayat"
                                            wire:click="$emit('view-audit-log', {{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                    @endif
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$records" />
        </x-slot>
    </x-card>

    <x-modal id="modal-alasan-validasi" :title="$judulFormAlasan" livewire>
        <x-slot name="body">
            <x-form id="form-alasan-validasi" wire:submit.prevent="simpanAlasan">
                <div class="form-group">
                    <label>
                        Alasan
                        <span class="text-danger">*</span>
                    </label>
                    <textarea wire:model.defer="alasan" class="form-control form-control-sm" rows="3" placeholder="Contoh: Denominator tidak sesuai register pasien"></textarea>
                    <x-form.error name="alasan" />
                    <small class="text-muted">Alasan dicatat di riwayat dan ditampilkan ke petugas.</small>
                </div>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            <x-button variant="secondary" data-dismiss="modal" icon="fas fa-times" title="Batal" />
            <x-button variant="danger" form="form-alasan-validasi" type="submit" wire:loading.attr="disabled" icon="fas fa-check" title="Simpan" />
        </x-slot>
    </x-modal>

    <livewire:pages.mutu.modal.input-koreksi-indikator />
    <livewire:pages.mutu.modal.view-audit-log-indikator />
</div>
