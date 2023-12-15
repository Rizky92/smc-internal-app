@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bed.css') }}">
@endpush

@section('informasi-kamar')
    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
        <div class="container-fluid d-flex justify-content-center">
            <img src="img/logo.png" alt="logo" style="max-width: 120px; height: auto;">
            <span>KETERSEDIAAN KAMAR</span>
        </div>
    </header>

    @if ($informasiKamar->count() > 0)
        <table class="table table-bordered table-striped text-white">   
            <thead>
                <tr>
                    <th width="30%">Bangsal</th>
                    <th width="20%">Kelas</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
        <div id="scrollingContent">
            <table class="table table-bordered">
                <div class="padding"></div>
                <tbody>
                    @php
                        $processedCombos = [];
                    @endphp
    
                    @foreach ($informasiKamar as $bangsal)
                        @foreach ($kelasList as $kelas)
                            @php
                                $comboKey = $bangsal->kd_bangsal . '_' . $kelas;
                                $occupiedRooms = $bangsal->countOccupiedRooms($kelas);
                                $emptyRooms = $bangsal->countEmptyRooms($kelas);
                                $showRow = $occupiedRooms > 0 || $emptyRooms > 0;
    
                                if (!in_array($comboKey, $processedCombos) && $showRow) {
                                    array_push($processedCombos, $comboKey);
                                } else {
                                    continue;
                                }
                            @endphp
    
                            @if ($showRow)
                                <tr>
                                    <td width="30%">{{ $bangsal->nm_bangsal }}</td>
                                    <td width="20%">{{ $kelas }}</td>
                                    <td>
                                        Terisi: {{ $occupiedRooms }} | Tersedia: {{ $emptyRooms }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <script>
            function refreshPage() {
                setTimeout(function () {
                    location.reload(true);
                }, 36000);
            }
            document.addEventListener('DOMContentLoaded', function () {
                refreshPage();
            });
        </script>
    @else
        <p>No data available</p>
    @endif
@endsection
