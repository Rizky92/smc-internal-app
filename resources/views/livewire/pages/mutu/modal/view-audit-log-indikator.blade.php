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
            @elseif ($logs !== null)
                <p class="text-muted text-center mb-0">Belum ada riwayat audit untuk data ini.</p>
            @else
                <p class="text-muted text-center mb-0">Memuat data...</p>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-button variant="secondary" data-dismiss="modal" icon="fas fa-times" title="Tutup" />
        </x-slot>
    </x-modal>
</div>
