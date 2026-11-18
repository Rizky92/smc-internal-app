<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_sep" title="No. SEP" />
                    <x-table.th name="tglsep" title="Tgl. SEP" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="nomr" title="No. RM" />
                    <x-table.th name="no_kartu" title="No. Kartu" />
                    <x-table.th name="nama_pasien" title="Nama Pasien" />
                    <x-table.th name="nmpolitujuan" title="Poli Tujuan" />
                    <x-table.th name="nmdpdjp" title="DPJP" />
                    <x-table.th name="jnspelayanan" title="Jns. Pelayanan" />
                    <x-table.th name="nmdiagnosaawal" title="Diagnosa" />
                    <x-table.th name="peserta" title="Peserta" />
                    <x-table.th name="asal_rujukan" title="Asal Rujukan" />
                    <x-table.th name="no_rujukan" title="No. Rujukan" />
                    <x-table.th name="tglrujukan" title="Tgl. Rujukan" />
                    <x-table.th name="noskdp" title="No. SKDP" />
                    <x-table.th name="prb" title="PRB" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_sep }}</x-table.td>
                            <x-table.td>{{ $item->tglsep }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->nomr }}</x-table.td>
                            <x-table.td>{{ $item->no_kartu }}</x-table.td>
                            <x-table.td>{{ $item->nama_pasien }}</x-table.td>
                            <x-table.td>{{ $item->nmpolitujuan }}</x-table.td>
                            <x-table.td>{{ $item->nmdpdjp }}</x-table.td>
                            <x-table.td>{{ $item->jnspelayanan == '1' ? 'Ranap' : 'Ralan' }}</x-table.td>
                            <x-table.td>{{ $item->nmdiagnosaawal }}</x-table.td>
                            <x-table.td>{{ $item->peserta }}</x-table.td>
                            <x-table.td>{{ $item->asal_rujukan }}</x-table.td>
                            <x-table.td>{{ $item->no_rujukan }}</x-table.td>
                            <x-table.td>{{ $item->tglrujukan }}</x-table.td>
                            <x-table.td>{{ $item->noskdp }}</x-table.td>
                            <x-table.td>{{ $item->prb }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
