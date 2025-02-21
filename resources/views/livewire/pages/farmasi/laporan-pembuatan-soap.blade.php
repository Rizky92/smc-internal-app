<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tgl_perawatan" title="Tgl. SOAP" />
                    <x-table.th name="jam_rawat" title="Jam" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="dpjp" title="DPJP" />
                    <x-table.th name="alergi" title="Alergi" />
                    <x-table.th name="keluhan" title="Keluhan (Subjek)" />
                    <x-table.th name="pemeriksaan" title="Pemeriksaan (Objek)" />
                    <x-table.th name="penilaian" title="Penilaian (Asesmen)" />
                    <x-table.th name="rtl" title="RTL (Plan)" />
                    <x-table.th name="nip" title="Petugas" />
                    <x-table.th name="nm_jbtn" title="Jabatan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanPembuatanSOAP as $item)
                        <x-table.tr>
                            <x-table.td>
                                {{ $item->tgl_perawatan }}
                            </x-table.td>
                            <x-table.td>{{ $item->jam_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>
                                {{ optional($item->dpjp)->pluck('nm_dokter')->join('; ') }}
                            </x-table.td>
                            <x-table.td>{{ $item->alergi }}</x-table.td>
                            <x-table.td>{{ $item->keluhan }}</x-table.td>
                            <x-table.td>
                                {{ $item->pemeriksaan }}
                            </x-table.td>
                            <x-table.td>{{ $item->penilaian }}</x-table.td>
                            <x-table.td>{{ $item->rtl }}</x-table.td>
                            <x-table.td>{{ $item->nip }} {{ $item->nama }}</x-table.td>
                            <x-table.td>{{ $item->nm_jbtn }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanPembuatanSOAP" />
        </x-slot>
    </x-card>
</div>
