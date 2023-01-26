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
                let inputCaraBayar

                $(document).ready(() => {
                    inputCaraBayar = $('#caraBayar').select2({
                        dropdownCssClass: 'text-sm px-0',
                    })

                    Livewire.hook('element.updated', (el, component) => {
                        inputCaraBayar.select2({
                            dropdownCssClass: 'text-sm px-0',
                        })
                    })

                    inputCaraBayar.on('select2:select', e => {
                        @this.set('caraBayar', inputCaraBayar.val(), true)
                    })

                    inputCaraBayar.on('select2:unselect', e => {
                        @this.set('caraBayar', inputCaraBayar.val(), true)
                    })
                })
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto" constant-width>Penjamin:</x-filter.label>
                <div wire:ignore style="width: 16rem">
                    <select class="form-control form-control-sm simple-select2-sm input-sm" id="caraBayar" autocomplete="off">
                        <option value="">&nbsp;</option>
                        @foreach ($this->penjamin as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <span class="text-sm" style="width: 5rem">TOTAL:</span>
                <span class="text-sm font-weight-bold">{{ rp($this->totalTagihanPiutangPasien) }}</span>
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table style="min-width: 100%; width: 100rem">
                <x-slot name="columns">
                    <x-table.th>No. Rawat</x-table.th>
                    <x-table.th>Pasien</x-table.th>
                    <x-table.th>Tgl. Piutang</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Total</x-table.th>
                    <x-table.th>Uang Muka</x-table.th>
                    <x-table.th>Terbayar</x-table.th>
                    <x-table.th>Sisa</x-table.th>
                    <x-table.th>Tgl. Jatuh Tempo</x-table.th>
                    <x-table.th>Penjamin</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->piutangPasien as $data)
                        <x-table.tr>
                            <x-table.td>{{ $data->no_rawat }}</x-table.td>
                            <x-table.td>{{ "{$data->no_rkm_medis} {$data->nm_pasien}" }}</x-table.td>
                            <x-table.td>{{ $data->tgl_piutang }}</x-table.td>
                            <x-table.td>{{ $data->status }}</x-table.td>
                            <x-table.td>{{ rp($data->total) }}</x-table.td>
                            <x-table.td>{{ rp($data->uang_muka) }}</x-table.td>
                            <x-table.td>{{ rp($data->terbayar) }}</x-table.td>
                            <x-table.td>{{ rp($data->sisa) }}</x-table.td>
                            <x-table.td>{{ $data->tgltempo }}</x-table.td>
                            <x-table.td>{{ $data->penjamin }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->piutangPasien" />
        </x-slot>
    </x-card>
</div>
