<div>
    <x-flash />

    @once
        @push('css')
            <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet">
            <link href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
        @endpush
        @push('js')
            <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
            <script>
                let inputBangsal

                $(document).ready(() => {
                    inputBangsal = $('#bangsal').select2({
                        dropdownCssClass: 'text-sm px-0',
                    })

                    inputBangsal.on('select2:select', e => {
                        @this.set('kodeBangsal', inputBangsal.val(), true)
                    })

                    inputBangsal.on('select2:unselect', e => {
                        @this.set('kodeBangsal', inputBangsal.val(), true)
                    })
                })
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.label constant-width>Ruangan:</x-filter.label>
                <div wire:ignore class="w-25">
                    <select class="form-control form-control-sm simple-select2-sm input-sm" id="bangsal" autocomplete="off">
                        <option value="-">-</option>
                        @foreach ($this->bangsal as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage :constantWidth="true" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Ruangan</x-table.th>
                    <x-table.th>Kode</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Satuan</x-table.th>
                    <x-table.th>Harga</x-table.th>
                    <x-table.th>Stok saat ini</x-table.th>
                    <x-table.th>Projeksi Harga</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->stokObatPerRuangan as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->nm_bangsal }}</x-table.td>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->satuan }}</x-table.td>
                            <x-table.td>{{ rp($obat->h_beli) }}</x-table.td>
                            <x-table.td>{{ $obat->stok }}</x-table.td>
                            <x-table.td>{{ rp($obat->projeksi_harga) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->stokObatPerRuangan" />
        </x-slot>
    </x-card>
</div>
