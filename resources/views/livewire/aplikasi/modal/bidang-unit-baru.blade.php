<div>
    <x-modal id="modal-bidang-baru" title="Tambah Bidang Baru untuk RKAT" livewire centered>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-bidang-baru" wire:submit.prevent="{{ $bidangId !== '' ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-3 pb-2 px-3">
                    <div class="form-group mt-3">
                        <label for="bidang-sekarang">Nama Bidang:</label>
                        <input type="text" id="bidang-sekarang" wire:model.defer="namaBidang" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-bidang-baru" />
        </x-slot>
    </x-modal>
</div>
