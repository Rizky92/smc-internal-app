<div wire:init="loadProperties">
    <x-flash />
    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="No. Rawat" />
                    <x-table.th title="Nama" />
                    <x-table.th title="Tgl. Lahir" />
                    <x-table.th title="Umur" />
                    <x-table.th title="JK" />
                    <x-table.th title="Tgl. Daftar" />
                    <x-table.th title="Tindakan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPasienPoliMCU as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->pasien->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->pasien->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $item->pasien->umur }}</x-table.td>
                            <x-table.td>{{ $item->pasien->jk }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="1" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPasienPoliMCU" />
        </x-slot>
    </x-card>
</div>
