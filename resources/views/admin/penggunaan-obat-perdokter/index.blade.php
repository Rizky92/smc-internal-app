@extends('layouts.admin', [
    'title' => 'Darurat Stok',
])

@once
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
            var laporanDaruratStokTable;

            $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
                let filterStokMinimalLebihDariNol = $('#filter_stok_minimal_nol').is(':checked')

                if (parseFloat(data[5]) > 0) {
                    return true
                }

                return filterStokMinimalLebihDariNol
            })

            $(document).ready(() => {
                laporanDaruratStokTable = $("#table_laporan")
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
                        columnDefs: [
                            {
                                target: 8,
                                render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. ')
                            }
                        ],
                        buttons: [
                            {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel"></i><span class="ml-1">Export ke excel</span>',
                                className: 'btn btn-default btn-sm',
                            }
                        ]
                    })
                
                laporanDaruratStokTable
                    .buttons()
                    .container()
                    .appendTo('#table_filter_action .d-flex')
            })

            $('#filter_stok_minimal_nol').change(() => {
                laporanDaruratStokTable.draw()
            })
        </script>
    @endpush
@endonce
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-0" id="table_filter_action">
                    <div class="d-flex justify-content-start align-items-center">
                        <div class="mr-auto">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="stok_minimal_nol" id="filter_stok_minimal_nol" class="custom-control-input">
                                <label class="custom-control-label" for="filter_stok_minimal_nol">Tampilkan barang dengan stok minimal 0</label>
                            </div>
                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" name="saran_order_nol" id="filter_saran_order_nol" class="custom-control-input">
                                <label class="custom-control-label" for="filter_saran_order_nol">Tampilkan barang dengan saran order 0</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_laporan" class="table table-hover table-striped table-bordered table-sm text-sm">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Nama Item</th>
                                <th>Qty</th>
                                <th>Dokter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($obatPerDokter as $dataObat)
                                @php($saranOrder = $barang->saran_order < 0 ? 0 : $barang->saran_order)
                                <tr>
                                    <td>{{ $barang->kode_brng }}</td>
                                    <td>{{ $barang->nama_brng }}</td>
                                    <td>{{ $barang->satuan_kecil }}</td>
                                    <td>{{ $barang->kategori }}</td>
                                    <td>{{ $barang->nama_industri }}</td>
                                    <td>{{ $barang->stokminimal }}</td>
                                    <td>{{ $barang->stok_di_gudang }}</td>
                                    <td>{{ $saranOrder }}</td>
                                    <td>{{ rp($barang->h_beli) }}</td>
                                    <td>{{ rp($barang->h_beli * $saranOrder) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
