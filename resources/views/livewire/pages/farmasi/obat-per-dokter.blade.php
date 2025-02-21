<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 12ch" name="no_resep" title="No. Resep" />
                    <x-table.th style="width: 30ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 12ch" name="tgl_perawatan" title="Tgl. Validasi" />
                    <x-table.th style="width: 10ch" name="jam" title="Jam" />
                    <x-table.th style="width: 30ch" name="nama_brng" title="Nama Obat" />
                    <x-table.th style="width: 20ch" name="nama" title="Kategori" />
                    <x-table.th style="width: 7ch" name="jml" title="Jumlah" />
                    <x-table.th style="width: 40ch" name="nm_dokter" title="Dokter Peresep" />
                    <x-table.th style="width: 40ch" name="dpjp" title="DPJP" />
                    <x-table.th style="width: 12ch" name="status" title="Jenis Rawat" />
                    <x-table.th style="width: 30ch" name="nm_poli" title="Asal Poli" />
                    <x-table.th style="width: 30ch" name="png_jawab" title="Jenis Bayar" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->obatPerDokter as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->no_resep }}</x-table.td>
                            <x-table.td>{{ $obat->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $obat->tgl_perawatan }}
                            </x-table.td>
                            <x-table.td>{{ $obat->jam }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama }}</x-table.td>
                            <x-table.td>{{ $obat->jml }}</x-table.td>
                            <x-table.td>{{ $obat->nm_dokter }}</x-table.td>
                            <x-table.td>{{ $obat->dpjp }}</x-table.td>
                            <x-table.td>
                                {{ str($obat->status)->title() }}
                            </x-table.td>
                            <x-table.td>{{ $obat->nm_poli }}</x-table.td>
                            <x-table.td>{{ $obat->png_jawab }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->obatPerDokter" />
        </x-slot>
    </x-card>
</div>
