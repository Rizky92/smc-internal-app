<div wire:init="loadProperties">
    <x-flash />

    @once
        @push('js')
            <script src="{{ asset('js/select2.full.min.js') }}"></script>
            <script>
                const inputKodeBarang = $('input#kode-barang')
                const inputNamaBarang = $('input#nama-barang')
                const inputSupplier = $('select#supplier')
                const inputStokMin = $('input#stok-min')
                const inputStokMax = $('input#stok-max')
                const inputStokSekarang = $('input#stok-sekarang')
                const inputSaranOrder = $('input#saran-order')

                const buttonSimpan = $('button#simpan-data')
                const buttonBatalSimpan = $('button#batal-simpan')
                const buttonResetFilters = $('button#reset-filter')

                $(document).ready(() => {
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

    <x-card use-loading>
        <x-slot name="header">
            @canany(['logistik.input-minmax-stok.create', 'logistik.input-minmax-stok.update'])
                <x-row>
                    <div class="col-12 col-md-6 col-lg-2">
                        <div class="form-group">
                            <label class="text-sm" for="kode-barang">Kode Barang</label>
                            <input type="text" class="form-control form-control-sm" id="kode-barang" readonly autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                            <label class="text-sm" for="nama-barang">Nama Barang</label>
                            <input type="text" class="form-control form-control-sm" id="nama-barang" readonly autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="text-sm" for="supplier">Supplier</label>
                            <x-filter.select2 livewire name="supplier" show-key :options="$this->supplier" placeholder="-" placeholder-value="-" width="100%" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok-min">Stok minimal</label>
                            <input type="number" class="form-control form-control-sm" id="stok-min" min="0" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok-max">Stok maksimal</label>
                            <input type="number" class="form-control form-control-sm" id="stok-max" min="0" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="stok-sekarang">Stok saat ini</label>
                            <input type="text" class="form-control form-control-sm" id="stok-sekarang" readonly autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                            <label class="text-sm" for="saran-order">Saran order</label>
                            <input type="text" class="form-control form-control-sm" id="saran-order" readonly autocomplete="off" />
                        </div>
                    </div>
                </x-row>

                <x-row-col class="pb-3 border-bottom">
                    <x-button disabled size="sm" variant="primary" id="simpan-data" title="Simpan" icon="fas fa-save" />
                    <x-button disabled size="sm" class="ml-2" id="batal-simpan" title="Batal" />
                </x-row-col>
            @endcanany

            <x-row-col-flex
                :class="Arr::toCssClasses([
                    'mt-3' => auth()
                        ->user()
                        ->canAny(['logistik.input-minmax-stok.create', 'logistik.input-minmax-stok.update']),
                ])">
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>

            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>

        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
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
                    @forelse ($this->barangLogistik as $barang)
                        <x-table.tr>
                            <x-table.td
                                clickable
                                data-kode-barang="{{ $barang->kode_brng }}"
                                data-nama-barang="{{ $barang->nama_brng }}"
                                data-kode-supplier="{{ $barang->kode_supplier }}"
                                data-stok-min="{{ $barang->stokmin }}"
                                data-stok-max="{{ $barang->stokmax }}"
                                data-stok-sekarang="{{ $barang->stok }}"
                                data-saran-order="{{ $barang->saran_order }}">
                                {{ $barang->kode_brng }}
                            </x-table.td>
                            <x-table.td>
                                {{ $barang->nama_brng }}
                            </x-table.td>
                            <x-table.td>{{ $barang->satuan }}</x-table.td>
                            <x-table.td>{{ $barang->jenis }}</x-table.td>
                            <x-table.td>
                                {{ $barang->nama_supplier }}
                            </x-table.td>
                            <x-table.td>{{ $barang->stokmin }}</x-table.td>
                            <x-table.td>{{ $barang->stokmax }}</x-table.td>
                            <x-table.td>{{ $barang->stok }}</x-table.td>
                            <x-table.td>
                                {{ $barang->saran_order }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($barang->harga) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($barang->total_harga) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->barangLogistik" />
        </x-slot>
    </x-card>
</div>
