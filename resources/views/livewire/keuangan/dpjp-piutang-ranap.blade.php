<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%">
                <x-slot name="columns">
                    <x-table.th name="tgl_keluar" title="Tgl. Keluar" />
                    <x-table.th name="jam_keluar" title="Jam" />
                    <x-table.th name="no_nota" title="No. Rawat" />
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
                        <x-table.tr>
                            <x-table.td>{{ $item->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $item->jam_keluar }}</x-table.td>
                            <x-table.td>{{ $item->nota->no_nota }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->perujuk }}</x-table.td>
                            <x-table.td>{{ $item->perujuk }}</x-table.td>
                        </x-table.tr>
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->piutangRanap" />
        </x-slot>
    </x-card>
</div>
