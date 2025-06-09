<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-refresh method="tarikDataTerbaru" title="Tarik Data Terbaru" class="ml-auto" />
                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 120rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 17ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 10ch" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="ruangan" title="Ruang Inap" style="width: 30ch" />
                    <x-table.th name="status_pasien" title="Jenis Perawatan" style="width: 18ch" />
                    <x-table.th name="bentuk_bayar" title="Bentuk Pembayaran" style="width: 20ch" />
                    <x-table.th name="besar_bayar" title="Total" style="width: 20ch" />
                    <x-table.th name="png_jawab" title="Asuransi" style="width: 25ch" />
                    <x-table.th name="tgl_penyelesaian" title="Dilunaskan Pada" style="width: 20ch" />
                    <x-table.th name="nama_pegawai" title="Oleh Petugas" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->billingYangDiselesaikan as $billing)
                        <x-table.tr>
                            <x-table.td :title="$billing->no_rawat">
                                {{ $billing->no_rawat }}
                            </x-table.td>
                            <x-table.td :title="$billing->no_rkm_medis">
                                {{ $billing->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td :title="$billing->nm_pasien">
                                {{ $billing->nm_pasien }}
                            </x-table.td>
                            <x-table.td :title="$billing->ruangan">
                                {{ $billing->ruangan }}
                            </x-table.td>
                            <x-table.td :title="$billing->status_pasien">
                                {{ $billing->status_pasien }}
                            </x-table.td>
                            <x-table.td :title="$billing->bentuk_bayar">
                                {{ $billing->bentuk_bayar }}
                            </x-table.td>
                            <x-table.td :title="rp($billing->besar_bayar)">
                                {{ rp($billing->besar_bayar) }}
                            </x-table.td>
                            <x-table.td :title="$billing->png_jawab">
                                {{ $billing->png_jawab }}
                            </x-table.td>
                            <x-table.td :title="$billing->tgl_penyelesaian">
                                {{ $billing->tgl_penyelesaian }}
                            </x-table.td>
                            <x-table.td :title="$billing->nama_pegawai">
                                {{ $billing->nama_pegawai }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="10" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->billingYangDiselesaikan" />
        </x-slot>
    </x-card>
</div>
