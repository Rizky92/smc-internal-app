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
                <x-filter.label constant-width>Total Harga:</x-filter.label>
                <x-filter.select model="totalHarga" :options="[
                    'below_100k' => 'Di bawah 100.000',
                    'above_100k' => '100.000 atau lebih',
                ]" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tgl_perawatan" title="Tanggal Berobat" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="nm_dokter" title="Dokter" />
                    <x-table.th name="total_harga" title="Total Harga" />
                    <x-table.th title="PPN" />
                    <x-table.th title="Total Harga + PPN" />
                    <x-table.th title="Detail Obat" />
                    <x-table.th name="jml" title="Jumlah" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->rincianKunjunganRalan as $item)
                        @php
                            $odd = $loop->iteration % 2 === 0 ? '255 255 255' : '247 247 247';
                            $count = $item->pemberian->count();
                            $firstDetail = $item->pemberian->first();
                        @endphp
                        <x-table.tr style="background-color: rgb({{ $odd }})">
                            <x-table.td rowspan="{{ $count }}">{{ $item->tgl_perawatan }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->no_rawat }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->png_jawab }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $item->nm_dokter }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ rp($item->total_harga) }}</x-table.td>
                            <x-table.td
                                rowspan="{{ $count }}">{{ rp(round($item->total_harga * 0.11)) }}</x-table.td>
                            <x-table.td
                                rowspan="{{ $count }}">{{ rp(round($item->total_harga + $item->total_harga * 0.11)) }}</x-table.td>
                            <x-table.td>{{ $firstDetail->obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $firstDetail->jml }}</x-table.td>
                        </x-table.tr>
                        @if ($count > 1)
                            @foreach ($item->pemberian->skip(1) as $detail)
                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td
                                        class="p-1 border-0">&ensp;&ensp;{{ $detail->obat->nama_brng }}</x-table.td>
                                    <x-table.td class="p-1 border-0">{{ $detail->jml }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty colspan="10" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->rincianKunjunganRalan" />
        </x-slot>
    </x-card>
</div>
