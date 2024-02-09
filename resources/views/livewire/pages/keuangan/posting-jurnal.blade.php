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
        body * {
            visibility: visible;
            margin: 0;
            padding: 0;
        }
    
        #printHeader {
            display: none;
        }
    
        @media print {
            #normalHeader {
                display: none;
            }
    
            #printHeader {
                display: block !important;
            }
    
            #printTable,
            #printTable * {
                visibility: visible;
            }

            @page {
                    size: landscape;
                }
        }
    </style>
    @endpush
    
    <x-card use loading>
        <x-slot name="header">
            <div id="normalHeader">
                <x-row-col-flex>
                    <x-filter.range-date />
                    <x-filter.button-export-excel class="ml-auto mx-2" />
                    <livewire:pages.keuangan.cetak-p-d-f-posting-jurnal :data="$this->dataPostingJurnal" />   
                        @once
                        @push('js')
                        <script>
                            document.addEventListener('livewire:load', function () {
                                Livewire.on('openPrintWindow', () => {
                                    window.print();
                                });
                            });
                        </script>
                        @endpush
                    @endonce
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
            <div id="printHeader">
                <div style="text-align: center;">
                    <img src="{{ asset('img/logo.png') }}" margin="0" style=" display: inline-block; vertical-align: middle;" width="80px">
                    <div style="display: inline-block; vertical-align: middle;">
                        <h2 style="font-family: 'Arial', serif; font-size:20px ; margin: 0;">RS SAMARINDA MEDIKA CITRA</h2>
                        <p style="font-size: 14px; margin: 1px;">Jl. Kadrie Oening no.85, RT.35, Kel. Air Putih, Kec. Samarinda Ulu, Samarinda, Kalimantan Timur
                            <br>TEL:0541-7273000
                            <br>E-mail:info@rssmc.co.id
                        </p>
                    </div>
                </div>
                <hr style="border-top: 2px solid #333; margin-top: 10px; margin-bottom: 1px;">
                <hr style="border-top: 2px solid #333; margin-top: 1px; margin-bottom: 10px; padding-top:2px">
            </div>       
        </x-slot>
        <x-slot name="body">
            <x-table id="printTable" :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="No. Jurnal" />
                    <x-table.th title="No. Bukti" />
                    <x-table.th name="tgl_jurnal" title="Tgl. Jurnal" />
                    <x-table.th name="jam_jurnal" title="Jam Jurnal" />
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
                            <x-table.td>{{ $item->keterangan }}</x-table.td>
                            <x-table.td>{{ $item->nm_rek }}</x-table.td>
                            <x-table.td>{{ $item->debet }}</x-table.td>
                            <x-table.td>{{ $item->kredit }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" padding />
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
            <x-paginator :data="$this->dataPostingJurnal" />
        </x-slot>
    </x-card>
</div>
