<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 200rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 15ch" name="no_tagihan" title="No. Tagihan" />
                    <x-table.th style="width: 15ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 12ch" name="tgl_tagihan" title="Tgl. Tagihan" />
                    <x-table.th style="width: 12ch" name="tgl_jatuh_tempo" title="Tgl. Jatuh Tempo" />
                    <x-table.th style="width: 20ch" name="tgl_bayar_terakhir" title="Tgl. Bayar Terakhir" />
                    <x-table.th style="width: 8ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 30ch" name="penjab_pasien" title="Asuransi Pasien" />
                    <x-table.th style="width: 30ch" name="penjab_piutang" title="Jaminan Piutang" />
                    <x-table.th style="width: 50ch" name="catatan" title="Catatan" />
                    <x-table.th style="width: 20ch" name="total_piutang" title="Total Piutang" />
                    <x-table.th style="width: 20ch" name="uang_muka" title="Uang Muka" />
                    <x-table.th style="width: 20ch" name="cicilan_sekarang" title="Cicilan saat ini" />
                    <x-table.th style="width: 20ch" name="sisa_piutang" title="Sisa Piutang" />
                    <x-table.th style="width: 20ch" title="0 - 30" />
                    <x-table.th style="width: 20ch" title="31 - 60" />
                    <x-table.th style="width: 20ch" title="61 - 90" />
                    <x-table.th style="width: 20ch" title="> 90" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataRekapPiutangAging as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->tgl_jatuh_tempo }}</x-table.td>
                            <x-table.td>{{ $item->tgl_bayar_terakhir }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->penjab_pasien }}</x-table.td>
                            <x-table.td>{{ $item->penjab_piutang }}</x-table.td>
                            <x-table.td>{{ $item->catatan }}</x-table.td>
                            <x-table.td>{{ rp($item->total_piutang) }}</x-table.td>
                            <x-table.td>{{ rp($item->uang_muka) }}</x-table.td>
                            <x-table.td>{{ rp($item->cicilan_sekarang) }}</x-table.td>
                            <x-table.td>{{ rp($item->sisa_piutang) }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari <= 30 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 30 && $item->umur_hari <= 60 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 60 && $item->umur_hari <= 90 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 90 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataRekapPiutangAging" />
        </x-slot>
    </x-card>
</div>
