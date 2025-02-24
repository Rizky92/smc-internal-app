<div>
    @push('js')
        <script>
            $('#modal-input-posting-jurnal').on('shown.bs.modal', e => {
                @this.emit('posting-jurnal.show-modal')
            })

            $('#modal-input-posting-jurnal').on('hide.bs.modal', e => {
                @this.emit('posting-jurnal.hide-modal')
            })

            $(document).on('data-saved', () => {
                $('#modal-input-posting-jurnal').modal('hide')
            })
        </script>
    @endpush

    <x-modal id="modal-input-posting-jurnal" size="xl" :title="'Posting Jurnal Baru'" livewire centered static>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />
            <x-form id="form-input-posting-jurnal" wire:submit.prevent="create">
                <x-row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="no_bukti">No. Bukti</label>
                            <input type="text" id="no_bukti" wire:model.defer="no_bukti" class="form-control form-control-sm" autocomplete="off" />
                            <x-form.error name="no_bukti" />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="jenis">Jenis</label>
                            <x-form.select id="jenis" model="jenis" :options="['U' => 'Umum', 'P' => 'Penyesuaian']" />
                            <x-form.error name="jenis" />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="tgl_jurnal">Tanggal</label>
                            <x-form.date model="tgl_jurnal" />
                            <x-form.error name="tgl_jurnal" />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label class="form-control-label text-sm" for="jam_jurnal">Jam</label>
                            <input type="text" id="jam_jurnal" wire:model.defer="jam_jurnal" class="form-control form-control-sm" autocomplete="off" />
                            <x-form.error name="jam_jurnal" />
                        </div>
                    </div>
                </x-row>
                <x-row-col>
                    <div class="form-group">
                        <label class="form-control-label text-sm" for="keterangan">Keterangan</label>
                        <input type="text" id="keterangan" wire:model.defer="keterangan" class="form-control form-control-sm" autocomplete="off" />
                        <x-form.error name="keterangan" />
                    </div>
                </x-row-col>
                <x-row-col>
                    <x-table zebra hover sticky nowrap>
                        <x-slot name="columns">
                            <x-table.th title="Rekening" style="width: 60%" />
                            <x-table.th title="Debet" />
                            <x-table.th title="Kredit" />
                        </x-slot>
                        <x-slot name="body">
                            @foreach ($this->detail ?? [] as $index => $item)
                                <x-table.tr>
                                    <x-table.td>
                                        <div style="width: 100%" wire:ignore>
                                            <select
                                                id="kd_rek_{{ $index }}"
                                                wire:model.defer="detail.{{ $index }}.kd_rek"
                                                class="form-control form-control-sm select2 input-sm"
                                                data-index="{{ $index }}">
                                                <option value="">Pilih Rekening</option>
                                                @foreach ($this->rekening as $kd_rek => $rekening)
                                                    <option value="{{ $kd_rek }}">
                                                        {{ $kd_rek }} -
                                                        {{ $rekening }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-form.error name="detail.{{ $index }}.kd_rek" />
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

                                            @push('js')
                                                @once
                                                    <script src="{{ asset('js/select2.full.min.js') }}"></script>
                                                @endonce

                                                <script>
                                                    document.addEventListener('livewire:load', function (e) {
                                                        Livewire.on('detailAdded', function () {
                                                            $('.select2').select2({ dropdownCssClass: 'text-sm px-0' });
                                                        })
                                                    })
                                                    $(document).ready(function (e) {
                                                        $('.select2').select2({ dropdownCssClass: 'text-sm px-0' })
                                                        $(document).on('change', '.select2', function (e) {
                                                            @this.set('detail.' + $(this).data('index') + '.kd_rek', $(this).val())
                                                        })
                                                    })
                                                </script>
                                            @endpush
                                        </div>
                                        <x-form.error name="detail.{{ $index }}.kd_rek" />
                                    </x-table.td>
                                    <x-table.td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="debet-{{ $index }}">Rp.</label>
                                            </div>
                                            <input type="number" id="debet-{{ $index }}" class="form-control text-right" wire:model.defer="detail.{{ $index }}.debet" />
                                        </div>
                                        <x-form.error name="detail.{{ $index }}.debet" />
                                    </x-table.td>
                                    <x-table.td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="kredit-{{ $index }}">Rp.</label>
                                            </div>
                                            <input type="number" id="kredit-{{ $index }}" class="form-control text-right" wire:model.defer="detail.{{ $index }}.kredit" />
                                        </div>
                                        <x-form.error name="detail.{{ $index }}.kredit" />
                                    </x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-slot>
                        <x-slot name="footer">
                            <x-table.tr>
                                <x-table.td>
                                    <x-button size="sm" variant="secondary" title="Tambah Detail" icon="fas fa-plus" wire:click="add" />
                                    <x-form.error name="totalDebitKredit" />
                                </x-table.td>
                                <x-table.td>
                                    {{ rp($this->totalDebet) }}
                                </x-table.td>
                                <x-table.td>
                                    {{ rp($this->totalKredit) }}
                                </x-table.td>
                            </x-table.tr>
                        </x-slot>
                    </x-table>
                </x-row-col>
                <x-row-col class="mt-3">
                    <h5>Jurnal Sementara</h5>
                    <x-table sticky nowrap>
                        <x-slot name="columns">
                            <x-table.th title="#" />
                            <x-table.th title="No. Bukti" />
                            <x-table.th title="Tanggal" />
                            <x-table.th title="Jam" />
                            <x-table.th title="Jenis" />
                            <x-table.th title="Keterangan" />
                            <x-table.th title="Kode" />
                            <x-table.th title="Rekening" />
                            <x-table.th title="Debet" />
                            <x-table.th title="Kredit" />
                        </x-slot>
                        <x-slot name="body">
                            @forelse ($this->jurnalSementara as $index => $jurnal)
                                @php
                                    $odd = $loop->iteration % 2 === 0 ? '255 255 255' : '247 247 247';
                                    $count = count($jurnal['detail']);
                                    $firstDetail = $jurnal['detail'][0];
                                @endphp

                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td rowspan="{{ $count }}">
                                        <x-button id="hapus-{{ $index }}" size="xs" title="Hapus" icon="fas fa-trash" variant="danger" outline hide-title wire:click.prevent="pop({{ $index }})" />
                                    </x-table.td>
                                    <x-table.td rowspan="{{ $count }}">
                                        {{ $jurnal['no_bukti'] }}
                                    </x-table.td>
                                    <x-table.td rowspan="{{ $count }}">
                                        {{ $jurnal['tgl_jurnal'] }}
                                    </x-table.td>
                                    <x-table.td rowspan="{{ $count }}">
                                        {{ $jurnal['jam_jurnal'] }}
                                    </x-table.td>
                                    <x-table.td rowspan="{{ $count }}">
                                        {{ $jurnal['jenis'] === 'U' ? 'UMUM' : 'PENYESUAIAN' }}
                                    </x-table.td>
                                    <x-table.td rowspan="{{ $count }}">
                                        {{ $jurnal['keterangan'] }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ $firstDetail['kd_rek'] }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ $this->rekening->get($firstDetail['kd_rek']) }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ rp($firstDetail['debet']) }}
                                    </x-table.td>
                                    <x-table.td>
                                        {{ rp($firstDetail['kredit']) }}
                                    </x-table.td>
                                </x-table.tr>
                                @foreach (collect($jurnal['detail'])->skip(1) ?? [] as $detail)
                                    <x-table.tr style="background-color: rgb({{ $odd }})">
                                        <x-table.td class="border-0">
                                            {{ $detail['kd_rek'] }}
                                        </x-table.td>
                                        <x-table.td class="border-0">
                                            {{ $this->rekening->get($detail['kd_rek']) }}
                                        </x-table.td>
                                        <x-table.td class="border-0">
                                            {{ rp($detail['debet']) }}
                                        </x-table.td>
                                        <x-table.td class="border-0">
                                            {{ rp($detail['kredit']) }}
                                        </x-table.td>
                                    </x-table.tr>
                                @endforeach
                            @empty
                                <x-table.tr-empty colspan="10" />
                            @endforelse
                        </x-slot>
                    </x-table>
                </x-row-col>
            </x-form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button size="sm" variant="success" title="Tambah Jurnal" icon="fas fa-plus" wire:click="push" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button
                size="sm"
                variant="primary"
                type="submit"
                class="ml-2"
                id="simpandata"
                title="Simpan"
                icon="fas fa-save"
                form="form-input-posting-jurnal"
                :disabled="empty($this->jurnalSementara)" />
        </x-slot>
    </x-modal>
</div>
