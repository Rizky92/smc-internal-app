<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_sep" title="No. SEP" />
                    <x-table.th name="tglsep" title="Tgl. SEP" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="jenis_pelayanan" title="Jenis Pelayanan" />
                    <x-table.th name="no_rm" title="No. RM" />
                    <x-table.th name="nama_pasien" title="Nama Pasien" />
                    <x-table.th name="jaminan_registrasi" title="Jenis Bayar" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_sep }}</x-table.td>
                            <x-table.td>{{ $item->tglsep }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->jenis_pelayanan }}</x-table.td>
                            <x-table.td>{{ $item->no_rm }}</x-table.td>
                            <x-table.td>{{ $item->nama_pasien }}</x-table.td>
                            <x-table.td>{{ $item->jaminan_registrasi }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="7" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
