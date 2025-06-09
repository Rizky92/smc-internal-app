<div wire:init="loadProperties">
    <x-flash />

    @can('keuangan.posting-jurnal.create')
        <livewire:pages.keuangan.modal.input-jurnal-posting />

        @once
            @push('js')
                <script>
                    function loadData(e) {
                        let { keterangan } = e.dataset
                        @this.emit('prepare', { keterangan: keterangan })
                        $('#modal-input-posting-jurnal').modal('show');
                    }
                </script>
            @endpush
        @endonce
    @endcan

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-button class="ml-auto" outline="true" variant="primary" size="sm" title="Print" icon="fas fa-print" wire:click="cetak" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="pt-3 border-top">
                <x-filter.label constant-width>Jenis:</x-filter.label>
                <x-filter.select model="jenis" :options="['U' => 'Umum', 'P' => 'Penyesuaian']" />
                @can('keuangan.posting-jurnal.create')
                    <x-button variant="primary" size="sm" title="Jurnal Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-posting-jurnal" class="btn-primary ml-auto" />
                @endcan
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table id="printTable" style="min-width: 100%" sticky nowrap borderless>
                <x-slot name="columns">
                    <x-table.th title="No. Jurnal" />
                    <x-table.th title="No. Bukti" />
                    <x-table.th title="Tgl. Jurnal" />
                    <x-table.th title="Jenis" />
                    <x-table.th title="Keterangan" />
                    <x-table.th title="Kode" />
                    <x-table.th title="Rekening" />
                    <x-table.th-money title="Debet" />
                    <x-table.th-money title="Kredit" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataJurnalPosting as $item)
                        @php
                            $odd = $loop->iteration % 2 === 0 ? '255 255 255' : '247 247 247';
                            $count = $item->detail->count();
                            $firstDetail = $item->detail->first();
                        @endphp

                        <x-table.tr style="background-color: rgb({{ $odd }})">
                            <x-table.td rowspan="{{ $count }}">
                                {{ $item->no_jurnal }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $item->no_bukti }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $item->tgl_jurnal }}
                                {{ $item->jam_jurnal }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $item->jenis === 'U' ? 'Umum' : 'Penyesuaian' }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $item->keterangan }}
                            </x-table.td>
                            <x-table.td>
                                {{ $firstDetail->kd_rek }}
                            </x-table.td>
                            <x-table.td>
                                @if ($firstDetail->kredit > 0)
                                    &emsp;&emsp;
                                @endif

                                {{ $firstDetail->rekening->nm_rek }}
                            </x-table.td>
                            <x-table.td-money :value="$firstDetail->debet" />
                            <x-table.td-money :value="$firstDetail->kredit" />
                        </x-table.tr>
                        @if ($count > 1)
                            @foreach ($item->detail->skip(1) as $detail)
                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td class="p-1 border-0">
                                        {{ $detail->kd_rek }}
                                    </x-table.td>
                                    <x-table.td class="border-0">
                                        &emsp;&emsp;
                                        {{ $detail->rekening->nm_rek }}
                                    </x-table.td>
                                    <x-table.td-money :value="$detail->debet" />
                                    <x-table.td-money :value="$detail->kredit" />
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                    <x-table.tr style="font-weight: bold">
                        <x-table.td>TOTAL :</x-table.td>
                        <x-table.td colspan="6" />
                        <x-table.td-money :value="optional($this->totalDebetKredit)->debet" />
                        <x-table.td-money :value="optional($this->totalDebetKredit)->kredit" />
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->dataJurnalPosting" />
        </x-slot>
    </x-card>
</div>
