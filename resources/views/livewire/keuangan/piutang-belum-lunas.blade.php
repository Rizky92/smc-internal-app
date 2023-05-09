<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading loading-target="loadProperties">
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto">Penjamin: </x-filter.label>
                <x-filter.select2 class="ml-3" livewire show-key name="penjamin" :options="$this->dataPenjamin" placeholder="-" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 20ch" name="tgl_piutang" title="Tgl. Piutang" />
                    <x-table.th style="width: 10ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 15ch" name="status" title="Status" />
                    <x-table.th style="width: 20ch" name="totalpiutang" title="Total" />
                    <x-table.th style="width: 20ch" name="uangmuka" title="Uang Muka" />
                    <x-table.th style="width: 20ch" name="sisapiutang" title="Sisa" />
                    <x-table.th style="width: 20ch" name="tgltempo" title="Tgl. Jatuh Tempo" />
                    <x-table.th style="width: 30ch" name="png_jawab" title="Penjamin" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPiutangBelumLunas as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_piutang }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ rp($item->totalpiutang) }}</x-table.td>
                            <x-table.td>{{ rp($item->uangmuka) }}</x-table.td>
                            <x-table.td>{{ rp($item->sisapiutang) }}</x-table.td>
                            <x-table.td>{{ $item->tgltempo }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPiutangBelumLunas" />
        </x-slot>
    </x-card>
</div>
