@extends('layouts.admin', [
    'title' => 'Laporan Produksi Farmasi Per Tahun',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table id="table_index" class="table table-hover table-striped table-bordered table-sm text-sm" style="width: 150rem">
                        <thead>
                            <tr>
                                @php($bulan = [
                                    'Januari',
                                    'Februari',
                                    'Maret',
                                    'April',
                                    'Mei',
                                    'Juni',
                                    'Juli',
                                    'Agustus',
                                    'September',
                                    'Oktober',
                                    'November',
                                    'Desember',
                                ])
                                <th width="250">Laporan</th>
                                @foreach ($bulan as $b)
                                    <th class="text-center px-0" width="150">{{ $b }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row" width="250">TOTAL KUNJUNGAN</th>
                                @foreach ($kunjunganTotal as $item)
                                    <th scope="col" class="text-center px-0" width="150">{{ $item }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th scope="row" width="250">Kunjungan Rawat Jalan</th>
                                @foreach ($kunjunganRalan as $item)
                                    <td class="text-center px-0" width="150">{{ $item }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th scope="row" width="250">Kunjungan Rawat Inap</th>
                                @foreach ($kunjunganRanap as $item)
                                    <td class="text-center px-0" width="150">{{ $item }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th>Kunjungan IGD</th>
                                @foreach ($kunjunganIgd as $item)
                                    <td class="text-center px-0" width="150">{{ $item }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Kunjungan <i>Walk in</i></th>
                                @foreach ($kunjunganWalkIn as $item)
                                    <td class="text-center px-0" width="150">{{ $item }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">TOTAL PENDAPATAN</th>
                                @foreach ($pendapatanObatTotal as $item)
                                    <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pendapatan Obat Rawat Jalan</th>
                                @foreach ($pendapatanObatRalan as $item)
                                    <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pendapatan Obat Rawat Inap</th>
                                @foreach ($pendapatanObatRanap as $item)
                                    <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pendapatan Obat IGD</th>
                                @foreach ($pendapatanObatIGD as $item)
                                    <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pendapatan Obat <i>Walk in</i></th>
                                @foreach ($pendapatanObatWalkIn as $item)
                                    <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pendapatan Alkes Farmasi dan Unit</th>
                            </tr>
                            <tr>
                                <th width="250">Retur Obat</th>
                                @foreach ($totalReturObat as $item)
                                    <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th width="250">Pembelian Farmasi</th>
                            </tr>
                            <tr>
                                <th width="250">Retur Supplier</th>
                            </tr>
                            <tr>
                                <th width="250">TOTAL PEMBELIAN (Pembelian Farmasi - Retur Supplier)</th>
                            </tr>
                            <tr>
                                <th width="250">Pemakaian BHP</th>
                            </tr>
                            <tr>
                                <th width="250">Transfer Order</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
