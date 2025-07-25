@push('styles')
    <style>
        .marquee {
            width: 100%;
            overflow-y: scroll;
            overflow-y: hidden;
            height: calc(80vh);
        }
    </style>
@endpush

<div class="card">
    <div class="card-header text-center">
        <h1 style="font-size: 5vh">Jadwal Dokter</h1>
    </div>
    <div class="card-body p-0">
        <table class="table text-nowrap mb-0" style="font-size: 2.5vh">
            <thead>
                <tr>
                    <th style="width: 34%">Nama Dokter</th>
                    <th class="text-center" style="width: 11%">SENIN</th>
                    <th class="text-center" style="width: 11%">SELASA</th>
                    <th class="text-center" style="width: 11%">RABU</th>
                    <th class="text-center" style="width: 11%">KAMIS</th>
                    <th class="text-center" style="width: 11%">JUMAT</th>
                    <th class="text-center" style="width: 11%">SABTU</th>
                </tr>
            </thead>
        </table>
        <div class="table-responsive marquee" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false"  style="height: calc(100vh - 100px);">
            <table class="table text-nowrap" style="font-size: 2.5vh">
                <tbody>
                    @foreach ($collection as $poli => $jadwals)
                        <tr>
                            <td colspan="8" class="bg-green"><strong>{{ strtoupper($poli) }}</strong></td>
                        </tr>
                        @foreach ($jadwals as $dokterId => $dokterJadwal)
                            {{-- Sudah berbentuk array --}}
                            <tr>
                                <td style="width: 34%">{{ $dokterJadwal[0]['dokter']['nm_dokter'] }}</td>
                                {{-- Ambil nama dokter dari array --}}
                                @foreach (['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'] as $day)
                                    <td class="text-center" style="width: 11%">
                                        @php
                                            $hariJadwal = array_filter($dokterJadwal, fn ($j) => strtoupper($j['hari_kerja']) === $day);
                                        @endphp

                                        @if (! empty($hariJadwal))
                                            {!! implode('<br>', array_map(fn ($j) => date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai'])), $hariJadwal)) !!}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@push('js')
    <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
    <script>
        $('.marquee').marquee();
    </script>
@endpush
