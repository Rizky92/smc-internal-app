<div>
    @push('js')
        <script>
            $('#modal-input-pelaporan-rkat').on('shown.bs.modal', e => {
                @this.emit('pelaporan-rkat.show-modal')
            })

            $('#modal-input-pelaporan-rkat').on('hide.bs.modal', e => {
                @this.emit('pelaporan-rkat.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-pelaporan-rkat').modal('hide')
            })
        </script>
    @endpush

    <x-modal
        id="modal-input-pelaporan-rkat"
        :title="($this->isUpdating() ? 'Edit Data Penggunaan RKAT' : 'Input Data Penggunaan RKAT') .
            ' Tahun ' .
        $this->tahun"
        livewire
        centered>
        <x-slot name="body" style="overflow-x: hidden">
            <x-form id="form-input-pelaporan-rkat" livewire :submit="$this->isUpdating() ? 'update' : 'create'">
                <x-row-col class="sticky-top bg-white">
                    <div class="form-group">
                        <label for="anggaran-bidang-id">Anggaran bidang digunakan:</label>
                        <x-form.select2 id="anggaran-bidang-id" model="anggaranBidangId" :options="$this->dataRKATPerBidang" placeholder="-" width="full-width" />
                        <x-form.error name="anggaranBidangId" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="tgl-pemakaian">Tgl. Pemakaian</label>
                        <x-form.date model="tglPakai" />
                        <x-form.error name="tglPakai" />
                    </div>
                    <div class="form-group mt-3">
                        <label for="keterangan">Keterangan</label>
                        <input type="text" id="keterangan" wire:model.defer="keterangan" class="form-control form-control-sm" />
                        <x-form.error name="keterangan" />
                    </div>
                    @if (! $this->isUpdating())
                        <div class="form-group mt-3">
                            <label for="upload">Upload File</label>
                            <input type="file" id="upload" wire:model="fileImport" class="form-control-file" accept=".xlsx, .xls" />
                            <a href="{{ asset('templates/template-import-pelaporan-rkat.xlsx') }}" download class="btn btn-link btn-sm mt-2">Template Import</a>
                        </div>
                    @endif

                    <div class="form-group mt-3">
                        <div class="d-flex justify-content-start align-items-center">
                            <span class="d-block font-weight-bold" style="width: calc(75% - 1.6rem)">Nama Pengeluaran</span>
                            <span class="d-block font-weight-bold">Nominal</span>
                        </div>
                        <ul class="p-0 m-0 mt-2 mb-3 d-flex flex-column" style="row-gap: 0.5rem" id="detail-pemakaian">
                            @foreach ($this->detail as $index => $item)
                                <li class="d-flex justify-content-start align-items-center m-0 p-0" wire:key="detail-pelaporan-{{ $index }}">
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="detail.{{ $index }}.keterangan" />
                                    <span class="ml-4 text-sm" style="width: 3rem">Rp.</span>
                                    <input type="text" class="form-control form-control-sm text-right w-25" wire:model.defer="detail.{{ $index }}.nominal" />
                                    @can('keuangan.rkat-pelaporan.update')
                                        <button type="button" wire:click="removeDetail({{ $index }})" class="btn btn-sm btn-danger ml-3">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                        @can('keuangan.rkat-pelaporan.update')
                            <x-button size="sm" variant="secondary" title="Tambah Detail" icon="fas fa-plus" wire:click="addDetail" />
                        @endcan

                        <div class="mt-1">
                            <x-form.error name="nominalPemakaian" />
                        </div>
                    </div>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            @can('keuangan.rkat-pelaporan.update')
                <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
                <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-input-pelaporan-rkat" />
            @endcan
        </x-slot>
    </x-modal>
</div>
