<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2" style="gap: 1rem; flex-wrap: wrap">
                <div class="d-flex align-items-center">
                    <x-filter.label constant-width>Departemen :</x-filter.label>
                    <x-filter.select2 name="Departemen" model="depId" livewire :options="$this->departemen" placeholder="SEMUA" />
                </div>

                <div class="d-flex align-items-center">
                    <x-filter.label constant-width>Status :</x-filter.label>
                    <select class="form-control form-control-sm" style="width: 12rem" wire:model="statusFilter" wire:change="searchData">
                        <option value="submitted">Submitted (Perlu Validasi)</option>
                        <option value="approved">Approved (Disetujui)</option>
                        <option value="approved_with_correction">Approved w/ Correction</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                        <option value="draft">Draft</option>
                        <option value="all">Semua Status</option>
                    </select>
                </div>

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
                            </x-table.td>
                            <x-table.td class="text-center">
                                <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
                            </x-table.td>
                            <x-table.td>
                                <div class="d-flex" style="gap: 0.25rem">
                                    @if (($record->status ?? 'draft') === 'submitted')
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
                                            <x-button variant="danger" size="xs" icon="fas fa-times" title="Tolak" wire:click="reject({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcan
                                    @elseif ($record->status === 'approved_with_correction')
                                        <x-button
                                            variant="info"
                                            size="xs"
                                            icon="fas fa-history"
                                            title="Riwayat Koreksi"
                                            wire:click="$emit('view-audit-log', {{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @canany(['mutu.validasi-data.approve', 'mutu.validasi-data.reject'])
                                            <x-button
                                                variant="warning"
                                                size="xs"
                                                icon="fas fa-undo"
                                                title="Batal Validasi"
                                                wire:click="resetStatus({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcanany
                                    @elseif (in_array($record->status ?? 'draft', ['approved', 'rejected']))
                                        @canany(['mutu.validasi-data.approve', 'mutu.validasi-data.reject'])
                                            <x-button
                                                variant="warning"
                                                size="xs"
                                                icon="fas fa-undo"
                                                title="Batal Validasi"
                                                wire:click="resetStatus({{ $record->indicator_id }}, '{{ $record->recorded_date }}')" />
                                        @endcanany
                                    @else
                                        <span class="text-muted text-xs">-</span>
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

    <livewire:pages.mutu.modal.input-koreksi-indikator />
    <livewire:pages.mutu.modal.view-audit-log-indikator />
</div>
