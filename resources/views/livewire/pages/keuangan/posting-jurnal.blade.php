<div wire:init="loadProperties">
    <x-flash />

    @can('keuangan.posting-jurnal.create')
        <livewire:pages.keuangan.modal.input-posting-jurnal />
        
        @once
            @push('js')
                <script>
                    function loadData(e) {
                        let {
                            keterangan
                        } = e.dataset

                        @this.emit('prepare', {
                            keternangan: keterangan
                        })

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

        .signature {
            display: none;
        }
        
        .time {
            display: none;
        }

        @media print {
            @page {
                size: landscape;
                margin: none;
            }

            .print {
                display: flex; 
                text-align: center;
                margin-top: none;
                padding: 0;
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
            <div class="print" >
                <h3>POSTING JURNAL</h3>
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
                    <x-filter.select model="jenis" :options="['U' => 'Umum', 'P' => 'Penyesuaian']"/>
                    @can('keuangan.posting-jurnal.create')
                        <x-button variant="primary" size="sm" title="Jurnal Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-posting-jurnal" class="btn-primary ml-auto" />
                    @endcan
                </x-row-col-flex>
            </div>
        </x-slot>
        <x-slot name="body">
            <x-table id="printTable" :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="No. Jurnal" />
                    <x-table.th title="No. Bukti" />
                    <x-table.th name="tgl_jurnal" title="Tgl. Jurnal" />
                    <x-table.th name="jam_jurnal" title="Jam Jurnal" />
                    <x-table.th title="Jenis" />
                    <x-table.th title="Keterangan" />
                    <x-table.th title="Kode Akun" />
                    <x-table.th title="Nama Akun" />
                    <x-table.th title="Debet" />
                    <x-table.th title="Kredit" />
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
                            <x-table.td rowspan="{{ $count }}">{{ $item->tgl_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->jam_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->jenis  === 'U' ? 'Umum' : 'Penyesuaian' }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->keterangan }}</x-table.td>
                            <x-table.td>{{ optional($firstDetail)->kd_rek}}</x-table.td>
                            <x-table.td>
                                @if (optional($firstDetail)->kredit > 0)
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                @endif
                                {{ optional(optional($firstDetail)->rekening)->nm_rek }}
                            </x-table.td>
                            <x-table.td>{{ optional($firstDetail)->debet > 0 ? rp(optional($firstDetail)->debet) : null }}</x-table.td>
                            <x-table.td>{{ optional($firstDetail)->kredit > 0 ? rp(optional($firstDetail)->kredit) : null }}</x-table.td>
                        </x-table.tr>
                        @if ($item->detail->skip(1)->count() > 0)
                            @foreach ($item->detail->skip(1) as $detail)
                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td class="p-1 border-0">{{ $detail->kd_rek }}</x-table.td>
                                    <x-table.td class="border-0">
                                        @if ($detail->kredit > 0)
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif {{ optional($detail->rekening)->nm_rek }}
                                    </x-table.td>
                                    <x-table.td class="border-0">{{ $detail->debet > 0 ? rp($detail->debet) : null }}</x-table.td>
                                    <x-table.td class="border-0">{{ $detail->kredit > 0 ? rp($detail->kredit) : null }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                    <x-table.tr>
                        <x-table.th colspan="7" />
                        <x-table.th title="TOTAL :" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->debet)" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->kredit)" />
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <div class="time">
                <p><b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
            </div>
            <div class="signature">
                <div style="text-align: center">
                    <p><b>Menyetujui</b></p>
                    <br>
                    <br>
                    <br>
                    <p><b>dr. Daisy Wijaya</b></p>
                    <p><b>Manager Keuangan</b></p>
                </div>
                <div style="text-align: center"> 
                    <p><b>Mengetahui</b></p>
                    <br>
                    <br>
                    <br>
                    <p><b>dr. Teguh Nurwanto, MARS</b></p>
                    <p><b>Direktur</b></p>
                </div>
            </div>
            <x-paginator :data="$this->dataPostingJurnal" />
        </x-slot>
    </x-card>
</div>
