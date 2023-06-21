<div>
    <x-flash />

    <livewire:keuangan.r-k-a-t.modal.input-pelaporan-r-k-a-t />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label constant-width>Tahun:</x-filter.label>
                <x-filter.select model="tahun" :options="$this->dataTahun" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="pt-3 border-top">
                <x-filter.label constant-width>Bidang:</x-filter.label>
                <x-filter.select model="bidang" :options="$this->dataBidang" placeholder="SEMUA" />
                <x-button variant="primary" size="sm" title="Laporan Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-pelaporan-rkat" class="btn-primary ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="#" />
                    <x-table.th title="Bidang" />
                    <x-table.th title="Anggaran" />
                    <x-table.th title="Tahun" />
                    <x-table.th title="Tgl. Pakai" />
                    <x-table.th title="Nominal" />
                    <x-table.th title="Deskripsi" />
                    <x-table.th title="Tgl. Diinput" />
                    <x-table.th title="Oleh" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPenggunaanRKAT as $penggunaan)
                        <x-table.tr>
                            <x-table.td>{{ $penggunaan->id }}</x-table.td>
                            <x-table.td>{{ $penggunaan->anggaranBidang->bidang->nama }}</x-table.td>
                            <x-table.td>{{ $penggunaan->anggaranBidang->anggaran->nama }}</x-table.td>
                            <x-table.td>{{ $penggunaan->anggaranBidang->tahun }}</x-table.td>
                            <x-table.td>{{ $penggunaan->tgl_dipakai }}</x-table.td>
                            <x-table.td>{{ rp($penggunaan->nominal_pemakaian) }}</x-table.td>
                            <x-table.td>{{ $penggunaan->deskripsi ?? '-' }}</x-table.td>
                            <x-table.td>{{ $penggunaan->created_at->format('Y-m-d') }}</x-table.td>
                            <x-table.td></x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPenggunaanRKAT" />
        </x-slot>
    </x-card>
</div>
