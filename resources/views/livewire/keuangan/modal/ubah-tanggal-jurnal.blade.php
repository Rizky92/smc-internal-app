<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-ubah-tgl-jurnal').on('shown.bs.modal', e => {
                        @this.emit('utj.show')
                    })

                    $('#modal-ubah-tgl-jurnal').on('hide.bs.modal', e => {
                        @this.emit('utj.hide')
                    })
                })

                $(document).on('jurnal-updated', e => {
                    $('#modal-ubah-tgl-jurnal').modal('hide')
                })
            </script>
        @endpush
    @endonce
    <x-modal livewire id="modal-ubah-tgl-jurnal" title="Ubah Tanggal Jurnal" size="lg">
        <x-slot name="body" class="p-0 pt-3" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-row-col class="px-3">
                <x-callout variant="warning">
                    <x-slot name="title">Perhatian!</x-slot>
                    <x-slot name="content">
                        Mengubah tanggal jurnal dapat mempengaruhi kegiatan penjurnalan yang sedang berjalan.
                    </x-slot>
                </x-callout>
            </x-row-col>
            @if (auth()->user()->can('keuangan.riwayat-jurnal-perbaikan.read'))
                <x-navtabs livewire class="pt-3" selected="tab-ubah-tgl-jurnal">
                    <x-slot name="tabs">
                        <x-navtabs.tab id="tab-ubah-tgl-jurnal" title="Aksi" />
                        <x-navtabs.tab id="tab-riwayat-perubahan-jurnal" title="Riwayat Perubahan" />
                    </x-slot>
                    <x-slot name="contents">
                        <x-navtabs.content id="tab-ubah-tgl-jurnal">
                            <form wire:submit.prevent="$emit('utj.save')" class="px-3" id="form-ubah-tgl-jurnal">
                                <x-row>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="text-sm" for="no-jurnal">No. Jurnal</label>
                                            <input type="text" class="form-control form-control-sm" id="no-jurnal" wire:model.defer="noJurnal" readonly autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="text-sm" for="no-bukti">No. Bukti</label>
                                            <input type="text" class="form-control form-control-sm" id="no-bukti" wire:model.defer="noBukti" readonly autocomplete="off">
                                        </div>
                                    </div>
                                </x-row>
                                <x-row-col>
                                    <div class="form-group">
                                        <label class="text-sm" for="keterangan">Keterangan</label>
                                        <textarea class="form-control form-control-sm" id="keterangan" wire:model.defer="keterangan" readonly style="resize: none"></textarea>
                                    </div>
                                </x-row-col>
                                <x-row>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="text-sm" for="tgl-jurnal-lama">Tgl. Jurnal lama</label>
                                            <input type="date" class="form-control form-control-sm" id="tgl-jurnal-lama" wire:model.defer="tglJurnalLama" readonly autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="text-sm" for="tgl-jurnal-baru">Tgl. Jurnal BARU</label>
                                            <input type="date" class="form-control form-control-sm" id="tgl-jurnal-baru" autocomplete="off" wire:model.defer="tglJurnalBaru">
                                        </div>
                                    </div>
                                </x-row>
                            </form>
                        </x-navtabs.content>
                        <x-navtabs.content id="tab-riwayat-perubahan-jurnal">
                            <x-row-col class="p-0">
                                <div class="table-responsive">
                                    <x-table style="max-width: 100%" id="table-riwayat">
                                        <x-slot name="columns">
                                            <x-table.th title="Waktu berubah" class="pl-3" />
                                            <x-table.th title="Tgl. Jurnal Diubah" />
                                            <x-table.th title="Tgl. Jurnal Asli" />
                                            <x-table.th title="Yang mengubah" />
                                            <x-table.th title="Aksi" />
                                        </x-slot>
                                        <x-slot name="body">
                                            @forelse ($this->backupJurnal as $item)
                                                <x-table.tr>
                                                    <x-table.td class="pl-3">{{ $item->created_at->format('Y-m-d H:i:s') }}</x-table.td>
                                                    <x-table.td>{{ $item->tgl_jurnal_diubah }}</x-table.td>
                                                    <x-table.td>{{ $item->tgl_jurnal_asli }}</x-table.td>
                                                    <x-table.td>{{ $item->nip . ' ' . optional($item->pegawai)->nama }}</x-table.td>
                                                    <x-table.td>
                                                        <x-button size="xs" variant="dark" outline title="Restore" icon="fas fa-sync-alt" wire:click.prevent="restoreTglJurnal({{ $item->id }})" />
                                                    </x-table.td>
                                                </x-table.tr>
                                            @empty
                                                <x-table.tr-empty colspan="5" />
                                            @endforelse
                                        </x-slot>
                                    </x-table>
                                </div>
                            </x-row-col>
                        </x-navtabs.content>
                    </x-slot>
                </x-navtabs>
            @else
                <x-navtabs.content id="tab-ubah-tgl-jurnal">
                    <form wire:submit.prevent="$emit('utj.save')" class="px-3" id="form-ubah-tgl-jurnal">
                        <x-row>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="text-sm" for="no-jurnal">No. Jurnal</label>
                                    <input type="text" class="form-control form-control-sm" id="no-jurnal" wire:model.defer="noJurnal" readonly autocomplete="off">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="text-sm" for="no-bukti">No. Bukti</label>
                                    <input type="text" class="form-control form-control-sm" id="no-bukti" wire:model.defer="noBukti" readonly autocomplete="off">
                                </div>
                            </div>
                        </x-row>
                        <x-row-col>
                            <div class="form-group">
                                <label class="text-sm" for="keterangan">Keterangan</label>
                                <textarea class="form-control form-control-sm" id="keterangan" wire:model.defer="keterangan" readonly style="resize: none"></textarea>
                            </div>
                        </x-row-col>
                        <x-row>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="text-sm" for="tgl-jurnal-lama">Tgl. Jurnal lama</label>
                                    <input type="date" class="form-control form-control-sm" id="tgl-jurnal-lama" wire:model.defer="tglJurnalLama" readonly autocomplete="off">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="text-sm" for="tgl-jurnal-baru">Tgl. Jurnal BARU</label>
                                    <input type="date" class="form-control form-control-sm" id="tgl-jurnal-baru" autocomplete="off" wire:model.defer="tglJurnalBaru">
                                </div>
                            </div>
                        </x-row>
                    </form>
                </x-navtabs.content>
            @endif
        </x-slot>
        <x-slot name="footer" class="justify-content-end">
            <x-button size="sm" title="Batal" data-dismiss="modal" wire:click.prevent="$emit('utj.hide')" />
            <x-button type="submit" size="sm" variant="danger" class="ml-2" title="Simpan" icon="fas fa-save" form="form-ubah-tgl-jurnal" />
        </x-slot>
    </x-modal>
</div>
