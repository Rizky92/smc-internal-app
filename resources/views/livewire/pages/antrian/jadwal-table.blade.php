@if ($jadwal->isNotEmpty()) 
    <table>
        <thead>
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
                    <td>{{ $data->poliklinik->nm_poli}}</td>
                    <td> {{ $data->jam_mulai }}</td>
                    <td> {{ $data->jam_selesai }}</td>
                    <td>{{ \App\Models\Perawatan\RegistrasiPasien::hitungData($data->kd_poli, $data->kd_dokter, now()->format('Y-m-d')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada jadwal dokter untuk hari ini.</p>
@endif