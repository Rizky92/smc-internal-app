<div>
    <x-flash />

    @can('keuangan.rkat-pelaporan.create')
        <livewire:pages.keuangan.modal.r-k-a-t-input-pelaporan />

        @once
            @push('js')
                <script>
                    function loadData(e) {
                        let {
                            pemakaianAnggaranId,
                            anggaranBidangId,
                            tglPakai,
                            keterangan
                        } = e.dataset

                        @this.emit('prepare', {
                            pemakaianAnggaranId,
                            anggaranBidangId,
                            tglPakai,
                            keterangan
                        })

                        $('#modal-input-pelaporan-rkat').modal('show')
                    }
                </script>
            @endpush
        @endonce
    @endcan

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
                @can('keuangan.rkat-pelaporan.create')
                    <x-button variant="primary" size="sm" title="Laporan Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-pelaporan-rkat" class="btn-primary ml-auto" />
                @endcan
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Bidang" />
                    <x-table.th title="Anggaran" />
                    <x-table.th title="Tahun" />
                    <x-table.th title="Tgl. Pakai" />
                    <x-table.th title="Judul" />
                    <x-table.th title="Nominal" />
                    <x-table.th title="Tgl. Diinput" />
                    <x-table.th title="Oleh" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPenggunaanRKAT as $penggunaan)
                        <x-table.tr>
                            <x-table.td
                                :clickable="user()->can('keuangan.rkat-pelaporan.update') || user()->can('keuangan.rkat-pelaporan.read')"
                                data-pemakaian-anggaran-id="{{ $penggunaan->id }}"
                                data-anggaran-bidang-id="{{ $penggunaan->anggaran_bidang_id }}"
                                data-tgl-pakai="{{ $penggunaan->tgl_dipakai }}"
                                data-keterangan="{{ $penggunaan->judul }}">
                                {{ $penggunaan->anggaranBidang->bidang->nama }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->anggaranBidang->anggaran->nama }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->anggaranBidang->tahun }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->tgl_dipakai }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->judul ?? '-' }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($penggunaan->nominal_pemakaian) }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->created_at->toDateString() }}
                            </x-table.td>
                            <x-table.td>
                                {{ $penggunaan->user_id }}
                                {{ optional($penggunaan->petugas)->nama }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="9" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPenggunaanRKAT" />
        </x-slot>
    </x-card>
</div>
