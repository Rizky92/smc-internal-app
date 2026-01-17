<x-card :table="false">
    <x-slot name="header" class="pb-1">
        <h3 class="h4 text-normal">Antrean Loket</h3>
    </x-slot>
    <x-slot name="body" class="pt-1">
        <x-form livewire submit="updatePengaturanAntreanLoket" id="form-pengaturan-antrean-loket" class="pl-3">
            <x-row-col-flex>
                <label for="antrean-prefix-huruf" class="m-0 w-25 font-weight-normal">Antrean Prefix Huruf</label>
                <div class="w-100">
                    <x-form.toggle model="antreanPrefixHuruf" label="" onLabel="Ya" offLabel="Tidak" />
                    <x-form.error name="antreanPrefixHuruf" />
                </div>
            </x-row-col-flex>
            <x-row-col-flex class="mt-3">
                <label for="prefix-huruf-aktif" class="m-0 w-25 font-weight-normal">Prefix Huruf Aktif</label>
                <div class="w-100">
                    <x-form.multiple-select wire:model.defer="prefixHurufAktif" :options="$this->dataPrefixHurufAktif" id="prefix-huruf-aktif" />
                    <x-form.error name="prefixHurufAktif" />
                </div>
            </x-row-col-flex>
        </x-form>
    </x-slot>
    <x-slot name="footer" class="d-flex justify-content-end">
        <x-button size="sm" type="reset" class="ml-auto" id="reset-form" title="Reset" />
        <x-button
            size="sm"
            variant="primary"
            type="submit"
            class="ml-2"
            id="update-form"
            title="Update"
            icon="fas fa-save"
            form="form-pengaturan-antrean-loket"
            wire:target="updatePengaturanAntreanLoket"
            wire:loading.class="d-none"
            wire:loading.class.remove="btn" />
        <div wire:loading wire:target="updatePengaturanAntreanLoket" wire:loading.attr="disabled">
            <x-button size="sm" variant="primary" class="ml-2" title="Menyimpan..." icon="spinner-border spinner-border-sm" disabled />
        </div>
    </x-slot>
</x-card>
