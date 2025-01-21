<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
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
                                <x-table.th name="status_lanjut" title="Jenis Rawat" />
                                <x-table.th name="tgl_bayar" title="Tgl. Faktur" />
                                <x-table.th name="jenis_faktur" title="Jenis Faktur" />
                                <x-table.th name="kode_transaksi" title="Kode Transaksi" />
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
                                        <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ $item->tgl_bayar }} {{ $item->jam_bayar }}</x-table.td>
                                        <x-table.td>{{ $item->jenis_faktur }}</x-table.td>
                                        <x-table.td>{{ $item->kode_transaksi }}</x-table.td>
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
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="20" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataLaporanFakturPajak" />
                    </x-navtabs.content>
                    <x-navtabs.content id="detailfaktur">
                        <x-table :sortColumns="$sortColumns" style="width: 100rem" zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_rawat" title="No. Rawat" />
                                <x-table.th name="kd_jenis_prw" title="Kode Item RS" />
                                <x-table.th name="kategori" title="Kategori" />
                                <x-table.th name="status_lanjut" title="Status Rawat" />
                                <x-table.th name="jenis_barang_jasa" title="Jenis Barang/Jasa" />
                                <x-table.th name="kode_barang_jasa" title="Kode Barang/Jasa" />
                                <x-table.th name="nama_barang_jasa" title="Nama Barang/Jasa" />
                                <x-table.th name="nama_satuan_ukur" title="Nama Satuan Ukur" />
                                <x-table.th-money align="right" name="harga_satuan" title="Harga Satuan" />
                                <x-table.th align="right" name="jumlah_barang_jasa" title="Jumlah" />
                                <x-table.th align="right" name="diskon_persen" title="Diskon (%)" />
                                <x-table.th-money align="right" name="diskon_nominal" title="Diskon (Rp)" />
                                <x-table.th-money align="right" name="dpp" title="DPP" />
                                <x-table.th-money align="right" name="dpp_nilai_lain" title="DPP Nilai Lain" />
                                <x-table.th align="right" name="ppn_persen" title="PPN (%)" />
                                <x-table.th-money align="right" name="ppn_nominal" title="PPN (Rp)" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataDetailFakturPajak as $item)
                                    @php
                                        $dppNilaiLain = round(intval($item->dpp) * (11/12));
                                        $ppn = intval($item->ppn_persen === '0' ? '12' : $item->ppn_persen);
                                    @endphp
                                    <x-table.tr>
                                        <x-table.td>{{ $item->no_rawat }}</x-table.td>
                                        <x-table.td>{{ $item->kd_jenis_prw }}</x-table.td>
                                        <x-table.td>{{ $item->kategori }}</x-table.td>
                                        <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ $item->jenis_barang_jasa }}</x-table.td>
                                        <x-table.td>{{ $item->kode_barang_jasa }}</x-table.td>
                                        <x-table.td>{{ $item->nama_barang_jasa }}</x-table.td>
                                        <x-table.td>{{ $item->nama_satuan_ukur }}</x-table.td>
                                        <x-table.td-money :value="$item->harga_satuan" />
                                        <x-table.td class="text-right">{{ $item->jumlah_barang_jasa }}</x-table.td>
                                        <x-table.td class="text-right">{{ $item->diskon_persen }}</x-table.td>
                                        <x-table.td-money :value="$item->diskon_nominal" />
                                        <x-table.td-money :value="$item->dpp" />
                                        <x-table.td-money :value="$dppNilaiLain" />
                                        <x-table.td class="text-right">{{ $ppn }}</x-table.td>
                                        <x-table.td-money :value="round($dppNilaiLain * ($ppn / 100))" />
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="21" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataDetailFakturPajak" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
