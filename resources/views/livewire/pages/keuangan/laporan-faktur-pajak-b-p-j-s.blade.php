<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-navtabs livewire selected="medis">
                <x-slot name="tabs">
                    <x-navtabs.tab id="faktur" title="Faktur" />
                    <x-navtabs.tab id="detailfaktur" title="Detail Faktur" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="faktur">
                        <x-table :sortColumns="$sortColumns" style="width: 310rem" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_rawat" title="no_rawat" />
                                <x-table.th name="kode_transaksi" title="kode_transaksi" />
                                <x-table.th name="tgl_bayar" title="tgl_bayar" />
                                <x-table.th name="jam_bayar" title="jam_bayar" />
                                <x-table.th name="status_lanjut" title="status_lanjut" />
                                <x-table.th name="jenis_faktur" title="jenis_faktur" />
                                <x-table.th name="keterangan_tambahan" title="keterangan_tambahan" />
                                <x-table.th name="dokumen_pendukung" title="dokumen_pendukung" />
                                <x-table.th name="cap_fasilitas" title="cap_fasilitas" />
                                <x-table.th name="id_tku_penjual" title="id_tku_penjual" />
                                <x-table.th name="jenis_id" title="jenis_id" />
                                <x-table.th name="negara" title="negara" />
                                <x-table.th name="id_tku" title="id_tku" />
                                <x-table.th name="no_rkm_medis" title="no_rkm_medis" />
                                <x-table.th name="nik_pasien" title="nik_pasien" />
                                <x-table.th name="nama_pasien" title="nama_pasien" />
                                <x-table.th name="alamat_pasien" title="alamat_pasien" />
                                <x-table.th name="email_pasien" title="email_pasien" />
                                <x-table.th name="no_telp_pasien" title="no_telp_pasien" />
                                <x-table.th name="kode_asuransi" title="kode_asuransi" />
                                <x-table.th name="nama_asuransi" title="nama_asuransi" />
                                <x-table.th name="alamat_asuransi" title="alamat_asuransi" />
                                <x-table.th name="telp_asuransi" title="telp_asuransi" />
                                <x-table.th name="email_asurans" title="email_asurans" />
                                <x-table.th name="npwp_asuransi" title="npwp_asuransi" />
                                <x-table.th name="kode_perusahaan" title="kode_perusahaan" />
                                <x-table.th name="nama_perusahaan" title="nama_perusahaan" />
                                <x-table.th name="alamat_perusahaan" title="alamat_perusahaan" />
                                <x-table.th name="telp_perusahaan" title="telp_perusahaan" />
                                <x-table.th name="email_perusahaan" title="email_perusahaan" />
                                <x-table.th name="npwp_perusahaan" title="npwp_perusahaan" />
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
                                        <x-table.td>{{ $item->alamat_asuransi }}</x-table.td>
                                        <x-table.td>{{ $item->telp_asuransi }}</x-table.td>
                                        <x-table.td>{{ $item->email_asurans }}</x-table.td>
                                        <x-table.td>{{ $item->npwp_asuransi }}</x-table.td>
                                        <x-table.td>{{ $item->kode_perusahaan }}</x-table.td>
                                        <x-table.td>{{ $item->nama_perusahaan }}</x-table.td>
                                        <x-table.td>{{ $item->alamat_perusahaan }}</x-table.td>
                                        <x-table.td>{{ $item->telp_perusahaan }}</x-table.td>
                                        <x-table.td>{{ $item->email_perusahaan }}</x-table.td>
                                        <x-table.td>{{ $item->npwp_perusahaan }}</x-table.td>
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
