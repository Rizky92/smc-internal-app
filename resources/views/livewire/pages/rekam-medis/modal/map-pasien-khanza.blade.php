<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-map-pasien-khanza').on('shown.bs.modal', e => {
                        @this.emit('epasien.show-map-pasien')
                    })

                    $('#modal-map-pasien-khanza').on('hide.bs.modal', e => {
                        @this.emit('epasien.hide-map-pasien')
                    })
                })
            </script>
        @endpush
    @endonce

    <x-modal title="Map Pasien Khanza" id="modal-map-pasien-khanza" size="xl" livewire centered static dismisable="false">
        <x-slot name="body">
            <x-row-col class="px-3 pt-3">
                <div class="form-group mt-3">
                    <label for="no-ktp">No KTP</label>
                    <input type="text" id="no-ktp" wire:model.defer="noKtp"
                        class="form-control form-control-sm" />
                    <x-form.error name="noKtp" />
                </div>
                <div class="form-group mt-3">
                    <label for="name">Nama</label>
                    <input type="text" id="name" wire:model.defer="name"
                        class="form-control form-control-sm" />
                    <x-form.error name="name" />
                </div>
                <div class="form-group mt-3">
                    <label for="tgl-lahir">Tanggal Lahir</label>
                    <x-form.date model="tglLahir" />
                    <x-form.error name="tglLahir" />
                </div>
                {{-- <div class="form-group mt-3">
                    <label for="no-telp">No. RM</label>
                    <input title="No. RM" type="text" id="no-rkm-medis" wire:model.defer="noRkmMedis" class="form-control form-control-sm" readonly />
                    <x-button size="sm" id="button-pilih-pasien" title="Pilih Pasien" data-toggle="modal" data-target="#modal-pilih-pasien" />
                </div> --}}
            </x-row-col>
            <x-row-col class="pt-2">
                <x-table zebra hover sortable :sortColumns="$sortColumns">
                    <x-slot name="columns">
                        {{-- <x-table.th-checkbox-all
                            livewire
                            class="pl-3"
                            style="width: max-content"
                            id="checkbox-set-hak-akses"
                            name="__checkbox_sha_utama"
                            model="checkedHakAkses"
                            lookup="sha-"
                        /> --}}
                        <x-table.th name="no_rkm_medis" title="No. RM" />
                        <x-table.th name="nm_pasien" title="Nama Pasien" />
                        <x-table.th name="tgl_lahir" title="Tanggal Lahir" />
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($this->pasien as $pasien)
                            <x-table.tr>
                                {{-- <x-table.td-checkbox
                                    livewire
                                    class="pl-3"
                                    model="checkedHakAkses"
                                    :key="$hakAkses->nama_field"
                                    :id="$hakAkses->nama_field"
                                    prefix="sha-"
                                /> --}}
                                <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                                <x-table.td>{{ $pasien->nm_pasien }}</x-table.td>
                                <x-table.td>{{ $pasien->tgl_lahir }}</x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr-empty colspan="3" padding />
                        @endforelse
                    </x-slot>
                </x-table>
                <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->pasien" />
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="d-flex justify-content-end">
            <x-filter.button-reset-filters class="ml-auto" />
            <x-filter.search class="ml-2" />
        </x-slot>
    </x-modal>
</div>
