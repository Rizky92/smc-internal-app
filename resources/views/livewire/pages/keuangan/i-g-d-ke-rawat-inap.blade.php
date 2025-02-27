<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage :constantWidth="true" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 13ch" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" style="width: 9ch" />
                    <x-table.th name="jam_reg" title="Jam Registrasi" style="width: 15ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 17ch" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="dpjp_igd" title="DPJP IGD" />
                    <x-table.th name="dpjp_ranap" title="DPJP Ranap" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->jam_reg }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->dpjp_igd }}</x-table.td>
                            <x-table.td>{{ $item->dpjp_ranap }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="7" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
