<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.label constant-width>Shift Kerja:</x-filter.label>
                <x-filter.select model="shift" :options="['Pagi' => 'Pagi', 'Siang' => 'Siang', 'Malam' => 'Malam']" />
                <x-filter.label class="ml-auto pr-3">Gudang:</x-filter.label>
                <x-filter.select model="bangsal" :options="['IFA' => 'Farmasi A', 'IFG' => 'Farmasi IGD', 'IFI' => 'Farmasi Rawat Inap', 'KO' => 'Kamar Operasi OK']" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="satuan" title="Satuan" />
                    <x-table.th name="stok" title="Stok Gudang" />
                    <x-table.th name="jumlah_shift" :title="'Pemakaian per Shift ' . $this->shift" />
                    <x-table.th name="jumlah_3hari" title="Pemakaian 3 Hari Terakhir" />
                    <x-table.th name="jumlah_6hari" title="Pemakaian 6 Hari Terakhir" />
                    <x-table.th name="sisa_6hari" title="Sisa 6 Hari" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataDefectaDepo as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td>{{ $item->satuan }}</x-table.td>
                            <x-table.td>{{ $item->stok }}</x-table.td>
                            <x-table.td>
                                {{ $item->jumlah_shift }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->jumlah_3hari }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->jumlah_6hari }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->sisa_6hari }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataDefectaDepo" />
        </x-slot>
    </x-card>
</div>
