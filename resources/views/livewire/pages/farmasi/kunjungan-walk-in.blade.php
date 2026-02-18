<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nota_jual" title="Nota Jual" />
                    <x-table.th name="no_rkm_medis" title="No RM" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="alamat" title="Alamat" />
                    <x-table.th name="tgl_jual" title="Tanggal Jual" />
                    <x-table.th name="jumlah" title="Jumlah" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataKunjunganWalkIn as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->nota_jual }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_jual }}</x-table.td>
                            <x-table.td>{{ $item->jumlah }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataKunjunganWalkIn" />
        </x-slot>
    </x-card>
</div>
