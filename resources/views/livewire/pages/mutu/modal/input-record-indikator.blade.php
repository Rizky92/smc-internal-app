<div>
    @push('js')
        <script>
            window.addEventListener('open-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('show');
            });

            window.addEventListener('close-modal', (e) => {
                $(`.modal#${e.detail.id}`).modal('hide');
            });

            $(document).on('record-saved', () => {
                $('#modal-input-record-indikator').modal('hide');
            });
        </script>
    @endpush

    <x-modal id="modal-input-record-indikator" title="Input Penilaian Harian: {{ $indicatorName }}" size="lg" livewire>
        <x-slot name="body" style="overflow-x: hidden">
            <x-flash class="mx-3 mt-3" />

            @if ($status === \App\Models\Quality\QualityIndicatorRecord::STATUS_SUBMITTED)
                <div class="alert alert-info mx-3 mt-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Data ini telah dikunci dan sedang menunggu validasi.
                </div>
            @elseif (in_array($status, \App\Models\Quality\QualityIndicatorRecord::STATUSES_DISETUJUI, true))
                <div class="alert alert-success mx-3 mt-3">
                    <i class="fas fa-check-circle mr-2"></i>
                    Data ini telah disetujui oleh validator.
                </div>
            @elseif ($status === \App\Models\Quality\QualityIndicatorRecord::STATUS_REJECTED)
                <div class="alert alert-danger mx-3 mt-3">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Data ini ditolak oleh validator. Silakan perbaiki dan kirim kembali.
                    @if ($alasanPenolakan)
                        <div class="mt-1">
                            <strong>Alasan:</strong>
                            {{ $alasanPenolakan }}
                        </div>
                    @endif
                </div>
            @elseif ($status === \App\Models\Quality\QualityIndicatorRecord::STATUS_VOIDED)
                <div class="alert alert-dark mx-3 mt-3">
                    <i class="fas fa-ban mr-2"></i>
                    Data ini telah dibatalkan (void) oleh validator dan tidak dihitung di laporan.
                </div>
            @endif

            <x-form id="form-input-record-indikator" wire:submit.prevent="save">
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input
                                type="date"
                                wire:model.defer="recordedDate"
                                class="form-control form-control-sm"
                                required
                                {{ $isEdit || $isDisabled ? 'disabled' : '' }} />
                            <x-form.error name="recordedDate" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numerator (Pembilang)</label>
                            <input
                                type="number"
                                wire:model.defer="numeratorValue"
                                class="form-control form-control-sm"
                                required
                                {{ $isDisabled ? 'disabled' : '' }} />
                            <x-form.error name="numeratorValue" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Denominator (Penyebut)</label>
                            <input
                                type="number"
                                wire:model.defer="denominatorValue"
                                class="form-control form-control-sm"
                                required
                                {{ $isDisabled ? 'disabled' : '' }} />
                            <x-form.error name="denominatorValue" />
                        </div>
                    </div>
                </x-row>
                <x-row>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea wire:model.defer="notes" class="form-control form-control-sm" rows="3" {{ $isDisabled ? 'disabled' : '' }}></textarea>
                            <x-form.error name="notes" />
                        </div>
                    </div>
                </x-row>
            </x-form>

            @if ($isEdit && in_array($status, \App\Models\Quality\QualityIndicatorRecord::STATUSES_DISETUJUI, true))
                <hr />
                <div class="px-3 pb-3">
                    <h6>Koreksi Data</h6>

                    @if ($koreksiPending && $pengajuanPending)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-hourglass-half mr-2"></i>
                            Pengajuan koreksi menunggu validasi: numerator {{ $pengajuanPending['numerator_value'] }}, denominator {{ $pengajuanPending['denominator_value'] }}.
                            <div class="mt-1">
                                <strong>Alasan:</strong>
                                {{ $pengajuanPending['reason'] }}
                            </div>
                        </div>
                    @else
                        @if ($alasanTolakKoreksi)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Pengajuan koreksi terakhir ditolak.
                                <strong>Alasan:</strong>
                                {{ $alasanTolakKoreksi }}
                            </div>
                        @endif

                        <p class="text-muted text-sm">Data yang sudah disetujui tidak dapat diubah langsung. Ajukan nilai yang benar; nilai lama tetap berlaku sampai validator menyetujui koreksi.</p>
                        <x-row>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Numerator Koreksi</label>
                                    <input type="number" wire:model.defer="koreksiNumerator" class="form-control form-control-sm" min="0" />
                                    <x-form.error name="koreksiNumerator" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Denominator Koreksi</label>
                                    <input type="number" wire:model.defer="koreksiDenominator" class="form-control form-control-sm" min="0" />
                                    <x-form.error name="koreksiDenominator" />
                                </div>
                            </div>
                        </x-row>
                        <div class="form-group">
                            <label>Catatan Koreksi</label>
                            <textarea wire:model.defer="koreksiNotes" class="form-control form-control-sm" rows="2"></textarea>
                            <x-form.error name="koreksiNotes" />
                        </div>
                        <div class="form-group">
                            <label>
                                Alasan Koreksi
                                <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model.defer="koreksiAlasan" class="form-control form-control-sm" rows="2"></textarea>
                            <x-form.error name="koreksiAlasan" />
                        </div>
                        <x-button variant="warning" size="sm" type="button" wire:click="ajukanKoreksi" wire:loading.attr="disabled" icon="fas fa-paper-plane" title="Ajukan Koreksi" />
                    @endif
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <div class="d-flex justify-content-between w-100">
                <div>
                    @if ($isEdit && $bisaDihapus)
                        <x-button
                            variant="danger"
                            title="Hapus"
                            icon="fas fa-trash"
                            onclick="confirm('Yakin ingin menghapus penilaian ini?') || event.stopImmediatePropagation()"
                            wire:click="delete" />
                    @endif
                </div>
                <div class="d-flex" style="gap: 0.5rem">
                    @if (! $isDisabled)
                        <x-button variant="success" type="button" wire:click="submit" wire:loading.attr="disabled" icon="fas fa-lock" title="Kunci & Kirim" />
                        <x-button variant="primary" form="form-input-record-indikator" type="submit" wire:loading.attr="disabled" icon="fas fa-save" title="Simpan Draft" />
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>
