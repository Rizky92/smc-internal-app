<x-card :table="false">
    <x-slot name="header" class="pb-1">
        <h3 class="h4 text-normal">RKAT</h3>
    </x-slot>
    <x-slot name="body" class="pt-1">
        <x-form livewire submit="updatePengaturanRKAT" id="form-pengaturan-rkat" class="pl-3">
            <x-row-col-flex>
                <label for="tahun-rkat" class="m-0 w-25 font-weight-normal">Tahun RKAT</label>
                <div class="w-100">
                    <x-form.select id="tahun-rkat" model="tahunRKAT" :options="$this->dataTahun" width="max-content" />
                    <x-form.error name="tahunRKAT" />
                </div>
            </x-row-col-flex>
            <x-row-col-flex class="mt-3">
                <label for="penetapan-rkat" class="m-0 w-25 font-weight-normal">Periode Waktu Penetapan RKAT</label>
                <div class="w-100">
                    <x-form.range-date model-start="tglAwalPenetapanRKAT" model-end="tglAkhirPenetapanRKAT" class="w-100" />
                    <x-form.error name="tglAwalPenetapanRKAT" />
                    <x-form.error name="tglAkhirPenetapanRKAT" />
                </div>
            </x-row-col-flex>
        </x-form>
    </x-slot>
    <x-slot name="footer" class="d-flex justify-content-end">
        <x-button size="sm" type="reset" class="ml-auto" id="reset-form" title="Reset" />
        <x-button size="sm" variant="primary" type="submit" class="ml-2" id="update-form-pengaturan-rkat" title="Update" icon="fas fa-save" form="form-pengaturan-rkat" />
    </x-slot>
</x-card>
