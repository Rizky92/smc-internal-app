<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Tahun" />
                    <x-table.th title="Bidang" />
                    <x-table.th title="Nama Anggaran" />
                    <x-table.th title="Total Anggaran" />
                    @foreach (\Carbon\CarbonPeriod::create(now()->startOfYear(), '1 month', now())->toArray() as $month)
                        <x-table.th :title="$month->translatedFormat('F')" />
                    @endforeach
                    <x-table.th title="Selisih" />
                    <x-table.th title="Persentase" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->anggaranTahunan as $item)
                        {{-- @dump($item) --}}
                        <x-table.tr>
                            <x-table.td>{{ $item->tahun }}</x-table.td>
                            <x-table.td>{{ $item->bidang->nama }}</x-table.td>
                            <x-table.td>{{ $item->nama }}</x-table.td>
                            <x-table.td>{{ rp($item->total_anggaran) }}</x-table.td>
                            <x-table.td>{{ rp($item->detail->sum->total_pemakaian) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_anggaran - $item->detail->sum->total_pemakaian) }}</x-table.td>
                            <x-table.td>{{
                                $item->total_anggaran > 0 || $item->detail->sum->total_pemakaian > 0
                                    ? number_format(($item->detail->sum->total_pemakaian / $item->total_anggaran) * 100, 2, ',', '.')
                                    : 0
                            }}%</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="1" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
