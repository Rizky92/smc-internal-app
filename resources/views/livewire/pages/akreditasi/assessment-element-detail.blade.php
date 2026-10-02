<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <h5 class="mb-0">Detail Elemen Penilaian - {{ $detail->kode ?? '' }}</h5>
            <x-row-col-flex class="mt-2">
                <a href="{{ route('admin.akreditasi.element-by-standard', $detail->standardId ?? 0) }}" class="btn btn-secondary btn-sm">&laquo; Kembali ke Daftar EP</a>
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            @if ($detail)
                {{-- Info Kontekstual --}}
                <div class="mb-4">
                    <h6 class="font-weight-bold">Informasi Standar &amp; EP</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold" style="width: 180px">Fokus Area</td>
                            <td>{{ $detail->focusAreaKode }} - {{ $detail->focusAreaNama }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Standar</td>
                            <td>{{ $detail->standardKode }} - {{ $detail->standardJudul }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Kode EP</td>
                            <td>{{ $detail->kode }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Deskripsi EP</td>
                            <td>{{ $detail->deskripsi }}</td>
                        </tr>
                        @if ($detail->standardMaksudTujuan)
                            <tr>
                                <td class="font-weight-bold">Maksud &amp; Tujuan</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#maksudTujuanModal">Lihat Maksud &amp; Tujuan</button>
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="font-weight-bold">Skor Saat Ini</td>
                            <td>
                                @if ($detail->skorStatus)
                                    <span class="badge {{ $detail->skorNilai === 10 ? 'badge-success' : ($detail->skorNilai === 5 ? 'badge-warning' : 'badge-danger') }}" style="font-size: 1rem">
                                        {{ ucfirst(str_replace('_', ' ', $detail->skorStatus)) }} ({{ $detail->skorNilai }})
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Belum dinilai</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Panduan Pencarian Bukti --}}
                @if ($detail->penjelasanKelengkapanBukti || $detail->proofMethodKode)
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Panduan Pencarian Bukti</h6>
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Penjelasan Kelengkapan Bukti</th>
                                    <th class="text-center" style="width: 120px">Metode Pembuktian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $detail->penjelasanKelengkapanBukti ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($detail->proofMethodKode)
                                            <span class="badge badge-info" style="font-size: 0.9rem">{{ $detail->proofMethodKode }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Upload Dokumen --}}
                <div class="mb-4">
                    <h6 class="font-weight-bold">Upload Dokumen Bukti Baru</h6>
                    <form wire:submit.prevent="upload">
                        <div class="form-row">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" placeholder="Judul Dokumen" wire:model.defer="judulDokumen" />
                                @error('judulDokumen')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" class="form-control" placeholder="Keterangan (opsional)" wire:model.defer="keterangan" />
                                @error('keterangan')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="file" class="form-control-file" wire:model.defer="file" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" />
                                @error('file')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-success btn-block" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Upload</span>
                                    <span wire:loading>Uploading...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Daftar File Tersimpan --}}
                <div>
                    <h6 class="font-weight-bold">Daftar File Tersimpan</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Dokumen</th>
                                    <th>Keterangan</th>
                                    <th>Tipe File</th>
                                    <th>Ukuran</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($detail->documents as $i => $doc)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $doc->judul_dokumen }}</td>
                                        <td>{{ $doc->keterangan ?? '-' }}</td>
                                        <td>{{ $doc->mime_type }}</td>
                                        <td>{{ round($doc->file_size / 1024, 1) }} KB</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info" wire:click="editDocument({{ $doc->id }})" title="Edit Keterangan">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $doc->id }})" title="Hapus">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Belum ada dokumen terupload.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">Data Elemen Penilaian tidak ditemukan.</div>
            @endif
        </x-slot>
    </x-card>

    {{-- Modal Maksud & Tujuan --}}
    @if ($detail && $detail->standardMaksudTujuan)
        <div class="modal fade" id="maksudTujuanModal" tabindex="-1" role="dialog" aria-labelledby="maksudTujuanModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="maksudTujuanModalLabel">Maksud &amp; Tujuan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>{{ $detail->standardKode }} - {{ $detail->standardJudul }}</strong></p>
                        <p>{{ $detail->standardMaksudTujuan }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Edit Keterangan --}}
    @if ($editDocumentId)
        <div class="modal fade show" id="editModal" tabindex="-1" role="dialog" style="display: block; background: rgba(0, 0, 0, 0.5)">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Keterangan Dokumen</h5>
                        <button type="button" class="close" wire:click="resetEditForm" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="updateDocument">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Judul Dokumen</label>
                                <input type="text" class="form-control" wire:model.defer="editJudulDokumen" />
                                @error('editJudulDokumen')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" wire:model.defer="editKeterangan" rows="2"></textarea>
                                @error('editKeterangan')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="resetEditForm">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Hapus --}}
    @if ($deleteDocumentId)
        <div class="modal fade show" id="deleteModal" tabindex="-1" role="dialog" style="display: block; background: rgba(0, 0, 0, 0.5)">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="close" wire:click="cancelDelete" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus dokumen ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Batal</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteDocument">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('css')
        <style>
            .modal.fade.show {
                display: block;
                background: rgba(0, 0, 0, 0.5);
            }
        </style>
    @endpush
</div>
