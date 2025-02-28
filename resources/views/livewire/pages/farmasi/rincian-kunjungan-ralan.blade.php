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
                <x-filter.select
                    model="totalHarga"
                    :options="[
                        'below_100k' => 'Di bawah 100.000',
                        'above_100k' => '100.000 atau lebih',
                    ]" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tgl_perawatan" title="Tanggal Berobat" />
                    <x-table.th name="no_resep" title="No. Resep" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="nm_dokter" title="Dokter" />
                    <x-table.th title="Kode Obat" />
                    <x-table.th title="Obat" />
                    <x-table.th title="Biaya Obat" />
                    <x-table.th name="jml" title="Jumlah" />
                    <x-table.th name="total" title="Total" />
                </x-slot>
                <x-slot name="body">
                    @php
                        $totalSementara = 0;
                        $noResepSebelumnya = null;
                    @endphp

                    @forelse ($this->rincianKunjunganRalan as $item)
                        @if ($noResepSebelumnya != $item->no_resep && $noResepSebelumnya != null)
                            {{-- Tampilkan total untuk no_resep sebelumnya --}}
                            <x-table.tr>
                                <x-table.td colspan="10">
                                    <strong>Total</strong>
                                </x-table.td>
                                <x-table.td>
                                    <strong>{{ rp($totalSementara) }}</strong>
                                </x-table.td>
                            </x-table.tr>
                            @php
                                $totalSementara = 0;
                            @endphp
                        @endif

                        @php
                            $totalSementara += $item->total;
                            $noResepSebelumnya = $item->no_resep;
                        @endphp

                        <x-table.tr>
                            <x-table.td>
                                {{ $item->tgl_perawatan }}
                            </x-table.td>
                            <x-table.td>{{ $item->no_resep }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->nm_dokter }}</x-table.td>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td>
                                {{ rp($item->biaya_obat) }}
                            </x-table.td>
                            <x-table.td>{{ $item->jml }}</x-table.td>
                            <x-table.td>{{ rp($item->total) }}</x-table.td>
                        </x-table.tr>

                        @if ($loop->last)
                            <x-table.tr>
                                <x-table.td colspan="10">
                                    <strong>Total</strong>
                                </x-table.td>
                                <x-table.td>
                                    <strong>{{ rp($totalSementara) }}</strong>
                                </x-table.td>
                            </x-table.tr>
                        @endif
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->rincianKunjunganRalan" />
        </x-slot>
    </x-card>
</div>
