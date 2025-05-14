<div wire:init="loadProperties" >
    <x-flash />

    @can('rekam-medis.verifikasi-user-epasien.update')
        @once
            @push('js')
                <script>
                    const inputUserId = $('input#user-id')
                    const inputNoKtp = $('input#user-ktp')
                    const inputName = $('input#user-name')
                    const inputTglLahir = $('input#user-tgl-lahir')
                    const inputNoRkmMedis = $('input#user-no-rkm-medis')
                    const inputVerifiedBy = $('input#user-no-rkm-medis-verified-by')

                    const buttonSimpan = $('button#simpan-data')
                    const buttonBatalSimpan = $('button#batal-simpan')
                    const buttonResetFilters = $('button#reset-filter')

                    $(document).ready(() => {
                        inputVerifiedBy.val(@json($this->verifiedByName));

                        buttonSimpan.click(e => @this.simpan(
                            inputUserId.val(),
                            inputNoRkmMedis.val(),
                            inputVerifiedBy.val()
                        ))

                        buttonResetFilters.click(clearData)
                        buttonBatalSimpan.click(clearData)

                        $(this).on('data-tersimpan', clearData)
                    })

                    function loadData(e) {
                        let { userId, name, noKtp, tglLahir, noRkmMedis,  verifiedBy } = e.dataset
                        inputUserId.val(userId)
                        inputName.val(name)
                        inputNoKtp.val(noKtp)
                        inputTglLahir.val(tglLahir)
                        inputNoRkmMedis.val(noRkmMedis)
                        inputVerifiedBy.val(@json($this->verifiedByName))

                        inputUserId.trigger('change')
                        inputName.trigger('change')
                        inputNoKtp.trigger('change')
                        inputTglLahir.trigger('change')
                        inputNoRkmMedis.trigger('change')
                        inputVerifiedBy.trigger('change')

                        buttonSimpan.prop('disabled', false)
                        buttonBatalSimpan.prop('disabled', false)
                    }

                    function clearData() {
                        inputUserId.val('')
                        inputName.val('')
                        inputNoKtp.val('')
                        inputTglLahir.val('')
                        inputNoRkmMedis.val('')

                        inputName.trigger('change')
                        inputNoKtp.trigger('change')
                        inputTglLahir.trigger('change')
                        inputNoRkmMedis.trigger('change')

                        buttonSimpan.prop('disabled', true)
                        buttonBatalSimpan.prop('disabled', true)
                    }

                    document.addEventListener('data-updated', event => {
                        const { noRkmMedis } = event.detail;
                        $('input#user-no-rkm-medis').val(noRkmMedis);
                    });
                </script>
            @endpush
        @endonce
        <livewire:pages.rekam-medis.modal.pilih-pasien />
    @endcan

    <x-card use-loading>
        <x-slot name="header">
            <x-row>
                <div class="col-3">
                    <div class="form-group">
                        <label for="user-id" class="text-sm">User ID</label>
                        <input type="text" class="form-control form-control-sm" id="user-id" readonly autocomplete="off">
                    </div>
                </div>
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
                            <input type="text" class="form-control form-control-sm" id="user-no-rkm-medis" autocomplete="off">
                            <x-button icon="fas fa-paperclip" size="sm" id="pilih-pasien" data-toggle="modal" data-target="#modal-pilih-pasien" />
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="user-no-rkm-medis-verified-by" class="text-sm">Diverifikasi Oleh</label>
                        <input type="text" class="form-control form-control-sm p" id="user-no-rkm-medis-verified-by" readonly autocomplete="off">
                    </div>
                </div>
            </x-row>
            <x-row-col class="pb-3 border-bottom">
                <x-button size="sm" variant="primary" id="simpan-data" title="Simpan" icon="fas fa-save" />
                <x-button size="sm" class="ml-2" id="batal-simpan" title="Batal" />
            </x-row-col>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.toggle class="ml-2" title="Tampilkan Semua User" model="semuaUser" />
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
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td :clickable="user()->can('rekam-medis.verifikasi-user-epasien.update')" data-user-id="{{$item->id}}" data-name="{{$item->name}}" data-no-ktp="{{$item->no_ktp}}" data-tgl-lahir="{{$item->tgl_lahir}}" data-no-rkm-medis="{{$item->no_rkm_medis}}" data-verified-by="{{$item->no_rkm_medis_verified_by}}" >{{ $item->name }}</x-table.td>
                            <x-table.td>{{ $item->email }}</x-table.td>
                            <x-table.td>{{ $item->no_ktp }}</x-table.td>
                            <x-table.td>{{ $item->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis_verified_by }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="6" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>