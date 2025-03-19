<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 pb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="pt-3 border-top">
                <x-filter.label constant-width>Instansi:</x-filter.label>
                <x-filter.select2 livewire name="penjamin" :options="$this->dataPenjamin" placeholder="SEMUA" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table style="min-width: 100%" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Penjamin" />
                    <x-table.th title="No. Rawat" />
                    <x-table.th title="No. RM" />
                    <x-table.th title="Nama" />
                    <x-table.th title="JK" />
                    <x-table.th title="Agama" />
                    <x-table.th title="Tgl. Daftar" />
                    <x-table.th title="Poli" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPasienPoliMCU as $item)
                        <x-table.tr>
                            <x-table.td>
                                {{ $item->penjamin->png_jawab }}
                            </x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->nm_pasien }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->jk }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->agama }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->tgl_registrasi }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->poliklinik->nm_poli }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="9" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPasienPoliMCU" />
        </x-slot>
    </x-card>
</div>
