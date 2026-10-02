<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <h5 class="mb-0">Standar - {{ $focusArea->kode ?? '' }} {{ $focusArea->nama ?? '' }}</h5>
            <x-row-col-flex class="mt-2">
                <a href="{{ route('admin.akreditasi.dashboard') }}" class="btn btn-secondary btn-sm">&laquo; Kembali ke Fokus Area</a>
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Judul Standar</th>
                            <th>Maksud &amp; Tujuan</th>
                            <th class="text-center">Total EP</th>
                            <th class="text-center">EP Terisi</th>
                            <th class="text-center">Dokumen</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($standards as $i => $std)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $std->kode }}</td>
                                <td>{{ $std->judul }}</td>
                                <td>
                                    @if ($std->maksudTujuan)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-link p-0"
                                            data-toggle="popover"
                                            data-trigger="focus"
                                            title="Maksud &amp; Tujuan"
                                            data-content="{{ $std->maksudTujuan }}">
                                            Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $std->totalEp }}</td>
                                <td class="text-center">{{ $std->epTerisi }}</td>
                                <td class="text-center">{{ $std->totalDokumen }}</td>
                                <td class="text-center">
                                    @if ($std->progressPersen >= 100)
                                        <span class="badge badge-success">Selesai</span>
                                    @elseif ($std->progressPersen > 0)
                                        <span class="badge badge-warning">{{ $std->progressPersen }}%</span>
                                    @else
                                        <span class="badge badge-secondary">Belum</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.akreditasi.element-by-standard', $std->id) }}" class="btn btn-primary btn-sm">Buka Elemen</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data Standar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
</div>
