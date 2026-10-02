<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <h5 class="mb-0">Elemen Penilaian - {{ $standard->kode ?? '' }} {{ $standard->judul ?? '' }}</h5>
            <x-row-col-flex class="mt-2">
                <a href="{{ route('admin.akreditasi.standard-by-focus-area', $standard->focus_area_id ?? 0) }}" class="btn btn-secondary btn-sm">&laquo; Kembali ke Standar</a>
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Deskripsi EP</th>
                            <th class="text-center">Metode Pembuktian</th>
                            <th class="text-center">Jml Dokumen</th>
                            <th class="text-center">Skor</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($elements as $i => $ep)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ep->kode }}</td>
                                <td>{{ $ep->deskripsi }}</td>
                                <td class="text-center">
                                    @if ($ep->proofMethodKode)
                                        <span class="badge badge-info">{{ $ep->proofMethodKode }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $ep->totalDokumen }}</td>
                                <td class="text-center">
                                    @if ($ep->skorStatus)
                                        <span class="badge {{ $ep->skorNilai === 10 ? 'badge-success' : ($ep->skorNilai === 5 ? 'badge-warning' : 'badge-danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $ep->skorStatus)) }} ({{ $ep->skorNilai }})
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Belum</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.akreditasi.ep-detail', $ep->id) }}" class="btn btn-primary btn-sm">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data Elemen Penilaian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
</div>
