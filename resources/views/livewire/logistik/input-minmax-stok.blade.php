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

                let buttonSimpan
                let buttonBatalSimpan
                let buttonResetFilters

                $(document).ready(() => {
                    inputKodeBarang = $('#kode_barang')
                    inputNamaBarang = $('#nama_barang')
                    inputSupplier = $('#supplier').select2({
                        dropdownCssClass: 'text-sm px-0'
                    })
                    inputStokMin = $('#stok_min')
                    inputStokMax = $('#stok_max')
                    inputStokSekarang = $('#stok_sekarang')
                    inputSaranOrder = $('#saran_order')

                    buttonSimpan = $('#simpan-data')
                    buttonBatalSimpan = $('#batal-simpan')
                    buttonResetFilters = $('button#reset-filter')

                    buttonSimpan.prop('disabled', true)
                    buttonBatalSimpan.prop('disabled', true)

                    buttonSimpan.click(e => @this.simpan(
                        inputKodeBarang.val(),
                        inputStokMin.val(),
                        inputStokMax.val(),
                        inputSupplier.val()
                    ))

                    buttonResetFilters.click(clearData)
                    buttonBatalSimpan.click(clearData)

                    Livewire.hook('element.updated', (el, component) => {
                        inputSupplier.select2({
                            dropdownCssClass: 'text-sm px-0',
                        })
                    })

                    $(this).on('data-tersimpan', clearData)
                })

                function loadData(e) {
                    let { kodeBarang, namaBarang, kodeSupplier, stokMin, stokMax, stokSekarang, saranOrder } = e.dataset
                    
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

                    buttonSimpan.prop('disabled', false)
                    buttonBatalSimpan.prop('disabled', false)
                }

                function clearData() {
                    inputKodeBarang.val('')
                    inputNamaBarang.val('')
                    inputSupplier.val('')
                    inputStokMin.val('')
                    inputStokMax.val('')
                    inputStokSekarang.val('')
                    inputSaranOrder.val('')

                    inputKodeBarang.trigger('change')
                    inputNamaBarang.trigger('change')
                    inputSupplier.trigger('change')
                    inputStokMin.trigger('change')
                    inputStokMax.trigger('change')
                    inputStokSekarang.trigger('change')
                    inputSaranOrder.trigger('change')

                    buttonSimpan.prop('disabled', true)
                    buttonBatalSimpan.prop('disabled', true)
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            @canany(['logistik.input-minmax-stok.create', 'logistik.input-minmax-stok.update'])
                <x-card.row>
                    <div class="col-12 col-md-6 col-lg-2">
                        <div class="form-group">
                            <label class="text-sm" for="kode_brng">Kode Barang</label>
                            <input type="text" class="form-control form-control-sm" id="kode_barang" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                            <label class="text-sm" for="nama_brng">Nama Barang</label>
                            <input type="text" class="form-control form-control-sm" id="nama_barang" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="form-group" wire:ignore>
                            <label class="text-sm" for="supplier">Supplier</label>
                            <x-filter.select2 name="supplier" :collection="$this->supplier" />
                        </div>
                    </div>
                </x-card.row>

                <x-card.row>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok_min">Stok minimal</label>
                            <input type="number" class="form-control form-control-sm" id="stok_min" min="0" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok_max">Stok maksimal</label>
                            <input type="number" class="form-control form-control-sm" id="stok_max" min="0" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok_saat_ini">Stok saat ini</label>
                            <input type="text" class="form-control form-control-sm" id="stok_sekarang" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="saran_order">Saran order</label>
                            <input type="text" class="form-control form-control-sm" id="saran_order" readonly autocomplete="off">
                        </div>
                    </div>
                </x-card.row>

                <x-card.row-col class="pb-3 border-bottom">
                    <x-button disabled class="btn-primary" id="simpan-data" title="Simpan" icon="fas fa-save" />
                    <x-button disabled class="btn-default ml-2" id="batal-simpan" title="Batal" />
                </x-card.row-col>
            @endcanany

            <x-card.row-col :class="Arr::toCssClasses(['mt-3' => auth()->user()->canAny(['logistik.input-minmax-stok.create', 'logistik.input-minmax-stok.update'])])">
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>

            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="satuan" title="Satuan" />
                    <x-table.th name="jenis" title="Jenis" />
                    <x-table.th name="nama_supplier" title="Supplier" />
                    <x-table.th name="stokmin" title="Min" />
                    <x-table.th name="stokmax" title="Max" />
                    <x-table.th name="stok" title="Saat ini" />
                    <x-table.th name="saran_order" title="Saran order" />
                    <x-table.th name="harga" title="Harga Per Unit" />
                    <x-table.th name="total_harga" title="Total Harga" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->barangLogistik as $barang)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-kode-barang="{{ $barang->kode_brng }}"
                                data-nama-barang="{{ $barang->nama_brng }}"
                                data-kode-supplier="{{ $barang->kode_supplier }}"
                                data-stok-min="{{ $barang->stokmin }}"
                                data-stok-max="{{ $barang->stokmax }}"
                                data-stok-sekarang="{{ $barang->stok }}"
                                data-saran-order="{{ $barang->saran_order }}"
                            >
                                {{ $barang->kode_brng }}
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
