<div>
    @php
        $status = 'belum';

        $statusColor = [
            'belum' => 'danger',
            'persiapan' => 'info',
            'selesai' => 'success',
        ];
    @endphp
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 15ch" name="no_resep" title="No. Resep" />
                    <x-table.th style="width: 15ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 10ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 10ch" title="Status" />
                    <x-table.th style="width: 12ch" name="waktu_peresepan" title="Waktu Resep" />
                    <x-table.th style="width: 12ch" name="waktu_validasi" title="Waktu Validasi" />
                    <x-table.th style="width: 12ch" name="waktu_penyerahan" title="Waktu Selesai" />

                    {{-- <x-table.th style="width: 10ch" title="Aksi" /> --}}
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPenyerahanObatDriveThru as $item)
                        @php
                            if ($item->waktu_penyerahan) {
                                $status = 'selesai';
                            } else if ($item->waktu_validasi) {
                                $status = 'persiapan';
                            } else {
                                $status = 'belum';
                            }
                        @endphp
                        <x-table.tr>
                            <x-table.td>{{ $item->no_resep }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }} ({{ $item->umurdaftar }} {{ $item->sttsumur }})</x-table.td>
                            <x-table.td><x-badge :variant="$statusColor[$status]">{{ Str::headline($status) }}</x-badge></x-table.td>
                            <x-table.td>{{ $item->waktu_peresepan }}</x-table.td>
                            <x-table.td>{{ $item->waktu_validasi }}</x-table.td>
                            <x-table.td>{{ $item->waktu_penyerahan }}</x-table.td>

                            {{-- <x-table.td>
                                <x-button-group>
                                    <x-button size="sm" variant="success" title="Serahkan" icon="fas fa-check" hide-title />
                                    <x-button size="sm" variant="danger" title="Batalkan" icon="fas fa-times" outline hide-title />
                                </x-button-group>
                            </x-table.td> --}}
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPenyerahanObatDriveThru" />
        </x-slot>
    </x-card>
</div>
