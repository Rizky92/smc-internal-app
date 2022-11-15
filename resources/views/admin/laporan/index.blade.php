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
            $(document).ready(function() {
                let laporanDaruratStokTable = $("#table_laporan")
                    .DataTable({
                        "serverSide": false,
                        "responsive": true,
                        "lengthChange": true,
                        "autoWidth": false,
                        "paging": true,
                        "pageLength": 25,
                        "lengthMenu": [
                            [10, 25, 50, 100, 200, -1],
                            ['10', '25', '50', '100', '200', 'Semua'],
                        ],
                        "buttons": [
                            {
                                "extend": 'excel',
                                "text": '<i class="fas fa-file-excel"></i><span class="ml-1">Export ke excel</span>',
                                "className": 'btn btn-default btn-sm',
                            }
                        ]
                    })
                    .buttons()
                    .container()
                    .appendTo('#table_laporan_wrapper .col-md-6:eq(0)');
            });
        </script>
    @endpush
@endonce
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <form method="GET" class="d-flex align-items-center justify-content-start mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="stok_minimal_nol" id="filter-stok-minimal-nol" class="custom-control-input">
                            <label class="custom-control-label" for="filter-stok-minimal-nol">Jangan tampilkan barang dengan stok minimal 0</label>
                        </div>
                        <button class="ml-3 btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-save"></i>
                            <span class="ml-1">Simpan</span>
                        </button>
                    </form>
                    <table id="table_laporan" class="table table-hover table-striped table-bordered table-sm text-sm">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Satuan besar</th>
                                <th>Isi</th>
                                <th>Satuan kecil</th>
                                <th>Kategori</th>
                                <th>Stok minimal</th>
                                <th>Stok di gudang</th>
                                <th>Saran order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daruratStok as $barang)
                                <tr>
                                    <td>{{ $barang->kode_brng }}</td>
                                    <td>{{ $barang->nama_brng }}</td>
                                    <td>{{ $barang->satuan_besar }}</td>
                                    <td>{{ $barang->isi }}</td>
                                    <td>{{ $barang->satuan_kecil }}</td>
                                    <td>{{ $barang->kategori }}</td>
                                    <td>{{ $barang->stokminimal }}</td>
                                    <td>{{ $barang->stok_di_gudang }}</td>
                                    <td>{{ $barang->saran_order <= 0 ? '0' : $barang->saran_order }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
