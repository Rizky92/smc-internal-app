<div wire:init="loadProperties">
    <x-flash />

    @can('keuangan.tarif-operasi.create')
        <livewire:pages.keuangan.modal.import-tarif-operasi />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('keuangan.tarif-operasi.create')
                    <x-button variant="primary" size="sm" title="Import" icon="fas fa-plus" data-toggle="modal" data-target="#modal-import-tarif-operasi" class="btn-primary ml-auto" />
                @endcan

                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kode_paket" title="Kode Paket" />
                    <x-table.th name="nm_perawatan" title="Nama Operasi" />
                    <x-table.th name="kategori" title="Kategori" />
                    <x-table.th name="operator1" title="Operator 1" />
                    <x-table.th name="operator2" title="Operator 2" />
                    <x-table.th name="operator3" title="Operator 3" />
                    <x-table.th name="asisten_operator1" title="Asisten Op 1" />
                    <x-table.th name="asisten_operator2" title="Asisten Op 2" />
                    <x-table.th name="asisten_operator3" title="Asisten Op 3" />
                    <x-table.th name="instrumen" title="Instrumen" />
                    <x-table.th name="dokter_anestesi" title="dr Anestesi" />
                    <x-table.th name="asisten_anestesi" title="Asisten Anes 1" />
                    <x-table.th name="asisten_anestesi2" title="Asisten Anes 2" />
                    <x-table.th name="dokter_anak" title="dr Anak" />
                    <x-table.th name="perawaat_resusitas" title="Perawat Resus" />
                    <x-table.th name="bidan" title="Bidan 1" />
                    <x-table.th name="bidan2" title="Bidan 2" />
                    <x-table.th name="bidan3" title="Bidan 3" />
                    <x-table.th name="perawat_luar" title="Perawat Luar" />
                    <x-table.th name="alat" title="Alat" />
                    <x-table.th name="sewa_ok" title="Sewa OK/VK" />
                    <x-table.th name="akomodasi" title="Akomodasi" />
                    <x-table.th name="bagian_rs" title="N.M.S." />
                    <x-table.th name="omloop" title="Onloop 1" />
                    <x-table.th name="omloop2" title="Onloop 2" />
                    <x-table.th name="omloop3" title="Onloop 3" />
                    <x-table.th name="omloop4" title="Onloop 4" />
                    <x-table.th name="omloop5" title="Onloop 5" />
                    <x-table.th name="sarpras" title="Sarpras" />
                    <x-table.th name="dokter_pjanak" title="dr Pj Anak" />
                    <x-table.th name="dokter_umum" title="dr Umum" />
                    <x-table.th name="jumlah" title="Total" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="kelas" title="Kelas" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kode_paket }}</x-table.td>
                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                            <x-table.td>{{ $item->kategori }}</x-table.td>
                            <x-table.td>{{ rp($item->operator1) }}</x-table.td>
                            <x-table.td>{{ rp($item->operator2) }}</x-table.td>
                            <x-table.td>{{ rp($item->operator3) }}</x-table.td>
                            <x-table.td>{{ rp($item->asisten_operator1) }}</x-table.td>
                            <x-table.td>{{ rp($item->asisten_operator2) }}</x-table.td>
                            <x-table.td>{{ rp($item->asisten_operator3) }}</x-table.td>
                            <x-table.td>{{ rp($item->instrumen) }}</x-table.td>
                            <x-table.td>{{ rp($item->dokter_anestesi) }}</x-table.td>
                            <x-table.td>{{ rp($item->asisten_anestesi) }}</x-table.td>
                            <x-table.td>{{ rp($item->asisten_anestesi2) }}</x-table.td>
                            <x-table.td>{{ rp($item->dokter_anak) }}</x-table.td>
                            <x-table.td>{{ rp($item->perawaat_resusitas) }}</x-table.td>
                            <x-table.td>{{ rp($item->bidan) }}</x-table.td>
                            <x-table.td>{{ rp($item->bidan2) }}</x-table.td>
                            <x-table.td>{{ rp($item->bidan3) }}</x-table.td>
                            <x-table.td>{{ rp($item->perawat_luar) }}</x-table.td>
                            <x-table.td>{{ rp($item->alat) }}</x-table.td>
                            <x-table.td>{{ rp($item->sewa_ok) }}</x-table.td>
                            <x-table.td>{{ rp($item->akomodasi) }}</x-table.td>
                            <x-table.td>{{ rp($item->bagian_rs) }}</x-table.td>
                            <x-table.td>{{ rp($item->omloop) }}</x-table.td>
                            <x-table.td>{{ rp($item->omloop2) }}</x-table.td>
                            <x-table.td>{{ rp($item->omloop3) }}</x-table.td>
                            <x-table.td>{{ rp($item->omloop4) }}</x-table.td>
                            <x-table.td>{{ rp($item->omloop5) }}</x-table.td>
                            <x-table.td>{{ rp($item->sarpras) }}</x-table.td>
                            <x-table.td>{{ rp($item->dokter_pjanak) }}</x-table.td>
                            <x-table.td>{{ rp($item->dokter_umum) }}</x-table.td>
                            <x-table.td>{{ rp($item->jumlah) }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->kelas }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="34" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
