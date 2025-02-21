<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label class="pr-3">Tahun:</x-filter.label>
                <x-filter.select model="tahun" :options="$this->dataTahun" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Kategori" />
                    <x-table.th title="Anggaran (A)" />
                    <x-table.th title="Total Pemakaian (B)" />
                    <x-table.th title="Selisih (A - B)" />
                    <x-table.th title="Persentase (B / A)" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanRKAT as $bidang)
                        @php
                            $totalAnggaran = 0;
                            $totalPemakaian = 0;
                        @endphp

                        <x-table.tr>
                            <x-table.td colspan="5" class="font-weight-bold">
                                {{ str($bidang->nama)->upper()->value() }}
                            </x-table.td>
                        </x-table.tr>
                        @foreach ($bidang->descendants as $unit)
                            <x-table.tr>
                                <x-table.td colspan="5" class="font-weight-bold">&emsp;{{ str($unit->nama)->upper()->value() }}</x-table.td>
                            </x-table.tr>
                            @foreach ($unit->anggaranBidang as $anggaran)
                                @php
                                    $totalAnggaran += $anggaran->nominal_anggaran;
                                    $totalPemakaian += $anggaran->total_pemakaian;
                                @endphp

                                <x-table.tr>
                                    <x-table.td>&emsp;&emsp;{{ $anggaran->anggaran->nama }}</x-table.td>
                                    <x-table.td>
                                        {{ rp($anggaran->nominal_anggaran) }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ rp($anggaran->total_pemakaian) }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ rp($anggaran->nominal_anggaran - $anggaran->total_pemakaian) }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ number_format($anggaran->total_pemakaian > 0 && $anggaran->nominal_anggaran > 0 ? ($anggaran->total_pemakaian / $anggaran->nominal_anggaran) * 100 : 0, 2, ',', '.') }}%
                                    </x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endforeach

                        <x-table.tr>
                            <x-table.td>TOTAL</x-table.td>
                            <x-table.td>
                                {{ rp($totalAnggaran) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($totalPemakaian) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($totalAnggaran - $totalPemakaian) }}
                            </x-table.td>
                            <x-table.td>{{ number_format($totalAnggaran > 0 && $totalPemakaian > 0 ? ($totalPemakaian / $totalAnggaran) * 100 : 0, 2, ',', '.') }}%</x-table.td>
                        </x-table.tr>
                        <x-table.tr>
                            <x-table.td-empty colspan="6" text="" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
