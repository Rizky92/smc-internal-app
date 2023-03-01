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
        <x-slot name="body">
            <x-navtabs livewire>
                <x-slot name="tabs">
                    <x-navtabs.tab id="medis" title="Obat/BHP/Alkes" selected />
                    <x-navtabs.tab id="nonmedis" title="Non Medis" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="medis" class="table-responsive" selected>
                        <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                            <x-slot name="columns">
                                <x-table.th name="id" title="#" />
                                <x-table.th name="no_jurnal" title="No. Jurnal" />
                                <x-table.th name="waktu_jurnal" title="Waktu" />
                                <x-table.th name="no_faktur" title="No. Faktur" />
                                <x-table.th name="status" title="Status" />
                                <x-table.th name="besar_bayar" title="Nominal" />
                                <x-table.th name="nama_bayar" title="Akun Bayar" />
                                <x-table.th name="kd_rek" title="Kode Rekening" />
                                <x-table.th name="nama_suplier" title="Supplier" />
                                <x-table.th name="nm_pegawai" title="Petugas" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->jurnalBarangMedis as $jurnal)
                                    @php
                                        $currentPage = $this->jurnalBarangMedis->currentPage() - 1;
                                        $perpage = $this->jurnalBarangMedis->perPage();
                                        $id = $currentPage * $perpage + $loop->iteration;
                                        
                                        $sortByIdDirection = $sortColumns['id'] ?? 'asc';
                                        
                                        if ($sortByIdDirection === 'desc') {
                                            $id = $this->jurnalBarangMedis->total() - $currentPage * $perpage - $loop->index;
                                        }
                                    @endphp
                                    <x-table.tr>
                                        <x-table.td>{{ $id }}</x-table.td>
                                        <x-table.td>{{ $jurnal->no_jurnal }}</x-table.td>
                                        <x-table.td>{{ $jurnal->waktu_jurnal }}</x-table.td>
                                        <x-table.td>{{ $jurnal->no_faktur }}</x-table.td>
                                        <x-table.td>{{ $jurnal->status }}</x-table.td>
                                        <x-table.td>{{ rp($jurnal->besar_bayar) }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nama_bayar }}</x-table.td>
                                        <x-table.td>{{ $jurnal->kd_rek }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nama_suplier }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nm_pegawai }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="10" />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->jurnalBarangMedis" />
                    </x-navtabs.content>
                    <x-navtabs.content id="nonmedis" class="table-responsive">
                        <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                            <x-slot name="columns">
                                <x-table.th name="id" title="#" />
                                <x-table.th name="no_jurnal" title="No. Jurnal" />
                                <x-table.th name="waktu_jurnal" title="Waktu" />
                                <x-table.th name="no_faktur" title="No. Faktur" />
                                <x-table.th name="status" title="Status" />
                                <x-table.th name="besar_bayar" title="Nominal" />
                                <x-table.th name="nama_bayar" title="Akun Bayar" />
                                <x-table.th name="kd_rek" title="Kode Rekening" />
                                <x-table.th name="nama_suplier" title="Supplier" />
                                <x-table.th name="nm_pegawai" title="Petugas" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->jurnalBarangNonMedis as $jurnal)
                                    @php
                                        $currentPage = $this->jurnalBarangNonMedis->currentPage() - 1;
                                        $perpage = $this->jurnalBarangNonMedis->perPage();
                                        $id = $currentPage * $perpage + $loop->iteration;
                                        
                                        $sortByIdDirection = $sortColumns['id'] ?? 'asc';
                                        
                                        if ($sortByIdDirection === 'desc') {
                                            $id = $this->jurnalBarangNonMedis->total() - $currentPage * $perpage - $loop->index;
                                        }
                                    @endphp
                                    <x-table.tr>
                                        <x-table.td>{{ $id }}</x-table.td>
                                        <x-table.td>{{ $jurnal->no_jurnal }}</x-table.td>
                                        <x-table.td>{{ $jurnal->waktu_jurnal }}</x-table.td>
                                        <x-table.td>{{ $jurnal->no_faktur }}</x-table.td>
                                        <x-table.td>{{ $jurnal->status }}</x-table.td>
                                        <x-table.td>{{ rp($jurnal->besar_bayar) }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nama_bayar }}</x-table.td>
                                        <x-table.td>{{ $jurnal->kd_rek }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nama_suplier }}</x-table.td>
                                        <x-table.td>{{ $jurnal->nm_pegawai }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="10" />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->jurnalBarangNonMedis" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
