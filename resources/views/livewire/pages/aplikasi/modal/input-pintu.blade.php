@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />

        <style>
            .select2-selection__arrow {
                top: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 2rem !important;
            }

            .select2-container .select2-selection--single .select2-selection__rendered {
                padding-left: 0 !important;
                margin-left: -0.125rem !important;
            }
        </style>
    @endonce
@endpush

<div>
    @push('js')
        <script>
            $('#modal-input-pintu').on('shown.bs.modal', e => {
                Livewire.emit('pintu.show-modal')
            })

            $('#modal-input-pintu').on('hide.bs.modal', e => {
                Livewire.emit('pintu.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-pintu').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-pintu" :title="($this->isUpdating() ? 'Edit Mapping Pintu' : 'Mapping Pintu')" livewire centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-pintu" livewire wire:submit.prevent="create">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group">
                        <label for="pintu">Pintu:</label>
                        <div wire:ignore>
                            <select id="kodePintu" wire:model="kodePintu" class="form-control form-control-sm select2 input-sm">
                                @if ($this->pintuPlaceholder)
                                    <option hidden selected value="{{ $this->pintuPlaceholder }}">
                                        {{ $this->pintuPlaceholderText }}
                                    </option>
                                    <option disabled>{{ $this->pintuPlaceholderText }}</option>
                                @endif

                                @foreach ($this->pintu as $kd_pintu => $nm_pintu)
                                    <option value="{{ $kd_pintu }}">
                                        {{ $nm_pintu }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-form.error name="kodePintu" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="poli">Poli:</label>
                        <div wire:ignore>
                            <select id="kodePoliklinik" wire:model="kodePoliklinik" class="form-control form-control-sm select2 input-sm">
                                @foreach ($this->poliklinik as $kd_poli => $nm_poli)
                                    <option value="{{ $kd_poli }}">
                                        {{ $nm_poli }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-form.error name="kodePoliklinik" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="dokter">Dokter:</label>
                        <div wire:ignore width="100%">
                            <select id="kodeDokter" wire:model="kodeDokter" class="form-control form-control-sm select2 input-sm">
                                @foreach ($this->dokter as $kd_dokter => $nm_dokter)
                                    <option value="{{ $kd_dokter }}">
                                        {{ $nm_dokter }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-form.error name="kodeDokter" />
                    </div>
                    @push('js')
                        @once
                            <script src="{{ asset('js/select2.full.min.js') }}"></script>
                        @endonce

                        <script>
                            document.addEventListener('livewire:load', function () {
                                $('#kodePintu').select2();
                                $('#kodePintu').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.emit('inputPintu.setKodePintu', data);
                                });

                                $('#kodePoliklinik').select2();
                                $('#kodePoliklinik').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.emit('inputPintu.setKodePoliklinik', data);
                                });

                                $('#kodeDokter').select2();
                                $('#kodeDokter').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.emit('inputPintu.setKodeDokter', data);
                                });
                            });

                            document.addEventListener('livewire:update', function () {
                                $('#kodePintu').select2();
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
            <x-button
                size="sm"
                variant="primary"
                class="ml-2"
                type="submit"
                id="simpan-data"
                title="Simpan"
                icon="fas fa-save"
                form="form-input-pintu"
                wire:target="create"
                wire:loading.class="d-none"
                wire:loading.class.remove="btn" />
            <div wire:loading wire:target="create" wire:loading.attr="disabled">
                <x-button size="sm" variant="primary" class="ml-2" title="Menyimpan..." icon="spinner-border spinner-border-sm" disabled />
            </div>
        </x-slot>
    </x-modal>
</div>
