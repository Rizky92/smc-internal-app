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
                <div class="card-body table-responsive p-0">
                    <table id="table_index" class="table table-hover table-striped table-sm text-sm" style="width: 180rem">
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
                                    <th class="text-center">{{ $b }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>TOTAL KUNJUNGAN</th>
                            </tr>
                            <tr>
                                <th>Kunjungan Rawat Jalan</th>
                            </tr>
                            <tr>
                                <th>Kunjungan Rawat Inap</th>
                            </tr>
                            <tr>
                                <th>Kunjungan IGD</th>
                            </tr>
                            <tr>
                                <th>Kunjungan <i>Walk in</i></th>
                            </tr>
                            <tr>
                                <th>TOTAL PENDAPATAN</th>
                            </tr>
                            <tr>
                                <th>Pendapatan Obat Rawat Jalan</th>
                            </tr>
                            <tr>
                                <th>Pendapatan Obat Rawat Inap</th>
                            </tr>
                            <tr>
                                <th>Pendapatan Obat IGD</th>
                            </tr>
                            <tr>
                                <th>Pendapatan Obat <i>Walk in</i></th>
                            </tr>
                            <tr>
                                <th>Pendapatan Alkes Farmasi dan Unit</th>
                            </tr>
                            <tr>
                                <th>Retur Obat</th>
                            </tr>
                            <tr>
                                <th>Pembelian Farmasi</th>
                            </tr>
                            <tr>
                                <th>Retur Supplier</th>
                            </tr>
                            <tr>
                                <th>TOTAL PEMBELIAN (Pembelian Farmasi - Retur Supplier)</th>
                            </tr>
                            <tr>
                                <th>Pemakaian BHP</th>
                            </tr>
                            <tr>
                                <th>Transfer Order</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
