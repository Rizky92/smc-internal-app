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
                    <x-table.th title="Bidang" />
                    <x-table.th title="Anggaran" />
                    <x-table.th title="Total Pemakaian" />
                    <x-table.th title="Selisih" />
                    <x-table.th title="Persentase" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanRKAT as $anggaran)
                        <x-table.tr>
                            <x-table.td colspan="5">{{ $anggaran->nama }}</x-table.td>
                        </x-table.tr>
                        @foreach ($anggaran->anggaran as $detail)
                            <x-table.tr>
                                <x-table.td>{{ $detail->anggaran->nama }}</x-table.td>
                                <x-table.td>{{ rp($detail->nominal_anggaran) }}</x-table.td>
                                <x-table.td>{{ rp($detail->total_pemakaian) }}</x-table.td>
                                <x-table.td>{{ rp($detail->nominal_anggaran - $detail->total_pemakaian) }}</x-table.td>
                                <x-table.td>
                                    {{ number_format(
                                        $detail->total_pemakaian > 0 && $detail->nominal_anggaran > 0
                                            ? ($detail->total_pemakaian / $detail->nominal_anggaran) * 100
                                            : 0,
                                    2, ',', '.') }}%
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                        <x-table.tr>
                            <x-table.td>Total</x-table.td>
                            @php
                                $totalAnggaran = $anggaran->anggaran->sum('nominal_anggaran');
                                $totalPemakaian = $anggaran->anggaran->sum('total_pemakaian');
                            @endphp
                            <x-table.td>{{ rp($totalAnggaran) }}</x-table.td>
                            <x-table.td>{{ rp($totalPemakaian) }}</x-table.td>
                            <x-table.td>{{ rp($totalAnggaran - $totalPemakaian) }}</x-table.td>
                            <x-table.td>
                                {{ number_format(
                                    $totalAnggaran > 0 && $totalPemakaian > 0
                                        ? ($totalPemakaian / $totalAnggaran) * 100
                                        : 0,
                                2, ',', '.') }}%
                            </x-table.td>
                        </x-table.tr>
                        <x-table.tr>
                            <x-table.td-empty colspan="6" text="" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
