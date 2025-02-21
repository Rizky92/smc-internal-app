<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.label class="ml-auto pr-3">Gudang:</x-filter.label>
                <x-filter.select model="bangsal" :options="['IFA' => 'Farmasi A', 'AP' => 'Farmasi B', 'IFG' => 'Farmasi IGD', 'IFI' => 'Farmasi Rawat Inap']" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-navtabs livewire :selected="Str::replace('.', '', '02.05.0011')">
                <x-slot name="tabs">
                    @foreach ($this->dataObat as $key => $title)
                        <x-navtabs.tab :id="$key" :title="$title" />
                    @endforeach
                </x-slot>
                <x-slot name="contents">
                    @foreach ($this->dataObat as $key => $_)
                        @php($property = 'dataLaporanPemakaianObatMorphine' . $key)
                        <x-navtabs.content :id="$key">
                            <x-table zebra hover sticky nowrap>
                                <x-slot name="columns">
                                    <x-table.th name="no_rawat" title="No. Rawat" />
                                    <x-table.th name="no_rkm_medis" title="No. RM" />
                                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                                    <x-table.th name="alamat" title="Alamat Pasien" />
                                    <x-table.th name="tgl_perawatan" title="Tgl. Diberikan" />
                                    <x-table.th name="jml" title="Jumlah" />
                                    <x-table.th name="nm_dokter" title="Nama Dokter" />
                                    <x-table.th name="alamat_dokter" title="Alamat Dokter" />
                                </x-slot>
                                <x-slot name="body">
                                    @forelse ($this->{$property} as $item)
                                        <x-table.tr>
                                            <x-table.td>
                                                {{ $item->no_rawat }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->no_rkm_medis }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->nm_pasien }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->alamat }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->tgl_perawatan }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ number_format($item->jml, 0, ',', '.') }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->nm_dokter }}
                                            </x-table.td>
                                            <x-table.td>
                                                {{ $item->alamat_dokter }}
                                            </x-table.td>
                                        </x-table.tr>
                                    @empty
                                        <x-table.tr-empty colspan="8" padding />
                                    @endforelse
                                </x-slot>
                            </x-table>
                            <x-paginator class="px-4 py-3 bg-light" :data="$this->{$property}" />
                        </x-navtabs.content>
                    @endforeach
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
