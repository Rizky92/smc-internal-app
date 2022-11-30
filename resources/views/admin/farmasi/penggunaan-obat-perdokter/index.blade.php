@extends('layouts.admin', [
    'title' => 'Penggunaan Obat Per Dokter Peresep',
])

{{-- @once
    @push('css')
        <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
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

        <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
        <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
        <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
        <script>
            var mainTable;

            $(document).ready(() => {
                $('#datemin').datetimepicker({
                    format: 'yyyy-MM-DD'
                })

                $('#datemax').datetimepicker({
                    format: 'yyyy-MM-DD'
                })

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
                        buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel"></i><span class="ml-1">Export ke excel</span>',
                            className: 'btn btn-default btn-sm',
                        }],
                    })

                mainTable
                    .buttons()
                    .container()
                    .appendTo('#table_filter_action')
            })
        </script>
    @endpush
@endonce --}}
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-0">
                    <div class="d-flex justify-content-end align-items-center" id="table_filter_action">
                        <div class="d-flex align-items-center mr-auto">
                            <span class="text-sm pr-4">Periode:</span>
                            <div class="input-group input-group-sm date w-25" id="datemin" data-target-input="nearest">
                                <div class="input-group-prepend" data-target="#datemin" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                                <input type="text" name="datemin" class="form-control datetimepicker-input" data-target="#datemin" />
                            </div>
                            <span class="text-sm px-2">Sampai</span>
                            <div class="input-group input-group-sm date w-25" id="datemax" data-target-input="nearest">
                                <div class="input-group-prepend" data-target="#datemax" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                                <input type="text" name="datemin" class="form-control datetimepicker-input" data-target="#datemax" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_index" class="table table-hover table-striped table-bordered table-sm text-sm">
                        <thead>
                            <tr>
                                <th>No. Resep</th>
                                <th>Tanggal Peresepan</th>
                                <th>Nama Obat</th>
                                <th>Jumlah</th>
                                <th>Dokter Peresep</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($obatPerDokter as $dataObat)
                                <tr>
                                    <td>{{ $dataObat->no_resep }}</td>
                                    <td>{{ $dataObat->tgl_peresepan }}</td>
                                    <td>{{ $dataObat->nama_brng }}</td>
                                    <td>{{ $dataObat->jml }}</td>
                                    <td>{{ $dataObat->nm_dokter }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items center justify-content-start">
                        <p class="text-muted">Menampilkan {{ $obatPerDokter->count() }} dari total {{ number_format($obatPerDokter->total(), 0, ',', '.') }} item.</p>
                        <div class="ml-auto">
                            {{ $obatPerDokter->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
