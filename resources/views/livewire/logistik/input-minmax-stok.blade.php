<div>
    <x-flash />

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
                        dropdownCssClass: 'text-sm px-0',
                    })
                    inputStokMin = $('#stok_min')
                    inputStokMax = $('#stok_max')
                    inputStokSekarang = $('#stok_sekarang')
                    inputSaranOrder = $('#saran_order')

                    Livewire.hook('element.updated', (el, component) => {
                        console.log({el, component})
                        inputSupplier.select2({
                            dropdownCssClass: 'text-sm px-0',
                        })
                    })
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

    <x-card>
        <x-slot name="header">
            <x-card.row>
                <div class="col-2">
                    <div class="form-group">
                        <label class="text-sm" for="kode_brng">Kode Barang</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="kode_barang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="nama_brng">Nama Barang</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="nama_barang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" wire:ignore>
                        <label class="text-sm" for="supplier">Supplier</label>
                        <select class="form-control form-control-sm simple-select2-sm input-sm" id="supplier" autocomplete="off">
                            <option value="-">-</option>
                            @foreach ($this->supplier as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-card.row>
            <x-card.row>
                <div class="col-3">
                    <div class="form-group">
                        <label class="text-sm" for="stok_min">Stok minimal</label>
                        <input type="number" class="form-control form-control-sm" id="stok_min" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="text-sm" for="stok_max">Stok maksimal</label>
                        <input type="number" class="form-control form-control-sm" id="stok_max" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="text-sm" for="stok_saat_ini">Stok saat ini</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="stok_sekarang" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="text-sm" for="saran_order">Saran order</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="saran_order" readonly autocomplete="off">
                    </div>
                </div>
            </x-card.row>
            <x-card.row-col class="mt-2">
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
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Kode</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Satuan</x-table.th>
                    <x-table.th>Jenis</x-table.th>
                    <x-table.th>Supplier</x-table.th>
                    <x-table.th>Min</x-table.th>
                    <x-table.th>Max</x-table.th>
                    <x-table.th>Saat ini</x-table.th>
                    <x-table.th>Saran order</x-table.th>
                    <x-table.th>Harga Per Unit</x-table.th>
                    <x-table.th>Total Harga</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->barangLogistik as $barang)
                        <x-table.tr>
                            <x-table.td>
                                {{ $barang->kode_brng }}
                                <x-slot name="clickable" data-kode-barang="{{ $barang->kode_brng }}" data-nama-barang="{{ $barang->nama_brng }}" data-kode-supplier="{{ $barang->kode_supplier }}" data-stok-min="{{ $barang->stokmin }}" data-stok-max="{{ $barang->stokmax }}" data-stok-sekarang="{{ $barang->stok }}" data-saran-order="{{ $barang->saran_order }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $barang->nama_brng }}</x-table.td>
                            <x-table.td>{{ $barang->satuan }}</x-table.td>
                            <x-table.td>{{ $barang->jenis }}</x-table.td>
                            <x-table.td>{{ $barang->nama_supplier }}</x-table.td>
                            <x-table.td>{{ $barang->stokmin }}</x-table.td>
                            <x-table.td>{{ $barang->stokmax }}</x-table.td>
                            <x-table.td>{{ $barang->stok }}</x-table.td>
                            <x-table.td>{{ $barang->saran_order }}</x-table.td>
                            <x-table.td>{{ rp($barang->harga) }}</x-table.td>
                            <x-table.td>{{ rp($barang->total_harga) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->barangLogistik" />
        </x-slot>
    </x-card>
</div>
