<div wire:init="loadProperties" >
    <x-flash />

    @can('rekam-medis.verifikasi-user-epasien.update')
        @once
            @push('js')
                <script>
                    const inputNoKtp = $('input#user-ktp')
                    const inputName = $('input#user-name')
                    const inputTglLahir = $('input#user-tgl-lahir')
                    const inputNoRkmMedis = $('input#user-no-rkm-medis')

                    const buttonDropdownPilihan = $('button#pilihan')
                    const buttonResetFilter = $('button#reset-filter')

                    $(document).on('DOMContentLoaded', e => {
                        $('button#reset-filter').click(e => clearData())
                    })

                    $(document).on('data-saved', e => clearData())
                    $(document).on('data-denied', e => clearData())
                    $(document).on('hidden.bs.modal', e => clearData())

                    function loadData(e) {
                        let { userId, name, noKtp, tglLahir, noRkmMedis } = e.dataset

                        buttonDropdownPilihan.prop('disabled', false)

                        inputName.val(name)
                        inputNoKtp.val(noKtp)
                        inputTglLahir.val(tglLahir)
                        inputNoRkmMedis.val(noRkmMedis)

                        @this.emit('user.prepare', noKtp, name, tglLahir, noRkmMedis)

                    }

                    function clearData() {
                        buttonDropdownPilihan.prop('disabled', true)
                        
                        inputName.val('')
                        inputNoKtp.val('')
                        inputTglLahir.val('')
                        inputNoRkmMedis.val('')
                    }
                </script>
            @endpush
        @endonce
        <livewire:pages.rekam-medis.modal.verifikasi-pasien />
        <livewire:pages.rekam-medis.modal.map-pasien-khanza />
        <livewire:pages.rekam-medis.modal.pilih-pasien />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-name" class="text-sm">Nama</label>
                        <input type="text" class="form-control form-control-sm" id="user-name" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-ktp" class="text-sm">No KTP</label>
                        <input type="text" class="form-control form-control-sm" id="user-ktp" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-tgl-lahir" class="text-sm">Tanggal Lahir</label>
                        <input type="text"" class="form-control form-control-sm" id="user-tgl-lahir" readonly autocomplete="off">
                    </div>
                </div>
            </x-row>
            <x-row>
                <div class="col-4">
                    <div class="form-group">
                        <label for="user-no-rkm-medis" class="text-sm">No. RM</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm" id="user-no-rkm-medis" readonly autocomplete="off">
                            <x-button icon="fas fa-paperclip" size="sm" data-toggle="modal" data-target="#modal-pilih-pasien" />
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-no-rkm-medis-verified-by" class="text-sm">Diverifikasi Oleh</label>
                        <input type="text" class="form-control form-control-sm" id="user-no-rkm-medis-verified-by" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-no-rkm-medis-verified-at" class="text-sm">Tanggal Diverifikasi</label>
                        <input type="text" class="form-control form-control-sm" id="user-no-rkm-medis-verified-at" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-3">
                    <div class="d-flex align-items-end h-100">
                        <div class="mb-3 d-flex align-items-center w-100">
                            <x-dropdown livewire>
                                <x-slot name="button" title="Pilihan" icon="fas fa-cogs" disabled></x-slot>
                                <x-slot name="menu" class="dropdown-menu-left">
                                    <x-dropdown.header class="text-left">Satu Sehat</x-dropdown.header>
                                    <x-dropdown.item as="button" id="button-verifikasi-pasien" title="Verifikasi Pasien" icon="fas fa-check" data-toggle="modal" data-target="#modal-verifikasi-pasien" />
                                    <x-dropdown.divider />
                                    <x-dropdown.header class="text-left">SIMRS Khanza</x-dropdown.header>
                                    <x-dropdown.item as="button" id="button-map-pasien-khanza" icon="fas fa-user-cog fa-fw" title="Map Pasien Khanza" data-toggle="modal" data-target="#modal-map-pasien-khanza" />
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </x-row>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="name" title="Nama" />
                    <x-table.th name="email" title="Email" />
                    <x-table.th name="no_ktp" title="No KTP" />
                    <x-table.th name="tgl_lahir" title="Tanggal Lahir" />
                    <x-table.th name="no_rkm_medis" title="No RM" />
                    <x-table.th name="no_rkm_medis_verified_by" title="Diverifikasi Oleh" />
                    <x-table.th name="no_rkm_medis_verified_at" title="Tanggal Diverifikasi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('rekam-medis.verifikasi-user-epasien.update')" data-user-id="{{$item->id}}" data-name="{{$item->name}}" data-no-ktp="{{$item->no_ktp}}" data-tgl-lahir="{{$item->tgl_lahir}}" data-no-rkm-medis="{{$item->no_rkm_medis}}" >{{ $item->name }}</x-table.td>
                            <x-table.td>{{ $item->email }}</x-table.td>
                            <x-table.td>{{ $item->no_ktp }}</x-table.td>
                            <x-table.td>{{ $item->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis_verified_by }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis_verified_at }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="7" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>