<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    {{-- <x-table.th name="id" title="#" /> --}}
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="p_jawab" title="Penanggung Jawab" />
                    <x-table.th name="nm_dokter" title="Nama Dokter" />
                    <x-table.th-money align="right" name="total_billing" title="Total Billing" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataSummaryBillingMCU as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->p_jawab }}</x-table.td>
                            <x-table.td>{{ $item->nm_dokter }}</x-table.td>
                            <x-table.td-money :value="$item->total_billing" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="9" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataSummaryBillingMCU" />
        </x-slot>
    </x-card>
</div>
