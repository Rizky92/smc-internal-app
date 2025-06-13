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
        <h1>Jadwal Dokter</h1>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm text-sm text-nowrap">
            <thead>
                <tr>
                    <th style="width: 30%">Nama Dokter</th>
                    <th style="width: 10%">SENIN</th>
                    <th style="width: 10%">SELASA</th>
                    <th style="width: 10%">RABU</th>
                    <th style="width: 10%">KAMIS</th>
                    <th style="width: 10%">JUMAT</th>
                    <th style="width: 10%">SABTU</th>
                    <th style="width: 10%">MINGGU</th>
                </tr>
            </thead>
        </table>
        <div class="table-responsive marquee" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false">
            <table class="table table-sm text-sm text-nowrap">
                <tbody>
                    @foreach ($collection as $poli => $jadwals)
                        <tr>
                            <td colspan="8" class="bg-green"><strong>{{ $poli }}</strong></td>
                        </tr>
                        @foreach ($jadwals as $dokterId => $dokterJadwal) {{-- Sudah berbentuk array --}}
                            <tr>
                                <td style="width: 30%">{{ $dokterJadwal[0]['dokter']['nm_dokter'] }}</td> {{-- Ambil nama dokter dari array --}}
                                @foreach (['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'] as $day)
                                    <td style="width: 10%">
                                        @php
                                            // Mapping hari ke angka (Carbon: Monday = 1, Sunday = 7)
                                            $dayMap = [
                                                'SENIN' => 1,
                                                'SELASA' => 2,
                                                'RABU' => 3,
                                                'KAMIS' => 4,
                                                'JUMAT' => 5,
                                                'SABTU' => 6,
                                                'MINGGU' => 7,
                                            ];

                                            // Dapatkan tanggal sebenarnya dari hari ini ke hari target dalam minggu ini
                                            $targetDate = \Carbon\Carbon::now()->startOfWeek()->addDays($dayMap[$day] - 1)->toDateString();

                                            // Ambil data cuti aktif dari dokter
                                            $dokter = $dokterJadwal[0]['dokter'];
                                            $cuti = $dokter['cuti_aktif'] ?? null;

                                            $isCuti = $cuti &&
                                                $cuti['tanggal_awal'] <= $targetDate &&
                                                $cuti['tanggal_akhir'] >= $targetDate;

                                            $hariJadwal = array_filter($dokterJadwal, fn($j) => strtoupper($j['hari_kerja']) === $day);
                                        @endphp
                                        @if ($isCuti)
                                            <h5><span class="badge bg-danger">CUTI</span></h5>
                                        @elseif (!empty($hariJadwal))
                                            {!! implode('<br>', array_map(fn($j) => date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai'])), $hariJadwal)) !!}
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