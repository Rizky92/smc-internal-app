@extends('layouts.admin', [
    'title' => 'Laporan Statistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table id="table_index" class="table table-hover table-striped table-bordered table-sm text-sm">
                        <thead>
                            <tr>
                                <th>No. Rawat</th>
                                <th>No. Rekam Medis</th>
                                <th>Nama Pasien</th>
                                <th>NIK</th>
                                <th>Agama</th>
                                <th>Suku</th>
                                <th>Kebangsaan</th>
                                <th>Pasien Lama / Baru</th>
                                <th>Waktu Masuk</th>
                                <th>Tgl. Masuk</th>
                                <th>Tgl. Pulang</th>
                                <th>Tgl. Lahir</th>
                                <th>L / P</th>
                                <th>Umur</th>
                                <th>Diagnosa Masuk</th>
                                <th>Diagnosa Primer</th>
                                <th>ICD Primer</th>
                                <th>Diagnosa Sekunder</th>
                                <th>ICD Sekunder</th>
                                <th>Tindakan</th>
                                <th>ICD_9CM</th>
                                <th>Lama Operasi</th>
                                <th>Rujukan Asal</th>
                                <th>DPJP</th>
                                <th>Perawatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daruratStok as $barang)
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
