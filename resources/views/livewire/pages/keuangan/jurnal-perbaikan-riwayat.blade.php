<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_jurnal" title="No. Jurnal" />
                    <x-table.th name="tgl_jurnal_asli" title="Tgl. Asli" />
                    <x-table.th name="tgl_jurnal_diubah" title="Tgl. Diubah" />
                    <x-table.th name="keterangan" title="Keterangan" />
                    <x-table.th align="right" name="total_debet" title="Total Debet" colspan="2" />
                    <x-table.th align="right" name="total_kredit" title="Total Kredit" colspan="2" />
                    <x-table.th name="nip" title="NIP" />
                    <x-table.th name="nama" title="Pegawai" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataRiwayatJurnalPerbaikan as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_jurnal }}</x-table.td>
                            <x-table.td>
                                {{ $item->tgl_jurnal_asli }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->tgl_jurnal_diubah }}
                            </x-table.td>
                            <x-table.td>
                                {{-- jurnal_backup lives in mysql_smc and the journal in mysql_sik,
                                     so no foreign key holds them together and the journal this row
                                     records an edit to may since have been deleted. --}}
                                {{ $item->jurnal?->keterangan }}
                            </x-table.td>
                            <x-table.td-money :value="$item->jurnal?->total_debet ?? 0" />
                            <x-table.td-money :value="$item->jurnal?->total_kredit ?? 0" />
                            <x-table.td>{{ $item->nip }}</x-table.td>
                            <x-table.td>
                                {{-- nip is a plain string across the database boundary, so the
                                     petugas may no longer exist in Khanza. --}}
                                {{ $item->pegawai?->nama }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="10" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataRiwayatJurnalPerbaikan" />
        </x-slot>
    </x-card>
</div>
