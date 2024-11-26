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
    <table width="100%" class="table table-sm text-sm table-bordered table-striped">
        <thead class="bg-success">
            <tr>
                <th style="width: 12ch;">No Reg</th>
                <th style="width: 44ch;">Nama Dokter</th>
                <th style="width: 44ch;">Nama Pasien</th>
            </tr>
        </thead>
    </table>
    <div class="marquee bg-white" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false">
        <table class="table table-sm text-sm table-bordered table-striped">
            <tbody>
                @forelse ($this->antreanPerPintu as $item)
                    <tr>
                        <td style="width: 12ch;">{{ $item->no_reg }}</td>
                        <td style="width: 44ch;">{{ $item->nm_dokter }}</td>
                        <td style="width: 44ch;">{{ $item->nm_pasien }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted p-4">
                            Tidak ada yang dapat ditampilkan saat ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@push('js')
    <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let marquee = $('.marquee');

            function initializeMarquee() {
                marquee.marquee();
                marquee.off('finished').on('finished', function () {
                    Livewire.emit('updateAntrean');
                    console.log('finished');
                });
            }

            initializeMarquee();    

            window.addEventListener('updateMarqueeData', function () {
                console.log('updateMarqueeData run');
                marquee.marquee('destroy');
                initializeMarquee();
            });
        });
    </script>
@endpush