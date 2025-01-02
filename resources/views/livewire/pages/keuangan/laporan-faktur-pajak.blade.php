<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table
                :sortColumns="$sortColumns"
                sortable
                zebra
                hover
                sticky
                nowrap
            >
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                    <x-table.th name="no_ktp" title="NIK" />
                    <x-table.th name="nm_pasien" title="Nama" />
                    <x-table.th name="alamat" title="Alamat" />
                    <x-table.th name="no_tlp" title="No. Telp" />
                    <x-table.th name="status_lanjut" title="Jenis Perawatan" />
                    <x-table.th name="png_Jawab" title="Jaminan/Asuransi" />
                    <x-table.th name="status" title="Kategori" />
                    <x-table.th name="nm_perawatan" title="Nama Item" />
                    <x-table.th name="biaya" title="Harga" colspan="2" />
                    <x-table.th name="jumlah" title="Jumlah" />
                    <x-table.th name="totalbiaya" title="Total" colspan="2" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanFakturPajak as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->no_ktp }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                            <x-table.td>{{ $item->no_tlp }}</x-table.td>
                            <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                            <x-table.td-money :value="$item->biaya" />
                            <x-table.td class="text-right">{{ round($item->jumlah, 2) }}</x-table.td>
                            <x-table.td-money :value="round($item->totalbiaya, 0)" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="13" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanFakturPajak" />
        </x-slot>
    </x-card>
</div>
