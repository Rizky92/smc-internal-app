<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Kode Barang" />
                    <x-table.th title="Nama Barang" />
                    <x-table.th title="Total Pesanan" />
                    <x-table.th title="Total Tagihan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->rincianPerbandinganBarangPO as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->total_pemesanan }}</x-table.td>
                            <x-table.td>{{ rp($obat->total_tagihan) }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="4" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->rincianPerbandinganBarangPO" />
        </x-slot>
    </x-card>
</div>
