<x-card :table="false">
    <x-slot name="header" class="pb-1">
        <h3 class="h4 text-normal">Faktur Pajak</h3>
    </x-slot>
    <x-slot name="body" class="pt-1">
        <x-form livewire submit="updateNPWPPenjual" id="form-npwp-penjual" class="pl-3">
            <x-row-col-flex>
                <label for="npwp-penjual" class="m-0 w-25 font-weight-normal">No. NPWP Penjual</label>
                <div class="w-100">
                    <x-form.text id="npwp-penjual" model="npwpPenjual" width="max-content" />
                    <x-form.error name="npwpPenjual" />
                </div>
            </x-row-col-flex>
        </x-form>
    </x-slot>
    <x-slot name="footer" class="d-flex justify-content-end">
        <x-button size="sm" type="reset" class="ml-auto" id="reset-form" title="Reset" />
        <x-button size="sm" variant="primary" type="submit" class="ml-2" id="update-form-npwp-penjual" title="Update" icon="fas fa-save" form="form-npwp-penjual" />
    </x-slot>
</x-card>
