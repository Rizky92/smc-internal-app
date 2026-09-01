@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />

        <style>
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: rgb(245, 245, 245);
                color: #1f2d3d;
                font-weight: 700;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: rgb(173, 173, 173);
                float: right;
            }
        </style>
    @endonce
@endpush

<div>
    @push('js')
        <script>
            // Use show.bs.modal to inspect the triggering element (relatedTarget).
            // Only clear the select2 when the modal is opened via the Create button
            // (which has data-action="create"). When opening for edit, the
            // application calls Livewire.prepare() and then shows the modal; the
            // server will emit 'inputPintu.syncSelectedJadwal' to populate select2.
            $('#modal-input-pintu').on('show.bs.modal', function (e) {
                // relatedTarget is the element that triggered the modal (if any)
                var trigger = e.relatedTarget || null;

                // If the trigger indicates a create action, clear the select2
                var shouldClear = false;
                try {
                    if (trigger && trigger.dataset && trigger.dataset.action === 'create') {
                        shouldClear = true;
                    }
                } catch (err) {
                    // ignore
                }

                if (shouldClear) {
                    try {
                        $('#selectedJadwal').val(null).trigger('change');
                    } catch (err) {
                        // ignore if select2 isn't ready
                    }
                }

                // Notify Livewire that modal was shown (modal lifecycle hook)
                Livewire.dispatch('pintu.show-modal');
            });

            $('#modal-input-pintu').on('hide.bs.modal', (e) => {
                Livewire.dispatch('pintu.hide-modal');
            });

            $(document).on('data-saved', () => {
                $('#modal-input-pintu').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-pintu" :title="($this->isUpdating() ? 'Edit Mapping Pintu' : 'Mapping Pintu')" livewire centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-pintu" livewire wire:submit.prevent="create">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group">
                        <label for="kodePintu">Kode Pintu:</label>
                        <input type="text" wire:model="kodePintu" class="form-control form-control-sm" />
                        <x-form.error name="kodePintu" />
                    </div>
                    <div class="form-group">
                        <label for="namaPintu">Nama Pintu:</label>
                        <input type="text" wire:model="namaPintu" class="form-control form-control-sm" />
                        <x-form.error name="namaPintu" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="poli">Jadwal Praktik Dokter</label>
                        <div wire:ignore>
                            <select id="selectedJadwal" wire:model="selectedJadwal" class="form-control form-control-sm select2 input-sm" multiple>
                                @foreach ($this->jadwalPraktik as $jadwal)
                                    @php
                                        $kdDokter = $jadwal->kd_dokter;
                                        $kdPoli = $jadwal->kd_poli;
                                        $labelDokter = $jadwal->dokter->nm_dokter ?? $kdDokter;
                                        $labelPoli = $jadwal->poliklinik->nm_poli ?? $kdPoli;
                                        $value = $kdDokter . '|' . $kdPoli;
                                    @endphp

                                    <option value="{{ $value }}">{{ $labelDokter }} — {{ $labelPoli }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-form.error name="selectedJadwal" />
                    </div>
                    @push('js')
                        @once
                            <script src="{{ asset('js/select2.full.min.js') }}"></script>
                        @endonce

                        <script>
                            document.addEventListener('livewire:load', function () {
                                $('#selectedJadwal').select2();
                                $('#selectedJadwal').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.dispatch('inputPintu.setSelectedJadwal', data);
                                });

                                // Listen for server-side event to sync select2 selection
                                Livewire.on('inputPintu.syncSelectedJadwal', function (data) {
                                    // set value (array) and trigger change so Livewire receives it if needed
                                    $('#selectedJadwal').val(data).trigger('change');
                                });

                                $('#kodePintu').select2();
                                $('#kodePintu').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.dispatch('inputPintu.setKodePintu', data);
                                });

                                $('#kodePoliklinik').select2();
                                $('#kodePoliklinik').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.dispatch('inputPintu.setKodePoliklinik', data);
                                });

                                $('#kodeDokter').select2();
                                $('#kodeDokter').on('change', function (e) {
                                    var data = $(this).val();
                                    Livewire.dispatch('inputPintu.setKodeDokter', data);
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
