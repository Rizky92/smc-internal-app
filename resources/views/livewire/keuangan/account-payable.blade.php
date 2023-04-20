<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading loading-target="loadProperties">
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 150rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" name="no_order" title="No. Order" />
                    <x-table.th style="width: 20ch" name="no_faktur" title="No. Faktur" />
                    <x-table.th style="width: 50ch" name="nama_suplier" title="Nama Suplier" />
                    <x-table.th style="width: 20ch" name="tgl_pesan" title="Tgl. Pesan" />
                    <x-table.th style="width: 20ch" name="tgl_tempo" title="Tgl. Tempo" />
                    <x-table.th style="width: 20ch" name="tgl_bayar" title="Tgl. Bayar" />
                    <x-table.th style="width: 15ch" name="status" title="Status" />
                    <x-table.th style="width: 30ch" name="tagihan" title="Tagihan" />
                    <x-table.th style="width: 30ch" name="dibayar" title="Dibayar" />
                    <x-table.th style="width: 30ch" name="sisa" title="Sisa" />
                    <x-table.th style="width: 25ch" name="nama_bayar" title="Akun Bayar" />
                    <x-table.th style="width: 30ch" name="periode_0_30" title="0 - 30" />
                    <x-table.th style="width: 30ch" name="periode_31_60" title="31 - 60" />
                    <x-table.th style="width: 30ch" name="periode_61_90" title="61 - 90" />
                    <x-table.th style="width: 30ch" name="periode_90_up" title="> 90" />
                    <x-table.th name="keterangan" title="Keterangan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataAccountPayableMedis as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_order }}</x-table.td>
                            <x-table.td>{{ $item->no_faktur }}</x-table.td>
                            <x-table.td>{{ $item->nama_suplier }}</x-table.td>
                            <x-table.td>{{ $item->tgl_pesan }}</x-table.td>
                            <x-table.td>{{ $item->tgl_tempo }}</x-table.td>
                            <x-table.td>{{ $item->tgl_bayar }}</x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ rp($item->tagihan) }}</x-table.td>
                            <x-table.td>{{ rp($item->dibayar) }}</x-table.td>
                            <x-table.td>{{ rp($item->sisa) }}</x-table.td>
                            <x-table.td>{{ $item->nama_bayar }}</x-table.td>
                            <x-table.td>{{ is_between($item->umur_hari, 0, 30) ? rp($item->sisa) : rp() }}</x-table.td>
                            <x-table.td>{{ is_between($item->umur_hari, 31, 60) ? rp($item->sisa) : rp() }}</x-table.td>
                            <x-table.td>{{ is_between($item->umur_hari, 61, 90) ? rp($item->sisa) : rp() }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 90 ? rp($item->sisa) : rp() }}</x-table.td>
                            <x-table.td>{{ $item->keterangan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="15" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataAccountPayableMedis" />
        </x-slot>
    </x-card>
</div>
