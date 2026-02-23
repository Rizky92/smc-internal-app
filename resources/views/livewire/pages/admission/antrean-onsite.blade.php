<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nomor" title="Nomor" />
                    <x-table.th name="tanggal" title="Tanggal" />
                    <x-table.th name="jam" title="Jam" />
                    <x-table.th name="jam_panggil" title="Jam Panggil" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->nomor }}</x-table.td>
                            <x-table.td>{{ $item->tanggal }}</x-table.td>
                            <x-table.td>{{ $item->jam }}</x-table.td>
                            <x-table.td>{{ $item->jam_panggil }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
