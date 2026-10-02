<div wire:init="loadProperties">
    <x-flash />

    <x-card>
        <x-slot name="header">
            <h5 class="mb-0">Data Seluruh Fokus Area</h5>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.search class="ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Fokus Area / Bab</th>
                            <th class="text-center">Total EP</th>
                            <th class="text-center">EP Diisi</th>
                            <th class="text-center">EP Belum</th>
                            <th class="text-center">Dokumen</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($focusAreas as $i => $fa)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $fa->kode }}</strong>
                                    <br />
                                    <small class="text-muted">{{ $fa->nama }}</small>
                                </td>
                                <td class="text-center">{{ $fa->totalEp }}</td>
                                <td class="text-center">{{ $fa->epTerisi }}</td>
                                <td class="text-center">{{ $fa->epBelumDiisi }}</td>
                                <td class="text-center">{{ $fa->totalDokumen }}</td>
                                <td class="text-center" style="min-width: 120px">
                                    <div class="progress" style="height: 20px">
                                        <div
                                            class="progress-bar {{ $fa->progressPersen >= 80 ? 'bg-success' : ($fa->progressPersen >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                            role="progressbar"
                                            style="width: {{ $fa->progressPersen }}%"
                                            aria-valuenow="{{ $fa->progressPersen }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $fa->progressPersen }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.akreditasi.standard-by-focus-area', $fa->id) }}" class="btn btn-primary btn-sm">Buka</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data Fokus Area.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
</div>
