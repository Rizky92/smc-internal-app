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
                                {{ $item->jurnal->keterangan }}
                            </x-table.td>
                            <x-table.td-money :value="$item->jurnal->total_debet" />
                            <x-table.td-money :value="$item->jurnal->total_kredit" />
                            <x-table.td>{{ $item->nip }}</x-table.td>
                            <x-table.td>
                                {{ $item->pegawai->nama }}
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
