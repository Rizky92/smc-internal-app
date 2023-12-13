@extends('layouts.app')

@section('jadwal-dokter')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}">
@endpush

<header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
    <div class="container-fluid d-flex justify-content-center">
        <img src="{{ asset('img/logo.png') }}" alt="logo" width="120">
        <span>ANTREAN POLIKLINIK</span>
    </div>
</header>
@if ($jadwal->isNotEmpty()) 
    <table class="table">
        <thead class="thead bg-pandan text-white">
            <tr>
                <th width="30%">Nama Dokter</th>
                <th>Poliklinik</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Register</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $data)
                <tr>
                    <td style="text-align: left;">
                        <a href="{{ route('antrian-poli', ['kd_poli' => $data->kd_poli, 'kd_dokter' => $data->kd_dokter]) }}">
                            {{ $data->dokter->nm_dokter }}
                        </a>
                    </td>
                    <td>{{ $data->poliklinik->nm_poli }}</td>
                    <td>{{ $data->jam_mulai }}</td>
                    <td>{{ $data->jam_selesai }}</td>
                    <td>{{ $data->register }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada jadwal dokter untuk hari ini.</p>
@endif
@endsection