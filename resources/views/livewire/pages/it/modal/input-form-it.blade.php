<div>
    @push('js')
        <script>
            $('#modal-input-tiket').on('shown.bs.modal', (e) => {
                Livewire.emit('input-tiket.show-modal');
            });

            $('#modal-input-tiket').on('hide.bs.modal', (e) => {
                Livewire.emit('input-tiket.hide-modal');
            });

            $(document).on('data-saved', () => {
                $('#modal-input-tiket').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-tiket" title="Input Tiket" livewire centered size="lg">
        <x-slot name="body">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-tiket" livewire wire:submit.prevent="save">
                <x-row-col>
                    <div class="form-group">
                        <label class="form-control-label text-sm" for="title">Judul</label>
                        <input type="text" id="title" wire:model.defer="title" class="form-control form-control-sm" placeholder="Judul keluhan" />
                        <x-form.error name="title" />
                    </div>
                </x-row-col>

                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="category_id">Kategori</label>
                            <x-form.select id="category_id" model="category_id" :options="$this->categories" placeholder="Pilih Kategori" />
                            <x-form.error name="category_id" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="priority">Prioritas</label>
                            <x-form.select id="priority" model="priority" :options="$this->priorityOptions" />
                            <x-form.error name="priority" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="department_id">Departemen</label>
                            <x-form.select id="department_id" model="department_id" :options="$this->departments" placeholder="Pilih Departemen" />
                            <x-form.error name="department_id" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="location">Lokasi</label>
                            <input type="text" id="location" wire:model.defer="location" class="form-control form-control-sm" placeholder="Lokasi/Ext" />
                        </div>
                    </div>
                </x-row>

                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="reporter_name">Nama Pelapor</label>
                            <input type="text" id="reporter_name" wire:model.defer="reporter_name" class="form-control form-control-sm" placeholder="Nama pelapor" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="reporter_phone">No. HP Pelapor</label>
                            <input type="text" id="reporter_phone" wire:model.defer="reporter_phone" class="form-control form-control-sm" placeholder="Nomor HP" />
                        </div>
                    </div>
                </x-row>

                <x-row-col>
                    <div class="form-group">
                        <label class="form-control-label text-sm" for="description">Deskripsi</label>
                        <textarea id="description" wire:model.defer="description" class="form-control form-control-sm" rows="4" placeholder="Detail keluhan..."></textarea>
                        <x-form.error name="description" />
                    </div>
                </x-row-col>

                <x-row-col>
                    <div class="form-group">
                        <label class="form-control-label text-sm">Lampiran (Opsional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="attachments" wire:model="attachments" multiple />
                            <label class="custom-file-label" for="attachments">Pilih file...</label>
                        </div>
                        <div wire:loading wire:target="attachments" class="text-primary small mt-1">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            Mengunggah...
                        </div>
                        <x-form.error name="attachments.*" />

                        @if ($attachments)
                            <div class="mt-2">
                                <ul class="list-unstyled small">
                                    @foreach ($attachments as $index => $file)
                                        <li class="text-muted">
                                            <i class="fas fa-file mr-1"></i>
                                            {{ $file->getClientOriginalName() }}
                                            <button type="button" class="btn btn-xs text-danger" wire:click="$set('attachments.{{ $index }}', null)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button variant="primary" type="submit" form="form-input-tiket" title="Simpan" wire:loading.attr="disabled" />
            <x-button class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
        </x-slot>
    </x-modal>
</div>
