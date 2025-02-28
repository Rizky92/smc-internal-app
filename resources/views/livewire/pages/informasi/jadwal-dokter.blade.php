<div wire:init="loadProperties">
    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.toggle class="ml-3" model="semuaPoli" title="Tampilkan Semua Poli" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nm_dokter" title="Nama Dokter" />
                    <x-table.th name="nm_poli" title="Poliklinik" />
                    <x-table.th name="jam_mulai" title="Jam Mulai" />
                    <x-table.th name="jam_selesai" title="Jam Selesai" />
                    <x-table.th name="register" title="Register" />
                    <x-table.th name="kuota" title="Kuota" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataJadwalDokter as $item)
                        <x-table.tr>
                            <x-table.td>
                                <a href="{{ route('admin.antrian-poli', ['kd_poli' => $item->kd_poli, 'kd_dokter' => $item->kd_dokter]) }}" target="_blank" class="text-decoration-none text-black">
                                    {{ $item->nm_dokter }}
                                </a>
                            </x-table.td>
                            <x-table.td>{{ $item->nm_poli }}</x-table.td>
                            <x-table.td>{{ $item->jam_mulai }}</x-table.td>
                            <x-table.td>
                                {{ $item->jam_selesai }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->total_registrasi }}
                            </x-table.td>
                            <x-table.td>{{ $item->kuota }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
