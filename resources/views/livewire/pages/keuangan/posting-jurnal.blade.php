<div wire:init="loadProperties">
    <x-flash />

    @can('keuangan.posting-jurnal.create')
        <livewire:pages.keuangan.modal.input-posting-jurnal />
        
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

    @push('css')
        <style>
            .print {
                display: none;
            }

            .conclusion {
                display: none;
            }

            .signature {
                display: none;
            }

            .time {
                display: none;
            }

            @media print {
                @page {
                    size: landscape;
                }

                body {
                    margin: 0;
                }

                .print {
                    display: flex;
                    text-align: center;
                    margin-top: none;
                    padding: 0;
                }

                .conclusion {
                    display: grid;
                    page-break-after: auto;
                }

                .time {
                    display: flex;
                    justify-content: end;
                }

                .signature {
                    display: flex;
                    justify-content: space-between;
                }

                h3 {
                    font-size: 20px;
                    margin-top: 0px;
                    padding: 0;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    @endpush

    <x-card>
        <x-slot name="header">
            <div class="print">
                <h3 style="font-family: tahoma; font-size: 11px;">POSTING JURNAL</h3>
            </div>
            <div class="no-print">
                <x-row-col-flex>
                    <x-filter.range-date />
                    <x-button class="ml-auto" outline="true" variant="primary" size="sm" title="Print" icon="fas fa-print" onclick="window.print()" />
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
            </div>
        </x-slot>
        <x-slot name="body">
            <x-table id="printTable" style="min-width: 100%" zebra hover sticky nowrap borderless>
                <x-slot name="columns">
                    <x-table.th style="width: 10%" title="No. Jurnal" />
                    <x-table.th style="width: 10%" title="No. Bukti" />
                    <x-table.th style="width: 6%" title="Tgl. Jurnal" />
                    <x-table.th style="width: 4%" title="Jenis" />
                    <x-table.th style="width: 22%" title="Keterangan" />
                    <x-table.th style="width: 4%" title="Kode" />
                    <x-table.th style="width: 22%" title="Rekening" />
                    <x-table.th style="width: 11%" title="Debet" style="text-align: right;" />
                    <x-table.th style="width: 11%" title="Kredit" style="text-align: right;" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPostingJurnal as $item )
                        @php
                            $odd = $loop->iteration % 2 === 0 ? '255 255 255' : '247 247 247';
                            $count = $item->detail->count();
                            $firstDetail = $item->detail->first();
                        @endphp

                        <x-table.tr style="background-color: rgb({{ $odd }})">
                            <x-table.td rowspan="{{ $count }}">{{ $item->no_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->no_bukti }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->tgl_jurnal }} {{ $item->jam_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->jenis === 'U' ? 'Umum' : 'Penyesuaian' }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->keterangan }}</x-table.td>
                            <x-table.td>{{ optional($firstDetail)->kd_rek }}</x-table.td>
                            <x-table.td>
                                @if (optional($firstDetail)->kredit > 0)
                                @endif
                                {{ optional(optional($firstDetail)->rekening)->nm_rek }}
                            </x-table.td>
                            <x-table.td style="text-align: right;">{{ optional($firstDetail)->debet > 0 ? rp(optional($firstDetail)->debet) : null }}</x-table.td>
                            <x-table.td style="text-align: right;">{{ optional($firstDetail)->kredit > 0 ? rp(optional($firstDetail)->kredit) : null }}</x-table.td>
                        </x-table.tr>
                        @if ($item->detail->skip(1)->count() > 0)
                            @foreach ($item->detail->skip(1) as $detail)
                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td class="p-1 border-0">{{ $detail->kd_rek }}</x-table.td>
                                    <x-table.td class="border-0">
                                        @if ($detail->kredit > 0)
                                        @endif {{ optional($detail->rekening)->nm_rek }}
                                    </x-table.td>
                                    <x-table.td style="text-align: right;" class="border-0">{{ $detail->debet > 0 ? rp($detail->debet) : null }}</x-table.td>
                                    <x-table.td style="text-align: right;" class="border-0">{{ $detail->kredit > 0 ? rp($detail->kredit) : null }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                    <x-table.tr>
                        <x-table.td>TOTAL: </x-table.td>
                        <x-table.td colspan="6" />
                        <x-table.td style="text-align: right;">{{ rp(optional($this->totalDebetKredit)->debet) }}</x-table.td>
                        <x-table.td style="text-align: right;">{{ rp(optional($this->totalDebetKredit)->kredit) }}</x-table.td>
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <div class="conclusion">
                <div class="time">
                    <p style="font-size: 0.70em"><b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
                </div>
                <div class="signature">
                    <div style="text-align: center; font-size: 0.70em;">
                        <p><b>Menyetujui</b></p>
                        <br>
                        <br>
                        <br>
                        <p><b>dr. Daisy Wijaya</b></p>
                        <p><b>Manager Keuangan</b></p>
                    </div>
                    <div style="text-align: center; font-size: 0.70em;">
                        <p><b>Mengetahui</b></p>
                        <br>
                        <br>
                        <br>
                        <p><b>dr. Teguh Nurwanto, MARS</b></p>
                        <p><b>Direktur</b></p>
                    </div>
                </div>
            </div>
            <x-paginator :data="$this->dataPostingJurnal" />
        </x-slot>
    </x-card>
</div>
