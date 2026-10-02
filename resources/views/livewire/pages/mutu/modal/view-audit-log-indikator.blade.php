<div>
    @push('js')
        <script>
            window.addEventListener('open-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('show');
            });

            window.addEventListener('close-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-view-audit-log-indikator" title="Riwayat Audit" size="lg" livewire>
        <x-slot name="body">
            <x-flash class="mx-3 mt-3" />
            @if ($histories && $histories->isNotEmpty())
                <h6 class="px-3 pt-3">Riwayat Validasi</h6>
                <x-table zebra hover sticky nowrap>
                    <x-slot name="columns">
                        <x-table.th title="Waktu" />
                        <x-table.th title="Aksi" />
                        <x-table.th title="Status" />
                        <x-table.th title="Num / Den" />
                        <x-table.th title="Oleh" />
                        <x-table.th title="Alasan" />
                    </x-slot>
                    <x-slot name="body">
                        @foreach ($histories as $history)
                            <x-table.tr>
                                <x-table.td>{{ carbon($history->created_at)->format('d-m-Y H:i') }}</x-table.td>
                                <x-table.td>{{ $history->actionLabel() }}</x-table.td>
                                <x-table.td>{{ $history->status_before ?? '-' }} &rarr; {{ $history->status_after }}</x-table.td>
                                <x-table.td>{{ $history->numerator_value }} / {{ $history->denominator_value }}</x-table.td>
                                <x-table.td>{{ $history->actor ?: '-' }}</x-table.td>
                                <x-table.td>{{ $history->reason ?: '-' }}</x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-slot>
                </x-table>
                @if ($logs && $logs->isNotEmpty())
                    <h6 class="px-3 pt-3">Perubahan Nilai</h6>
                @endif
            @endif

            @if ($logs && $logs->isNotEmpty())
                <x-table zebra hover sticky nowrap>
                    <x-slot name="columns">
                        <x-table.th title="Tanggal" />
                        <x-table.th title="Field" />
                        <x-table.th title="Nilai Lama" />
                        <x-table.th title="Nilai Baru" />
                        <x-table.th title="Diubah Oleh" />
                        <x-table.th title="Alasan" />
                    </x-slot>
                    <x-slot name="body">
                        @foreach ($logs as $log)
                            @php
                                $fieldLabels = [
                                    'numerator_value' => 'Numerator',
                                    'denominator_value' => 'Denominator',
                                    'notes' => 'Catatan',
                                ];
                            @endphp

                            <x-table.tr>
                                <x-table.td>{{ carbon($log->created_at)->format('d-m-Y H:i') }}</x-table.td>
                                <x-table.td>{{ $fieldLabels[$log->field_name] ?? $log->field_name }}</x-table.td>
                                <x-table.td>{{ $log->old_value ?: '-' }}</x-table.td>
                                <x-table.td>
                                    <span class="text-success font-weight-bold">{{ $log->new_value ?: '-' }}</span>
                                </x-table.td>
                                <x-table.td>{{ $log->changed_by }}</x-table.td>
                                <x-table.td>{{ $log->reason ?: '-' }}</x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-slot>
                </x-table>
            @elseif ($logs !== null && ($histories === null || $histories->isEmpty()))
                <p class="text-muted text-center mb-0">Belum ada riwayat audit untuk data ini.</p>
            @elseif ($logs === null)
                <p class="text-muted text-center mb-0">Memuat data...</p>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-button variant="secondary" data-dismiss="modal" icon="fas fa-times" title="Tutup" />
        </x-slot>
    </x-modal>
</div>
