@extends('layouts.admin', [
    'title' => 'Laporan Produksi Farmasi Per Tahun',
])

{{-- @once
    @push('css')
        <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    @endpush
    @push('js')
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
        <script>
            var mainTable;

            $(document).ready(() => {
                mainTable = $("#table_index")
                    .DataTable({
                        autoWidth: false,
                        responsive: true,
                        lengthChange: true,
                        paging: true,
                        pageLength: 25,
                        lengthMenu: [
                            [10, 25, 50, 100, 200, -1],
                            ['10', '25', '50', '100', '200', 'Semua'],
                        ],
                        buttons: [
                            {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel"></i><span class="ml-1">Export ke excel</span>',
                                className: 'btn btn-default btn-sm',
                            }
                        ]
                    })
                
                mainTable
                    .buttons()
                    .container()
                    .appendTo('#table_filter_action .d-flex')
            })
        </script>
    @endpush
@endonce --}}
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-0" id="table_filter_action">
                    <div class="d-flex justify-content-end align-items-center">
                        
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th width="250">Kunjungan TOTAL</th>
                                <th width="250">Inpatient</th>
                                <th width="250">Outpatient</th>
                                <th width="250">Emergency</th>
                                <th width="250">Walk in</th>
                                <th width="250">Pendapatan TOTAL</th>
                                <th width="250">Pendapatan Obat Inpatient</th>
                                <th width="250">Pendapatan Obat Outpatient</th>
                                <th width="250">Pendapatan Obat Emergency</th>
                                <th width="250">Pendapatan Obat Walk in</th>
                                <th width="250">Pendapatan Alkes Farmasi dan Unit</th>
                                <th width="250">Drug Return</th>
                                <th width="250">pembelian Farmasi</th>
                                <th width="250">Retur Supplier</th>
                                <th width="250">Pembelian TOTAL (Pembelian Farmasi - Retur Supplier)</th>
                                <th width="250">Pemakaian BHP</th>
                                <th width="250">Transfer Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($dataLaporan = [
                                'Januari' => 1000,
                                'Februari' => 1000,
                                'Maret' => 1000,
                                'April' => 1000,
                                'Mei' => 1000,
                                'Juni' => 1000,
                                'Juli' => 1000,
                                'Agustus' => 1000,
                                'September' => 1000,
                                'Oktober' => 1000,
                                'November' => 1000,
                                'Desember' => 1000,
                            ])
                            @foreach ($dataLaporan as $bulan => $data)
                                <tr>
                                    <td>{{ $bulan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
