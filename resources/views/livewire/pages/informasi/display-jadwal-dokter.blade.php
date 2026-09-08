{{--
    Rendered into the layout's {{ $slot }}, not into
    @yield('display-jadwal-dokter').
    
    Livewire 3 requires a component's own output to have exactly one root HTML
    element. Wrapping the body in @section buffers it instead of echoing it, so
    Livewire saw an empty render and threw RootTagMissingFromViewException. The
    matching @yield in layouts/app.blade.php is now dead for this page.
--}}
<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}" />
    @endpush

    <header class="d-flex flex-wrap justify-content-center mb-4 border-bottom shadow">
        <div class="container-fluid d-flex justify-content-center">
            <img src="{{ asset('img/logo.png') }}" alt="logo" width="100px" height="80px" />
            <h1 class="text-success head">JADWAL DOKTER HARI INI</h1>
        </div>
    </header>

    <table class="table table-bordered">
        <thead class="bg-success">
            <tr>
                <th width="34%">Nama Dokter</th>
                <th width="26%">Poliklinik</th>
                <th width="10%" style="text-align: center">Jam Mulai</th>
                <th width="10%" style="text-align: center">Jam Selesai</th>
                <th width="20%">Total Register & Kuota</th>
            </tr>
        </thead>
    </table>
    <div id="scrollingContent">
        <table class="table table-bordered">
            <div class="padding"></div>
            <tbody>
                @forelse ($this->dataJadwalDokter as $item)
                    <tr class="@if($item->total_registrasi >= $item->kuota) bg-danger @endif">
                        <td width="34%">{{ $item->nm_dokter }}</td>
                        <td width="26%">{{ $item->nm_poli }}</td>
                        <td width="10%" style="text-align: center">
                            {{ $item->jam_mulai }}
                        </td>
                        <td width="10%" style="text-align: center">
                            {{ $item->jam_selesai }}
                        </td>
                        <td width="20%">
                            @if ($item->total_registrasi >= $item->kuota)
                                Register :
                                <b class="text-white">
                                    {{ $item->total_registrasi }}
                                </b>
                                | Kuota :
                                <b class="text-white">{{ $item->kuota }}</b>
                            @else
                                Register :
                                <b class="text-danger">
                                    {{ $item->total_registrasi }}
                                </b>
                                | Kuota :
                                <b class="text-danger">{{ $item->kuota }}</b>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function refreshPage() {
            setTimeout(function () {
                location.reload(true);
            }, 80000);
        }
        document.addEventListener('DOMContentLoaded', function () {
            refreshPage();
        });
    </script>
</div>
