@extends('layouts.admin', [
    'title' => 'Stok Minmax Barang Logistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" id="table_filter_action">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="kode_brng">Kode Barang</label>
                                <input type="text" name="kode_brng" class="form-control" id="kode_brng" readonly autocomplete="off">
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <label for="nama_brng">Nama Barang</label>
                                <input type="text" name="nama_brng" class="form-control" id="nama_brng" readonly autocomplete="off">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="stok_min">Stok minimal</label>
                                <input type="text" name="stok_min" class="form-control" id="stok_min" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-3 offset-1">
                            <div class="form-group">
                                <label for="stok_max">Stok maksimal</label>
                                <input type="text" name="stok_max" class="form-control" id="stok_max" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Satuan</th>
                                <th>Kategori</th>
                                <th>Supplier</th>
                                <th>Stok minimal</th>
                                <th>Stok saat ini</th>
                                <th>Saran order</th>
                                <th>Harga Per Unit (Rp)</th>
                                <th>Total Harga (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @foreach ($daruratStok as $barang)
                                @php($saranOrder = $barang->saran_order < 0 ? '0' : $barang->saran_order)
                                <tr>
                                    <td>{{ $barang->kode_brng }}</td>
                                    <td>{{ $barang->nama_brng }}</td>
                                    <td>{{ $barang->satuan_kecil }}</td>
                                    <td>{{ $barang->kategori }}</td>
                                    <td>{{ $barang->nama_industri }}</td>
                                    <td>{{ $barang->stokminimal }}</td>
                                    <td>{{ $barang->stok_saat_ini }}</td>
                                    <td>{{ $saranOrder }}</td>
                                    <td>{{ ceil($barang->h_beli) }}</td>
                                    <td>{{ ceil($barang->h_beli * $saranOrder) }}</td>
                                </tr>
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
