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
                    <x-filter.button-export-excel class="ml-auto mx-2" />
                    <x-button variant="primary" size="sm" title="Print" icon="fas fa-print" onclick="window.print()" />
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
                    <x-table.th title="Rekening" />
                    <x-table.th title="Debet" />
                    <x-table.th title="Kredit" />
                </x-slot>
                <x-slot name="body">
                        @forelse ($this->dataPostingJurnal as $item )
                        <x-table.tr>
                            <x-table.td>{{ $item->no_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->no_bukti }}</x-table.td>
                            <x-table.td>{{ $item->tgl_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->jam_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->jenis  === 'U' ? 'Umum' : 'Penyesuaian' }}</x-table.td>
                            <x-table.td>{{ $item->keterangan }}</x-table.td>
                            <x-table.td>{{ $item->nm_rek }}</x-table.td>
                            <x-table.td>{{ $item->debet }}</x-table.td>
                            <x-table.td>{{ $item->kredit }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                    <x-table.tr>
                        <x-table.th colspan="6" />
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
