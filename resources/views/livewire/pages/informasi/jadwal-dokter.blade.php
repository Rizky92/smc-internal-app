<header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
    <div class="container-fluid d-flex justify-content-center">
        <img src="{{ asset('img/logo.png') }}" alt="logo" width="120">
        <span>RS SAMARINDA MEDIKA CITRA</span>
    </div>
</header>
@if ($jadwal->isNotEmpty()) 
<div id="scrollingContent">
    <table class="table">
        <thead class="thead bg-pandan text-white">
            <tr>
                <th>Nama Dokter</th>
                <th>Poli Klinik</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Register</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $data)
                <tr>
                    <td>
                        <a href="{{ route('antrian.show', ['kd_poli' => $data->kd_poli, 'kd_dokter' => $data->kd_dokter]) }}">
                            {{ $data->dokter->nm_dokter }}
                        </a>
                    </td>
                    <td>{{ $data->poliklinik->nm_poli }}</td>
                    <td>{{ $data->jam_mulai }}</td>
                    <td>{{ $data->jam_selesai }}</td>
                    <td>{{ \App\Models\Perawatan\RegistrasiPasien::hitungData($data->kd_poli, $data->kd_dokter, now()->format('Y-m-d')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada jadwal dokter untuk hari ini.</p>
@endif

{{-- <div>
    <x-flash />
    <x-slot name="columns">
        <x-table.th name="kd_dokter" title="Nama Dokter" />
        <x-table.th name="hari_kerja" title="Hari Kerja" />
        <x-table.th name="jam_mulai" title="Jam Mulai" />
        <x-table.th name="jam_selesai" title="Jam Selesai" />
        <x-table.th name="nm_poli" title="Poliklinik" />
    </x-slot>
    <x-slot name="body">
        @forelse ($this->dataJadwalDokter as $item)
        <x-table.tr>
            <x-table.td>{{ $item->nm_dokter }}</x-table.td>
            <x-table.td>{{ $item->hari_kerja }}</x-table.td>
            <x-table.td>{{ $item->jam_mulai }}</x-table.td>
            <x-table.td>{{ $item->jam_selesai }}</x-table.td>
            <x-table.td>{{ $item->nm_poli }}</x-table.td>
        </x-table.tr>
        @empty
            <x-table.tr-empty colspan="1" padding />
        @endforelse
    </x-slot>
</div> --}}
