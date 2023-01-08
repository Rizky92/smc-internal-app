<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
        </x-slot>
        <x-slot name="body">

        </x-slot>
        <x-slot name="footer">

        </x-slot>
    </x-card>

    <div class="card">
        @once
            @push('css')
                <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
                <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
            @endpush
            @push('js')
                <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
                <script>
                    let inputKodeBarang
                    let inputNamaBarang
                    let inputSupplier
                    let inputStokMin
                    let inputStokMax
                    let inputStokSekarang
                    let inputSaranOrder

                    $(document).ready(() => {
                        inputKodeBarang = $('#kode_barang')
                        inputNamaBarang = $('#nama_barang')
                        inputSupplier = $('#supplier').select2({
                            theme: 'bootstrap4'
                        })
                        inputStokMin = $('#stok_min')
                        inputStokMax = $('#stok_max')
                        inputStokSekarang = $('#stok_sekarang')
                        inputSaranOrder = $('#saran_order')
                    })

                    const loadData = ({
                        kodeBarang,
                        namaBarang,
                        kodeSupplier,
                        stokMin,
                        stokMax,
                        stokSekarang,
                        saranOrder
                    }) => {
                        console.log({
                            kodeBarang,
                            namaBarang,
                            kodeSupplier,
                            stokMin,
                            stokMax,
                            stokSekarang,
                            saranOrder
                        })

                        inputKodeBarang.val(kodeBarang)
                        inputNamaBarang.val(namaBarang)
                        inputSupplier.val(kodeSupplier)
                        inputStokMin.val(stokMin)
                        inputStokMax.val(stokMax)
                        inputStokSekarang.val(stokSekarang)
                        inputSaranOrder.val(saranOrder)

                        inputKodeBarang.trigger('change')
                        inputNamaBarang.trigger('change')
                        inputSupplier.trigger('change')
                        inputStokMin.trigger('change')
                        inputStokMax.trigger('change')
                        inputStokSekarang.trigger('change')
                        inputSaranOrder.trigger('change')
                    }

                    $('#simpandata').click(() => {
                        @this.simpan(
                            inputKodeBarang.val(),
                            inputStokMin.val(),
                            inputStokMax.val(),
                            inputSupplier.val()
                        )

                        inputKodeBarang.val('')
                        inputNamaBarang.val('')
                        inputSupplier.val('')
                        inputStokMin.val(0)
                        inputStokMax.val(0)
                        inputStokSekarang.val(0)
                        inputSaranOrder.val(0)

                        inputKodeBarang.trigger('change')
                        inputNamaBarang.trigger('change')
                        inputSupplier.trigger('change')
                        inputStokMin.trigger('change')
                        inputStokMax.trigger('change')
                        inputStokSekarang.trigger('change')
                        inputSaranOrder.trigger('change')
                    })
                </script>
            @endpush
        @endonce
        <div class="card-body border-bottom" id="input">
            <div class="row" wire:ignore>
                <div class="col-2">
                    <div class="form-group">
                        <label for="kode_brng">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_barang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="nama_brng">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <select class="form-control" id="supplier" autocomplete="off">
                            <option value="-">-</option>
                            @foreach ($this->supplier as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_min">Stok minimal</label>
                        <input type="number" class="form-control" id="stok_min" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_max">Stok maksimal</label>
                        <input type="number" class="form-control" id="stok_max" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="stok_saat_ini">Stok saat ini</label>
                        <input type="text" class="form-control" id="stok_sekarang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="saran_order">Saran order</label>
                        <input type="text" class="form-control" id="saran_order" readonly autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <button type="button" class="btn btn-primary btn-sm" id="simpandata">
                            <i class="fas fa-save"></i>
                            <span class="ml-1">Simpan</span>
                        </button>
                        <button type="button" wire:click="exportToExcel" class="ml-2 btn btn-default btn-sm">
                            <i class="fas fa-file-excel"></i>
                            <span class="ml-1">Export ke Excel</span>
                        </button>
                        <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                            <input type="search" class="form-control" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="searchData" />
                            <div class="input-group-append">
                                <button type="button" wire:click="searchData" class="btn btn-sm btn-default">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
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
                        <th>Harga Per Unit</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->barangLogistik as $barang)
                        <tr style="position: relative">
                            <td>
                                {{ $barang->kode_brng }}
                                <a href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0" data-kode-barang="{{ $barang->kode_brng }}" data-nama-barang="{{ $barang->nama_brng }}" data-kode-supplier="{{ $barang->kode_supplier }}" data-stok-min="{{ $barang->stokmin }}" data-stok-max="{{ $barang->stokmax }}" data-stok-sekarang="{{ $barang->stok }}" data-saran-order="{{ $barang->saran_order }}" onclick="loadData(this.dataset)"></a>
                            </td>
                            <td>{{ $barang->nama_brng }}</td>
                            <td>{{ $barang->satuan }}</td>
                            <td>{{ $barang->jenis }}</td>
                            <td>{{ $barang->nama_supplier }}</td>
                            <td>{{ $barang->stokmin }}</td>
                            <td>{{ $barang->stokmax }}</td>
                            <td>{{ $barang->stok }}</td>
                            <td>{{ $barang->saran_order }}</td>
                            <td>{{ rp($barang->harga) }}</td>
                            <td>{{ rp($barang->total_harga) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->barangLogistik->count() }} dari total {{ number_format($this->barangLogistik->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->barangLogistik->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
