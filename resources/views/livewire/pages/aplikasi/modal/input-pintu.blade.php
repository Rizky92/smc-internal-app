@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />
    @endonce
@endpush

<div>
    @push('js')
        <script>
            $('#modal-input-pintu').on('shown.bs.modal', e => {
                @this.emit('pintu.show-modal')
            })

            $('#modal-input-pintu').on('hide.bs.modal', e => {
                @this.emit('pintu.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-pintu').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-pintu" :title="($this->isUpdating() ? 'Edit Data Pintu' : 'Input Data Pintu')" livewire centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-pintu" livewire :submit="$this->isUpdating() ? 'update' : 'create'">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group">
                        <label for="kd-pintu">Kode Pintu:</label>
                        <input type="text" id="kd-pintu" wire:model.defer="kodePintu" class="form-control form-control-sm" />
                        <x-form.error name="kodePintu" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="nm-pintu">Nama Pintu:</label>
                        <input type="text" id="nm-pintu" wire:model.defer="namaPintu" class="form-control form-control-sm" />
                        <x-form.error name="namaPintu" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="poli">Poli:</label>
                        <div wire:ignore>
                            <select id="kodePoliklinik" wire:model="kodePoliklinik" class="form-control select2-poli" multiple>
                                @foreach ($this->poliklinik as $kd_poli => $nm_poli)
                                    <option value="{{ $kd_poli }}">
                                        {{ $nm_poli }}
                                    </option>
                                @endforeach
                            </select>
                            <x-form.error name="kodePoliklinik" />
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="dokter">Dokter:</label>
                        <div wire:ignore>
                            <select id="kodeDokter" wire:model="kodeDokter" class="form-control select2-dokter" multiple>
                                @foreach ($this->dokter as $kd_dokter => $nm_dokter)
                                    <option value="{{ $kd_dokter }}">
                                        {{ $nm_dokter }}
                                    </option>
                                @endforeach
                            </select>
                            <x-form.error name="kodeDokter" />
                        </div>
                    </div>
                    @push('js')
                        @once
                            <script src="{{ asset('js/select2.full.min.js') }}"></script>
                        @endonce

                        <script>
                            document.addEventListener('livewire:load', function () {

                                $('#kodePoliklinik').select2();
                                $('#kodePoliklinik').on('change', function (e) {
                                    var data = $(this).val();
                                    @this.set('kodePoliklinik', data);
                                });

                                $('#kodeDokter').select2();
                                $('#kodeDokter').on('change', function (e) {
                                    var data = $(this).val();
                                    @this.set('kodeDokter', data);
                                });
                            });

                            document.addEventListener('livewire:update', function () {
                                $('#kodePoliklinik').select2();
                                $('#kodeDokter').select2();
                            });
                        </script>
                    @endpush
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer">
            @if ($this->isUpdating() && user()->can('antrean.manajemen-pintu.delete'))
                <x-button size="sm" variant="danger" data-dismiss="modal" id="hapusdata" title="Hapus" icon="fas fa-trash" wire:click="delete" />
            @endif

            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" type="submit" id="simpan-data" title="Simpan" icon="fas fa-save" form="form-input-pintu" />
        </x-slot>
    </x-modal>
</div>
