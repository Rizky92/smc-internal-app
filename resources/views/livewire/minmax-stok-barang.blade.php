<div class="card">
    @push('css')
        <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    @endpush
    @push('js')
        <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(() => {
                $('.select2').select2({
                    theme: 'bootstrap4'
                })
            })
        </script>
    @endpush
    <div class="card-body" id="input">
        <div class="row">
            <div class="col-2">
                <div class="form-group">
                    <label for="kode_brng">Kode Barang</label>
                    <input type="text" name="kode_brng" class="form-control" id="kode_brng" readonly autocomplete="off" value="{{ old('kode_brng', $kodeBarang) }}">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="nama_brng">Nama Barang</label>
                    <input type="text" name="nama_brng" class="form-control" id="nama_brng" readonly autocomplete="off" value="{{ old('nama_brng', $namaBarang) }}">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="supplier">Supplier</label>
                    <select class="form-control select2" name="supplier" id="supplier">
                        @foreach ($supplier as $kode => $nama)
                            <option value="{{ $kode }}" {{ old('supplier', $supplier) == $kode ? 'selected' : null }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="stok_min">Stok minimal</label>
                    <input type="text" name="stok_min" class="form-control" id="stok_min" autocomplete="off" value="{{ old('stok_min', $stokMin) }}">
                </div>
            </div>
            <div class="col-3 offset-1">
                <div class="form-group">
                    <label for="stok_max">Stok maksimal</label>
                    <input type="text" name="stok_max" class="form-control" id="stok_max" autocomplete="off" value="{{ old('stok_max', $stokMax) }}">
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
                    <th>Jenis</th>
                    <th>Supplier</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Saat ini</th>
                    <th>Saran order</th>
                    <th>Harga Per Unit (Rp)</th>
                    <th>Total Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($barang as $item)
                    <tr style="position: relative">
                        <td>
                            {{ $item->kode_brng }}
                            <a href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0" wire:click="getItem('{{ $item->kode_brng }}')"></a>
                        </td>
                        <td>{{ $item->nama_brng }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->supplier }}</td>
                        <td>{{ $item->stokmin }}</td>
                        <td>{{ $item->stokmax }}</td>
                        <td>{{ $item->stok }}</td>
                        <td>{{ $item->saran_order }}</td>
                        <td>{{ $item->harga }}</td>
                        <td>{{ $item->total_harga }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
