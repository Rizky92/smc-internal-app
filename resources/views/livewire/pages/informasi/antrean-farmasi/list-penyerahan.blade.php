@push('styles')
    <style>
        .marquee {
            width: 100%;
            overflow-y: hidden;
            height: calc(90vh);
        }
    </style>
@endpush

<div class="col-6 pt-2">
    <div class="card card-outline card-success">
        <div class="card-body">
            <div class="bg-success p-2 text-center">
                <h4 class="text-white font-weight-bold text-uppercase" style="font-size: 4vh">
                    {{ __('Selesai') }}
                </h4>
            </div>
            <div
                id="marquee-penyerahan"
                wire:key="marquee-penyerahan-{{ $this->dataPenyerahan->count() }}"
                class="marquee bg-white"
                @if ($this->dataPenyerahan->count() < 10) wire:poll.300s @endif
                data-row-count="{{ $this->dataPenyerahan->count() }}"
                data-direction="up"
                data-duration="30000"
                startVisible="true"
                data-gap="10"
                data-duplicated="false">
                <table class="table table-bordered table-striped" style="font-size: 1.5vh">
                    <tbody>
                        @forelse ($this->dataPenyerahan as $item)
                            <tr>
                                <td style="width: 60%">
                                    <span class="font-weight-bold">{{ $item->nm_pasien }}</span>
                                    (
                                    <span>{{ $item->nm_poli }}</span>
                                    )
                                    <br />
                                    <span>Dokter Peresep : {{ $item->nm_dokter }}</span>
                                </td>
                                <td style="width: 20%">
                                    {{ $item->is_racikan == '1' ? 'Racikan' : 'Non Racikan' }}
                                </td>
                                <td style="width: 20%">{{ $item->jam_validasi }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center p-4">Tidak ada yang dapat ditampilkan saat ini</td>
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
        function initMarqueePenyerahan() {
            let marqueePenyerahan = $('#marquee-penyerahan');
            let rowCount = parseInt(marqueePenyerahan.data('row-count'));

            if (rowCount > 10) {
                marqueePenyerahan.marquee('destroy');
                marqueePenyerahan.find('.js-marquee-wrapper').remove();
                marqueePenyerahan.marquee();
                marqueePenyerahan.off('finished').on('finished', function () {
                    $(this).marquee('destroy');
                    Livewire.dispatchTo('pages.informasi.antrean-farmasi.list-penyerahan', 'marqueePenyerahanFinished');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initMarqueePenyerahan);

        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'pages.informasi.antrean-farmasi.list-penyerahan') {
                initMarqueePenyerahan();
            }
        });
    </script>
@endpush
