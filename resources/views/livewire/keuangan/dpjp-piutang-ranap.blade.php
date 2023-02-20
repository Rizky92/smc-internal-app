<div>
    <x-flash />

    <x-card use-default-filter wire:init="loadProperties">
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                <x-slot name="columns">
                    <x-table.th name="tgl_keluar" title="Tgl. Keluar" />
                    <x-table.th name="jam_keluar" title="Jam" />
                    <x-table.th name="no_nota" title="No. Nota" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="perujuk" title="Asal Rujukan" />
                    <x-table.th title="Registrasi" />
                    <x-table.th title="Tindakan" />
                    <x-table.th title="Obat + Embl. Tsl." />
                    <x-table.th title="Retur Obat" />
                    <x-table.th title="Resep Pulang" />
                    <x-table.th title="Laboratorium" />
                    <x-table.th title="Radiologi" />
                    <x-table.th title="Kamar + Layanan" />
                    <x-table.th title="Operasi" />
                    <x-table.th title="Harian" />
                    <x-table.th title="Tambahan" />
                    <x-table.th title="Potongan" />
                    <x-table.th name="uangmuka" title="Uang Muka" />
                    <x-table.th name="totalpiutang" title="Total" />
                    <x-table.th title="Dibayar" />
                    <x-table.th title="Sisa" />
                    <x-table.th name="kd_kamar" title="Kamar" />
                    <x-table.th name="nm_bangsal" title="Bangsal" />
                    <x-table.th title="Dokter P.J." />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->piutangRanap as $item)
                        @php($kategoriBilling = $item->billing->pluck('total', 'status')->mapWithKeys(fn ($total, $status) => [Str::snake($status) => $total]))
                        <x-table.tr>
                            <x-table.td>{{ $item->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $item->jam_keluar }}</x-table.td>
                            <x-table.td>{{ $item->nota->no_nota }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->perujuk }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('registrasi') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->only(['ranap_dokter', 'ranap_dokter_paramedis', 'ranap_paramedis', 'ralan_dokter', 'ralan_dokter_paramedis', 'ralan_paramedis'])->sum() }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('obat') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('retur_obat') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('resep_pulang') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('laborat') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('radiologi') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('potongan') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('tambahan') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->only(['kamar', 'service'])->sum() }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('operasi') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->get('harian') }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->sum() }}</x-table.td>
                            <x-table.td>{{ $item->uangmuka }}</x-table.td>
                            <x-table.td>{{ $item->dibayar }}</x-table.td>
                            <x-table.td>{{ $kategoriBilling->sum() - $item->uangmuka - $item->dibayar }}</x-table.td>
                            <x-table.td>{{ $item->kd_kamar }}</x-table.td>
                            <x-table.td>{{ $item->nm_bangsal }}</x-table.td>
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
