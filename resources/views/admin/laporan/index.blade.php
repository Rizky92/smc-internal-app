@extends('layouts.admin', [
    'title' => 'Darurat Stok',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title w-100">
                        <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                            Filter table
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="collapse show" data-parent="#accordion">
                    <div class="card-body">
                        <form method="GET">
                            <div class="form-group">
                                <label for="filter-perpage">Per halaman</label>
                                <select class="custom-select form-control-border" id="filter-perpage" name="perpage">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="filter-perpage">Per halaman</label>
                                <select class="custom-select form-control-border" id="filter-perpage" name="perpage">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-start align-items-center">
                    <h3 class="card-title">Darurat Stok</h3>

                    <div class="d-flex align-items-center ml-auto">
                        <button type="button" class="btn btn-light">
                            Aksi
                        </button>
                        <div class="input-group input-group-sm">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0" style="height: auto;">
                    <table class="table table-head-fixed text-nowrap">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Satuan</th>
                                <th>Stok Minimal</th>
                                <th>Jenis</th>
                                <th>Stok Di Gudang</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daruratStok as $barang)
                                <tr>
                                    <td>{{ $barang->kode_brng }}</td>
                                    <td>{{ $barang->nama_brng }}</td>
                                    <td>{{ $barang->satuan }}</td>
                                    <td>{{ $barang->stokminimal }}</td>
                                    <td>{{ $barang->nama }}</td>
                                    <td>{{ $barang->stok_saat_ini }}</td>
                                    <td>{{ $barang->selisih_stok }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    {{ $daruratStok->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
