<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode Barang" />
                    <x-table.th name="nama_brng" title="Nama Barang" />
                    <x-table.th name="kode_sat" title="Satuan" />
                    <x-table.th-money name="harga" title="Harga(Rp)" />
                    <x-table.th name="stok_awal" title="Stok Awal" />
                    <x-table.th-money name="nilai_stok_awal" title="Stok Awal(Rp)" />
                    <x-table.th name="pengadaan" title="Pengadaan" />
                    <x-table.th-money name="sub_total_pengadaan" title="Pengadaan(Rp)" />
                    <x-table.th name="penerimaan" title="Penerimaan" />
                    <x-table.th-money name="sub_total_penerimaan" title="Penerimaan(Rp)" />
                    <x-table.th name="stok_keluar" title="Stok Keluar" />
                    <x-table.th-money name="sub_total_keluar" title="Stok Keluar(Rp)" />
                    <x-table.th name="pengambilan_utd" title="Pengambilan UTD" />
                    <x-table.th-money name="sub_total_pengambilan_utd" title="Pengambilan UTD(Rp)" />
                    <x-table.th name="hibah" title="Hibah" />
                    <x-table.th-money name="sub_total_hibah" title="Hibah(Rp)" />
                    <x-table.th name="stok_akhir" title="Stok Akhir" />
                    <x-table.th-money name="nilai_stok_akhir" title="Stok Akhir(Rp)" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td>{{ $item->kode_sat }}</x-table.td>
                            <x-table.td-money :value="$item->harga" />
                            <x-table.td class="text-right">{{ $item->stok_awal }}</x-table.td>
                            <x-table.td-money :value="($item->stok_awal * $item->harga)" />
                            <x-table.td class="text-right">{{ $item->pengadaan }}</x-table.td>
                            <x-table.td-money :value="$item->sub_total_pengadaan" />
                            <x-table.td class="text-right">{{ $item->penerimaan }}</x-table.td>
                            <x-table.td-money :value="$item->sub_total_penerimaan" />
                            <x-table.td class="text-right">{{ $item->stok_keluar }}</x-table.td>
                            <x-table.td-money :value="$item->sub_total_keluar" />
                            <x-table.td class="text-right">{{ $item->pengambilan_utd }}</x-table.td>
                            <x-table.td-money :value="$item->sub_total_pengambilan_utd" />
                            <x-table.td class="text-right">{{ $item->hibah }}</x-table.td>
                            <x-table.td-money :value="$item->sub_total_hibah" />
                            <x-table.td class="text-right">
                                {{ $item->stok_awal + $item->pengadaan + $item->penerimaan + $item->hibah - $item->stok_keluar - $item->pengambilan_utd }}
                            </x-table.td>
                            <x-table.td-money :value="($item->stok_awal + $item->pengadaan + $item->penerimaan + $item->hibah - $item->stok_keluar - $item->pengambilan_utd) * $item->harga" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="25" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
