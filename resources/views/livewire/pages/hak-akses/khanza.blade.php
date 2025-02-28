<div>
    <x-flash />

    @once
        @push('js')
            <script>
                const inputNamaField = $('input#field')
                const inputJudulMenu = $('input#judul')

                const buttonSimpan = $('button#simpan')
                const buttonBatalSimpan = $('button#batal')
                const buttonResetFilter = $('button#reset-filter')

                buttonSimpan.click(e => {
                    @this.simpanHakAkses(inputNamaField.val(), inputJudulMenu.val())

                    resetInput(e)
                })

                buttonBatalSimpan.click(resetInput)
                buttonResetFilter.click(resetInput)

                $(document).on('data-saved', resetInput)

                inputNamaField.on('keyup', e => {
                    if (inputNamaField.val().trim() == '' && inputJudulMenu.val().trim() == '') {
                        setFormState('disabled', true)
                    }

                    setFormState('disabled', false)
                })

                inputJudulMenu.on('keyup', e => {
                    if (!inputNamaField.val() && !inputJudulMenu.val()) {
                        setFormState('disabled', true)
                    }

                    setFormState('disabled', false)
                })

                function resetInput(e) {
                    inputNamaField.val(null)
                    inputJudulMenu.val(null)

                    inputNamaField.trigger('change')
                    inputJudulMenu.trigger('change')

                    setFormState('disabled', true)
                }

                function loadData(e) {
                    let { namaField, judulMenu } = e.dataset

                    setFormState('disabled', false)

                    inputNamaField.val(namaField)
                    inputJudulMenu.val(judulMenu)

                    inputNamaField.trigger('change')
                    inputJudulMenu.trigger('change')
                }

                function setFormState(prop, state) {
                    buttonSimpan.prop(prop, state)
                    buttonBatalSimpan.prop(prop, state)
                }
            </script>
        @endpush
    @endonce

    <x-card use-loading>
        <x-slot name="header">
            <x-row livewire>
                <div class="col-5">
                    <div class="form-group">
                        <label class="text-sm" for="field">Nama Field</label>
                        <input class="form-control form-control-sm" id="field" type="text" autocomplete="off" />
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label class="text-sm" for="judul">Judul Menu</label>
                        <input class="form-control form-control-sm" id="judul" type="text" autocomplete="off" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-end h-100">
                        <x-button size="sm" class="mb-3" title="Batal" disabled />
                        <x-button size="sm" variant="primary" class="mb-3 ml-2" title="Simpan" icon="fas fa-save" disabled />
                    </div>
                </div>
            </x-row>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
                <x-filter.button-refresh method="syncHakAkses" icon="fas fa-sync-alt" title="Sync Hak Akses" class="ml-3" />
            </x-row-col-flex>
        </x-slot>

        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 50%" name="nama_field" title="Nama Field" />
                    <x-table.th style="width: 50%" name="judul_menu" title="Judul Menu" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->hakAksesKhanza as $hakAkses)
                        <x-table.tr>
                            <x-table.td clickable data-nama-field="{{ $hakAkses->nama_field }}" data-judul-menu="{{ $hakAkses->judul_menu }}">
                                {{ $hakAkses->nama_field }}
                            </x-table.td>
                            <x-table.td>
                                {{ $hakAkses->judul_menu }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="2" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->hakAksesKhanza" />
        </x-slot>
    </x-card>
</div>
