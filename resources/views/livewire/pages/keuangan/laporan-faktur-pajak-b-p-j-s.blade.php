<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-navtabs livewire selected="faktur">
                <x-slot name="tabs">
                    <x-navtabs.tab id="faktur" title="Faktur" />
                    <x-navtabs.tab id="detailfaktur" title="Detail Faktur" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="faktur">
                        <x-table :sortColumns="$sortColumns" style="width: 310rem" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_rawat" title="No. Rawat" />
                                <x-table.th name="kode_transaksi" title="Kode Transaksi" />
                                <x-table.th name="tgl_bayar" title="Tgl. Bayar" />
                                <x-table.th name="jam_bayar" title="Jam Bayar" />
                                <x-table.th name="status_lanjut" title="Jenis Rawat" />
                                <x-table.th name="jenis_faktur" title="Jenis Faktur" />
                                <x-table.th name="keterangan_tambahan" title="Keterangan Tambahan" />
                                <x-table.th name="dokumen_pendukung" title="Dokumen Pendukung" />
                                <x-table.th name="cap_fasilitas" title="Cap Fasilitas" />
                                <x-table.th name="id_tku_penjual" title="ID TKU Penjual" />
                                <x-table.th name="jenis_id" title="Jenis ID" />
                                <x-table.th name="negara" title="Negara" />
                                <x-table.th name="id_tku" title="ID TKU" />
                                <x-table.th name="no_rkm_medis" title="No. RM" />
                                <x-table.th name="nik_pasien" title="NIK Pasien" />
                                <x-table.th name="nama_pasien" title="Nama Pasien" />
                                <x-table.th name="alamat_pasien" title="Alamat Pasien" />
                                <x-table.th name="email_pasien" title="Email Pasien" />
                                <x-table.th name="no_telp_pasien" title="No. Telp Pasien" />
                                <x-table.th name="kode_asuransi" title="Kode Asuransi" />
                                <x-table.th name="nama_asuransi" title="Nama Asuransi" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataLaporanFakturPajak as $item)
                                    <x-table.tr>
                                        <x-table.td>{{ $item->no_rawat }}</x-table.td>
                                        <x-table.td>{{ $item->kode_transaksi }}</x-table.td>
                                        <x-table.td>{{ $item->tgl_bayar }}</x-table.td>
                                        <x-table.td>{{ $item->jam_bayar }}</x-table.td>
                                        <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ $item->jenis_faktur }}</x-table.td>
                                        <x-table.td>{{ $item->keterangan_tambahan }}</x-table.td>
                                        <x-table.td>{{ $item->dokumen_pendukung }}</x-table.td>
                                        <x-table.td>{{ $item->cap_fasilitas }}</x-table.td>
                                        <x-table.td>{{ $item->id_tku_penjual }}</x-table.td>
                                        <x-table.td>{{ $item->jenis_id }}</x-table.td>
                                        <x-table.td>{{ $item->negara }}</x-table.td>
                                        <x-table.td>{{ $item->id_tku }}</x-table.td>
                                        <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                                        <x-table.td>{{ $item->nik_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->nama_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->alamat_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->email_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->no_telp_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->kode_asuransi }}</x-table.td>
                                        <x-table.td>{{ $item->nama_asuransi }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="31" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataLaporanFakturPajak" />
                    </x-navtabs.content>
                    <x-navtabs.content id="detailfaktur">
                        {{-- <x-table :sortColumns="$sortColumns" style="width: 200rem" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_rawat" title="No. Rawat" />
                                <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                                <x-table.th name="tgl_bayar" title="Tgl. Pelunasan" />
                                <x-table.th name="jam_bayar" title="Jam Pelunasan" />
                                <x-table.th name="jenis_id" title="Jenis ID" />
                                <x-table.th name="negara" title="Negara" />
                                <x-table.th name="npwp" title="No. NPWP" />
                                <x-table.th name="no_rkm_medis" title="No. RM" />
                                <x-table.th name="no_ktp" title="NIK" />
                                <x-table.th name="nm_pasien" title="Nama" />
                                <x-table.th name="alamat" title="Alamat" />
                                <x-table.th name="email" title="Email" />
                                <x-table.th name="no_tlp" title="No. Telp" />
                                <x-table.th name="status_lanjut" title="Status Registrasi" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataLaporanFakturPajak as $item)
                                    <x-table.tr>
                                        <x-table.td>{{ $item->no_rawat }}</x-table.td>
                                        <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                                        <x-table.td>{{ $item->tgl_bayar }}</x-table.td>
                                        <x-table.td>{{ $item->jam_bayar }}</x-table.td>
                                        <x-table.td>{{ $item->jenis_id }}</x-table.td>
                                        <x-table.td>{{ $item->negara }}</x-table.td>
                                        <x-table.td>{{ $item->npwp }}</x-table.td>
                                        <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                                        <x-table.td>{{ $item->no_ktp }}</x-table.td>
                                        <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                                        <x-table.td>{{ $item->alamat }}</x-table.td>
                                        <x-table.td>{{ $item->email }}</x-table.td>
                                        <x-table.td>{{ $item->no_tlp }}</x-table.td>
                                        <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="14" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataLaporanFakturPajak" /> --}}
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
            
        </x-slot>
    </x-card>
</div>
