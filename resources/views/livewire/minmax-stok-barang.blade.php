<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                Proses ekspor dimulai! Mohon jangan ditutup halaman ini. Silahkan tunggu sekiranya 1 menit agar file selesai diproses.
            </p>
            <p>
                Klik refresh untuk cek apakah file sudah diekspor.
                <button type="button" wire:click="refreshPage" class="btn btn-sm btn-light">
                    <i class="fas fa-sync"></i>
                    <span>refresh</span>
                </button>
            </p>
        </div>
    @endif

    @if (session()->has('excel.exported'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('excel.exported') }}
                <a href="{{ session('excel.download') }}" class="btn btn-sm btn-dark text-decoration-none ml-1">
                    <i class="fas fa-download"></i>
                    <span class="ml-1">Download.</span>
                </a>
            </p>
        </div>
    @endif

    @if (session()->has('saved.content'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('saved.content') }}
            </p>
        </div>
    @endif

    <div class="card">
        @once
            @push('css')
                <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
                <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
            @endpush
            @push('js')
                <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
                <script>
                    let supplierSelectComponent

                    $(document).ready(() => {
                        supplierSelectComponent = $('.select2').select2({
                            theme: 'bootstrap4'
                        })
                    })

                    const loadData = (barang, supplier) => {
                        @this.getItem(barang)

                        supplierSelectComponent.val(supplier)

                        supplierSelectComponent.trigger('change')
                    }

                    $('#simpandata').click(() => @this.simpan(supplierSelectComponent.val()))
                </script>
            @endpush
        @endonce
        <div class="card-body border-bottom" id="input">
            <div class="row">
                <div class="col-2">
                    <div class="form-group">
                        <label for="kode_brng">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_brng" readonly autocomplete="off" wire:model.defer="kodeBarang">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="nama_brng">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_brng" readonly autocomplete="off" wire:model.defer="namaBarang">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" wire:ignore>
                        <label for="supplier">Supplier</label>
                        <select class="form-control select2" name="supplier" id="supplier">
                            @foreach ($supplier as $kode => $nama)
                                <option value="{{ $kode }}" {{ old('kd_supplier', $kodeSupplier) == $kode ? 'selected' : null }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_min">Stok minimal</label>
                        <input type="number" class="form-control" id="stok_min" min="0" autocomplete="off" wire:model.defer="stokMin">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_max">Stok maksimal</label>
                        <input type="number" class="form-control" id="stok_max" min="0" autocomplete="off" wire:model.defer="stokMax">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_saat_ini">Stok saat ini</label>
                        <input type="text" class="form-control" id="stok_saat_ini" autocomplete="off" disabled wire:model.defer="stokSekarang">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="saran_order">Saran order</label>
                        <input type="text" class="form-control" id="saran_order" autocomplete="off" disabled wire:model.defer="saranOrder">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <div class="input-group input-group-sm w-25">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-default" wire:click="$emit('refreshData')">
                                    <i class="fas fa-sync"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                            <input type="search" id="cari" name="cari" class="form-control" wire:model.defer="cari" wire:keydown.enter.stop="$emit('refreshData')">
                        </div>
                        <div class="custom-control custom-switch ml-3">
                            <input type="checkbox" class="custom-control-input" id="tampilkanSaranOrderNol" wire:model.defer="tampilkanSaranOrderNol">
                            <label class="custom-control-label text-sm" for="tampilkanSaranOrderNol">Tampilkan barang dengan saran order nol</label>
                        </div>
                        <button type="button" wire:click="exportToExcel" class="ml-auto btn btn-default btn-sm">
                            <i class="fas fa-file-excel"></i>
                            <span class="ml-1">Export ke Excel</span>
                        </button>
                        <button type="button" class="ml-2 btn btn-primary btn-sm" id="simpandata">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="position:relative">
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
                    @foreach ($barangLogistik as $barang)
                        <tr style="position: relative">
                            <td>
                                {{ $barang->kode_brng }}
                                <a href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0"
                                    data-kode-barang="{{ $barang->kode_brng }}"
                                    data-nama-barang="{{ $barang->nama_brng }}"
                                    data-kode-supplier="{{ $barang->kode_supplier }}"
                                    data-stok-min="{{ $barang->stok_min }}"
                                    data-stok-max="{{ $barang->stok_max }}"
                                    data-stok-skrg="{{ $barang->stok }}"
                                    data-saran-order="{{ $barang->saran_order }}"
                                    onclick="loadData('{{ $barang->kode_brng }}', '{{ $barang->kode_supplier }}')"
                                ></a>
                            </td>
                            <td>{{ $barang->nama_brng }}</td>
                            <td>{{ $barang->satuan }}</td>
                            <td>{{ $barang->jenis }}</td>
                            <td>{{ $barang->nama_supplier }}</td>
                            <td>{{ $barang->stokmin }}</td>
                            <td>{{ $barang->stokmax }}</td>
                            <td>{{ $barang->stok }}</td>
                            <td>{{ $barang->saran_order }}</td>
                            <td>{{ $barang->harga }}</td>
                            <td>{{ $barang->total_harga }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $barangLogistik->count() }} dari total {{ number_format($barangLogistik->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $barangLogistik->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
