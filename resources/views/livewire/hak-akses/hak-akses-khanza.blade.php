<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let inputNamaField
                let inputJudulMenu

                $(document).ready(e => {
                    inputNamaField = $('#nama_field')
                    inputJudulMenu = $('#judul_menu')
                })

                function loadData({
                    namaField,
                    judulMenu
                }) {
                    inputNamaField.val(namaField)
                    inputJudulMenu.val(judulMenu)

                    inputNamaField.trigger('change')
                    inputJudulMenu.trigger('change')
                }

                function resetInput() {
                    inputNamaField.val('')  
                    inputJudulMenu.val('')

                    inputNamaField.trigger('change')
                    inputJudulMenu.trigger('change')
                }
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header">
            <x-card.row :livewire="true">
                <div class="col-5">
                    <div class="form-group">
                        <label class="text-sm" for="nama_field">Nama Field</label>
                        <input class="form-control form-control-sm" id="nama_field" type="text" autocomplete="off">
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label class="text-sm" for="judul_menu">Judul Menu</label>
                        <input class="form-control form-control-sm" id="judul_menu" type="text" autocomplete="off">
                    </div>
                </div>
                {{-- <div class="col-2">
                    <div class="d-flex justify-content-end align-items-end h-100">
                        <button class="btn btn-sm btn-default mb-3" type="button" onclick="resetInput()">
                            Batal
                        </button>
                        <button class="btn btn-sm btn-primary mb-3 ml-2" type="button" wire:click="simpandata">
                            <i class="fas fa-save"></i>
                            <span class="ml-1">Simpan</span>
                        </button>
                    </div>
                </div> --}}
            </x-card.row>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="nama_field" title="Nama Field" />
                    <x-table.th name="judul_menu" title="Judul Menu" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->hakAksesKhanza as $hakAkses)
                        <x-table.tr>
                            <x-table.td>
                                {{ $hakAkses->nama_field }}
                                <x-slot name="clickable" data-nama-field="{{ $hakAkses->nama_field }}" data-judul-menu="{{ $hakAkses->judul_menu }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $hakAkses->judul_menu }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->hakAksesKhanza" />
        </x-slot>
    </x-card>
</div>
