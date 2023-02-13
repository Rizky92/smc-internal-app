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
                let inputKodeRekening

                $(document).ready(() => {
                    inputKodeRekening = $('#kode-rekening').select2({
                        dropdownCssClass: 'text-sm px-0',
                    })

                    Livewire.hook('element.updated', (el, component) => {
                        inputKodeRekening.select2({
                            dropdownCssClass: 'text-sm px-0',
                        })
                    })

                    inputKodeRekening.on('select2:select', e => {
                        @this.set('kodeRekening', inputKodeRekening.val(), true)
                    })

                    inputKodeRekening.on('select2:unselect', e => {
                        @this.set('kodeRekening', inputKodeRekening.val(), true)
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
                <x-filter.label class="ml-auto" constant-width>Rekening :</x-filter.label>
                <div wire:ignore style="width: 16rem">
                    <select class="form-control form-control-sm simple-select2-sm input-sm" id="kode-rekening" autocomplete="off">
                        <option value="">&nbsp;</option>
                        @foreach ($this->rekening as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                <x-slot name="columns">
                    <x-table.th name="tgl_jurnal" title="Tgl." style="width: 13ch" />
                    <x-table.th name="jam_jurnal" title="Jam" style="width: 9ch" />
                    <x-table.th name="no_jurnal" title="No. Jurnal" style="width: 15ch" />
                    <x-table.th name="no_bukti" title="No. Bukti" style="width: 17ch" />
                    <x-table.th name="keterangan" title="Keterangan" />
                    <x-table.th name="kd_rek" title="Rekening" style="width: 12ch" />
                    <x-table.th name="debet" title="Debet" style="width: 20ch" />
                    <x-table.th name="kredit" title="Kredit" style="width: 20ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->bukuBesar as $jurnal)
                        <x-table.tr>
                            <x-table.td>{{ $jurnal->tgl_jurnal }}</x-table.td>
                            <x-table.td>{{ $jurnal->jam_jurnal }}</x-table.td>
                            <x-table.td>{{ $jurnal->no_jurnal }}</x-table.td>
                            <x-table.td>{{ $jurnal->no_bukti }}</x-table.td>
                            <x-table.td>{{ $jurnal->keterangan }}</x-table.td>
                            <x-table.td>{{ $jurnal->kd_rek }}</x-table.td>
                            <x-table.td>{{ rp($jurnal->debet) }}</x-table.td>
                            <x-table.td>{{ rp($jurnal->kredit) }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty :colspan="8" />
                    @endforelse
                </x-slot>
                <x-slot name="footer">
                    <x-table.tr>
                        <x-table.th colspan="5" />
                        <x-table.th title="TOTAL :" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->debet)" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->kredit)" />
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->bukuBesar" />
        </x-slot>
    </x-card>
</div>
