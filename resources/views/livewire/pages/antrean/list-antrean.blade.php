@push('styles')
    <style>
        .marquee {
            width: 100%;
            overflow-y: scroll;
            overflow-y: hidden;
            height: calc(75vh);
        }
    </style>
@endpush

<div class="col">
    <div class="card card-outline card-success">
        <div class="card-header d-flex justify-content-center">
            <h5 class="text-uppercase">
                Antrean
                {{ \App\Models\Aplikasi\Pintu::where('kd_pintu', $this->kd_pintu)->first()->nm_pintu }}
            </h5>
        </div>
        <div class="card-body">
            <table width="100%" class="table table-sm text-sm table-bordered table-striped">
                <thead class="bg-success">
                    <tr>
                        <th style="width: 12ch">No Reg</th>
                        <th>Nama Pasien</th>
                    </tr>
                </thead>
            </table>
            <div class="marquee bg-white" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false">
                <table class="table table-sm text-sm table-bordered table-striped">
                    <tbody>
                        @forelse ($this->antreanPerPintu as $group)
                            <tr class="bg-success text-white">
                                <td colspan="2" class="fw-bold">{{ $group['header']['nm_dokter'] }} — {{ $group['header']['jam_mulai'] }} - {{ $group['header']['jam_selesai'] }}</td>
                            </tr>
                            @foreach ($group['items'] as $item)
                                <tr>
                                    <td style="width: 12ch">
                                        {{ $item->no_reg }}
                                    </td>
                                    <td>
                                        {{ $item->nm_pasien }}
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted p-4">Tidak ada yang dapat ditampilkan saat ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let rowCount = @json($this->totalRow);
            let refreshInterval;

            function initializeMarquee() {
                let marquee = $('.marquee');

                if (rowCount > 10) {
                    marquee.marquee();
                    marquee.off('finished').on('finished', function () {
                        Livewire.emit('updateAntrean');
                    });

                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                        refreshInterval = null;
                    }
                } else {
                    marquee.marquee('destroy');

                    if (!refreshInterval) {
                        refreshInterval = setInterval(function () {
                            Livewire.emit('updateAntrean');
                        }, 5000);
                    }
                }
            }

            initializeMarquee();

            window.addEventListener('updateMarqueeData', function (event) {
                rowCount = event.detail.rowCount || 0;
                initializeMarquee();
            });
        });
    </script>
@endpush
