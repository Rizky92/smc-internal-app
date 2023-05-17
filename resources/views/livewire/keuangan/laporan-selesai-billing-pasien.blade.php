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
            <x-table :sortColumns="$sortColumns" style="width: 140rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="id" title="#" style="width: 10ch" />
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 17ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 12ch" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="ruangan" title="Ruang Inap" style="width: 30ch" />
                    <x-table.th name="status_pasien" title="Jenis Perawatan" style="width: 18ch" />
                    <x-table.th name="bentuk_bayar" title="Bentuk Pembayaran" style="width: 24ch" />
                    <x-table.th name="besar_bayar" title="Nominal yang Dibayar" style="width: 27ch" />
                    <x-table.th name="png_jawab" title="Asuransi" style="width: 35ch" />
                    <x-table.th name="tgl_penyelesaian" title="Dilunaskan Pada" style="width: 20ch" />
                    <x-table.th name="nama_pegawai" title="Oleh Petugas" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->billingYangDiselesaikan as $billing)
                        <x-table.tr>
                            @php
                                $currentPage = $this->billingYangDiselesaikan->currentPage() - 1;
                                $perpage = $this->billingYangDiselesaikan->perPage();
                                $id = $currentPage * $perpage + $loop->iteration;
                                
                                $sortByIdDirection = $sortColumns['id'] ?? 'asc';
                                
                                if ($sortByIdDirection === 'desc') {
                                    $id = $this->billingYangDiselesaikan->total() - $currentPage * $perpage - $loop->index;
                                }
                            @endphp
                            <x-table.td>{{ $id }}</x-table.td>
                            <x-table.td>{{ $billing->no_rawat }}</x-table.td>
                            <x-table.td>{{ $billing->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $billing->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $billing->ruangan }}</x-table.td>
                            <x-table.td>{{ $billing->status_pasien }}</x-table.td>
                            <x-table.td>{{ $billing->bentuk_bayar }}</x-table.td>
                            <x-table.td>{{ rp($billing->besar_bayar) }}</x-table.td>
                            <x-table.td>{{ $billing->png_jawab }}</x-table.td>
                            <x-table.td>{{ $billing->tgl_penyelesaian }}</x-table.td>
                            <x-table.td>{{ $billing->nama_pegawai }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->billingYangDiselesaikan" />
        </x-slot>
    </x-card>
</div>
