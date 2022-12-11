<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('excel.exporting') }}
            </p>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <div class="ml-auto">
                            <button class="btn btn-default btn-sm" type="button" wire:click="exportToExcel">
                                <i class="fas fa-file-excel"></i>
                                <span class="ml-1">Export ke Excel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0 border-top">
            <table id="table_index" class="table table-hover table-striped table-sm text-sm" style="width: 150rem">
                <thead>
                    <tr>
                        @php($bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                        <th width="250">Laporan</th>
                        @foreach ($bulan as $b)
                            <th class="text-center px-0" width="150">{{ $b }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row" width="250">TOTAL KUNJUNGAN</th>
                        @foreach ($this->kunjunganTotal as $item)
                            <th scope="col" class="text-center px-0" width="150">{{ $item }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th scope="row" width="250">Kunjungan Rawat Jalan</th>
                        @foreach ($this->kunjunganRalan as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th scope="row" width="250">Kunjungan Rawat Inap</th>
                        @foreach ($this->kunjunganRanap as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Kunjungan IGD</th>
                        @foreach ($this->kunjunganIgd as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Kunjungan <i>Walk in</i></th>
                        @foreach ($this->kunjunganWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">TOTAL PENDAPATAN</th>
                        @foreach ($this->pendapatanObatTotal as $item)
                            <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat Rawat Jalan</th>
                        @foreach ($this->pendapatanObatRalan as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat Rawat Inap</th>
                        @foreach ($this->pendapatanObatRanap as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat IGD</th>
                        @foreach ($this->pendapatanObatIGD as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat <i>Walk in</i></th>
                        @foreach ($this->pendapatanObatWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Alkes Farmasi dan Unit</th>
                    </tr>
                    <tr>
                        <th width="250">Retur Obat</th>
                        @foreach ($this->totalReturObat as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pembelian Farmasi</th>
                        @foreach ($this->totalPembelianFarmasi as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Retur Supplier</th>
                        @foreach ($this->totalReturObatKeSupplier as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">TOTAL PEMBELIAN (Pembelian Farmasi - Retur Supplier)</th>
                        @foreach ($this->totalBersihPembelianFarmasi as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pemakaian BHP</th>
                        @foreach ($this->stokKeluarMedis as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Transfer Order</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
