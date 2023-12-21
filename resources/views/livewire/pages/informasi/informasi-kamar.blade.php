@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bed.css') }}">
@endpush

@section('informasi-kamar')
    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
        <div class="container-fluid d-flex justify-content-center">
            <img src="{{ asset('img/logo.png') }}" alt="logo" width="120" loading="lazy">
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

                                if (!in_array($comboKey, $processedCombos)) {
                                    $processedCombos[] = $comboKey;
                                    $occupiedRooms = $bangsal->countOccupiedRooms($kelas);
                                    $emptyRooms = $bangsal->countEmptyRooms($kelas);
                                    $showRow = $occupiedRooms > 0 || $emptyRooms > 0;
                                } else {
                                    $showRow = false;
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

        @push('js')
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
        @endpush
        
    @else
        <p>No data available</p>
    @endif
@endsection
