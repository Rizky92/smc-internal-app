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
                let inputBangsal

                $(document).ready(() => {
                    inputBangsal = $('#bangsal').select2({
                        dropdownCssClass: 'text-sm px-0',
                        selectionCssClass: 'm-0 p-0 bg-dark'
                    })
                })

                $('#bangsal').change(() => @this.set('kodeBangsal', $('#bangsal').val(), true))

                @this.on('searchData', () => {
                    inputBangsal.trigger('change')
                })
            </script>
        @endpush
    @endonce

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center" wire:ignore>
                                <span class="text-sm pr-2">Ruangan:</span>
                                <select class="form-control form-control-sm simple-select2-sm input-sm" id="bangsal" autocomplete="off">
                                    <option value="-">-</option>
                                    @foreach ($this->bangsal as $kode => $nama)
                                        <option value="{{ $kode }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- <span class="text-sm pr-4">Periode:</span>
                        <input class="form-control form-control-sm w-25" type="date" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input class="form-control form-control-sm w-25" type="date" wire:model.defer="periodeAkhir" /> --}}
                        <div class="ml-auto">
                            <button class="btn btn-default btn-sm" type="button" wire:click="exportToExcel">
                                <i class="fas fa-file-excel"></i>
                                <span class="ml-1">Export ke Excel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <x-filter />
            </div>
        </div>
        <div class="card-body table-responsive p-0 border-top">
            <table class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Stok saat ini</th>
                        {{-- <th>SO terakhir</th>
                        <th>Tgl. SO Terakhir</th> --}}
                        <th>Projeksi Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->stokObatPerRuangan as $obat)
                        <tr>
                            <td>{{ $obat->nm_bangsal }}</td>
                            <td>{{ $obat->kode_brng }}</td>
                            <td>{{ $obat->nama_brng }}</td>
                            <td>{{ $obat->satuan }}</td>
                            <td>{{ rp($obat->h_beli) }}</td>
                            <td>{{ $obat->stok }}</td>
                            {{-- <td>{{ $obat->so_terakhir }}</td>
                            <td>{{ $obat->tgl_so_terakhir }}</td> --}}
                            <td>{{ rp($obat->projeksi_harga) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->stokObatPerRuangan->count() }} dari total {{ number_format($this->stokObatPerRuangan->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->stokObatPerRuangan->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
