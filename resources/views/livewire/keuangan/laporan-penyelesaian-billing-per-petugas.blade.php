<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date />
                <x-filter.button method="tarikDataTerbaru" title="Tarik Data Terbaru" icon="fas fa-sync-alt" class="ml-auto" />
                <x-filter.button-export-excel class="ml-2" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table style="min-width: 100%; width: 140rem" sortable :sortColumns="$sortColumns">
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
                    @foreach ($this->billingYangDiselesaikan as $billing)
                        <x-table.tr>
                            @php
                                $currentPage = $this->billingYangDiselesaikan->currentPage() - 1;
                                $perpage = $this->billingYangDiselesaikan->perPage();
                                $id = ($currentPage * $perpage) + $loop->iteration;

                                $sortByIdDirection = $sortColumns['id'] ?? 'asc';

                                if ($sortByIdDirection === 'desc') {
                                    $id = $this->billingYangDiselesaikan->total() - ($currentPage * $perpage) - $loop->index;
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
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->billingYangDiselesaikan" />
        </x-slot>
    </x-card>
</div>
