<div>
    <x-flash />

    <x-card wire:init="loadProperties">
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.label constant-width>Status :</x-filter.label>
                <div class="input-group input-group-sm" style="width: 9rem">
                    <x-filter.select model="status" :options="['' => 'Semua', 'Belum Lunas' => 'Belum Lunas', 'Lunas' => 'Lunas']" />
                </div>
                <x-filter.label class="ml-auto mr-3" constant-width>Jenis Bayar :</x-filter.label>
                <x-filter.select2 title="Penjamin" model="jenisBayar" :collection="$this->penjamin" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%; width: 250rem">
                <x-slot name="columns">
                    <x-table.th style="width: 21ch" name="waktu_keluar" title="Waktu Keluar" />
                    <x-table.th style="width: 15ch" title="No. Nota" />
                    <x-table.th style="width: 12ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th style="width: 50ch" name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 50ch" name="png_jawab" title="Jenis Bayar" />
                    <x-table.th style="width: 30ch" name="perujuk" title="Asal Rujukan" />
                    <x-table.th style="width: 15ch" title="Registrasi" />
                    <x-table.th style="width: 15ch" title="Tindakan" />
                    <x-table.th style="width: 20ch" title="Obat + Embl. Tsl." />
                    <x-table.th style="width: 15ch" title="Retur Obat" />
                    <x-table.th style="width: 15ch" title="Resep Pulang" />
                    <x-table.th style="width: 15ch" title="Laboratorium" />
                    <x-table.th style="width: 15ch" title="Radiologi" />
                    <x-table.th style="width: 20ch" title="Kamar + Layanan" />
                    <x-table.th style="width: 15ch" title="Operasi" />
                    <x-table.th style="width: 15ch" title="Harian" />
                    <x-table.th style="width: 15ch" title="Tambahan" />
                    <x-table.th style="width: 15ch" title="Potongan" />
                    <x-table.th style="width: 15ch" name="uangmuka" title="Uang Muka" />
                    <x-table.th style="width: 15ch" name="totalpiutang" title="Total" />
                    <x-table.th style="width: 15ch" title="Dibayar" />
                    <x-table.th style="width: 15ch" title="Sisa" />
                    <x-table.th style="width: 40ch" name="ruangan" title="Kamar" />
                    <x-table.th style="width: 40ch" title="Dokter P.J." />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->piutangRanap as $item)
                        @php
                            $kategoriBilling = $item
                                ->billing
                                ->pluck('total', 'status')
                                ->mapWithKeys(fn ($total, $status) => [Str::snake($status) => $total]);

                            $billingTindakan = $kategoriBilling
                                ->only([
                                    'ranap_dokter',
                                    'ranap_dokter_paramedis',
                                    'ranap_paramedis',
                                    'ralan_dokter',
                                    'ralan_dokter_paramedis',
                                    'ralan_paramedis',
                                ])
                                ->sum();

                            $total = $kategoriBilling->sum();

                            $sisa = $total - $item->uangmuka - $item->dibayar;
                        @endphp
                        <x-table.tr>
                            <x-table.td>{{ $item->waktu_keluar }}</x-table.td>
                            <x-table.td>{{ $item->no_nota }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->perujuk }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('registrasi')) }}</x-table.td>
                            <x-table.td>{{ rp($billingTindakan) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('obat')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('retur_obat')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('resep_pulang')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('laborat')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('radiologi')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('potongan')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('tambahan')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->only(['kamar', 'service'])->sum()) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('operasi')) }}</x-table.td>
                            <x-table.td>{{ rp($kategoriBilling->get('harian')) }}</x-table.td>
                            <x-table.td>{{ rp($total) }}</x-table.td>
                            <x-table.td>{{ rp($item->uangmuka) }}</x-table.td>
                            <x-table.td>{{ rp($item->dibayar) }}</x-table.td>
                            <x-table.td>{{ rp($sisa) }}</x-table.td>
                            <x-table.td>{{ $item->ruangan }}</x-table.td>
                            <x-table.td>{{ $item->dpjpRanap->implode('nm_dokter', ', ') }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="26" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->piutangRanap" />
        </x-slot>
    </x-card>
</div>
