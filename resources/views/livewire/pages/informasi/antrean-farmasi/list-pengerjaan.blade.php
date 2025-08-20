@push('styles')
    <style>
        .marquee {
            width: 100%;
            overflow-y: hidden;
            height: calc(90vh);
        }
    </style>
@endpush

<div class="col-6 pt-2" style="font-size: 1.5vh">
    <div class="card card-outline card-success">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="bg-success">
                    <tr>
                        <th style="width: 60%">Pasien</th>
                        <th style="width: 20%">Jenis Resep</th>
                        <th style="width: 20%">Mulai Pengerjaan</th>
                    </tr>
                </thead>
            </table>
            <div
                id="marquee-pengerjaan"
                wire:key="marquee-pengerjaan-{{ $this->dataPengerjaan->count() }}"
                class="marquee bg-white"
                @if ($this->dataPengerjaan->count() < 20) wire:poll.300s @endif
                data-row-count="{{ $this->dataPengerjaan->count() }}"
                data-direction="up"
                data-duration="20000"
                startVisible="true"
                data-gap="10"
                data-duplicated="false"
            >
                <table class="table table-bordered table-striped">
                    <tbody>
                        @forelse ($this->dataPengerjaan as $item)
                            <tr>
                                <td style="width: 60%">
                                    <span class="font-weight-bold">{{ $item->nm_pasien }}</span>
                                    (<span>{{ $item->nm_poli }}</span>) <br>
                                    <span>Dokter Peresep : {{ $item->nm_dokter }}</span>
                                </td>
                                <td style="width: 20%">
                                    {{ $item->is_racikan == '1' ? 'Racikan' : 'Non Racikan' }}
                                </td>
                                <td style="width: 20%">{{ $item->jam_validasi }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center p-4">
                                    Tidak ada yang dapat ditampilkan saat ini
                                </td>
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
        function initMarqueePengerjaan() {
            let marqueePengerjaan = $('#marquee-pengerjaan');
            let rowCount = parseInt(marqueePengerjaan.data('row-count'));

            if (rowCount > 20) {
                if (marqueePengerjaan.hasClass('js-marquee')) {
                    marqueePengerjaan.marquee('destroy');
                }

                marqueePengerjaan.marquee();

                marqueePengerjaan.off('finished').on('finished', function() {
                    $(this).marquee('destroy');
                    Livewire.emit('marqueePengerjaanFinished');
                });
            } else {
                if (marqueePengerjaan.hasClass('js-marquee')) {
                    marqueePengerjaan.marquee('destroy');
                }
            }
        }

        document.addEventListener("DOMContentLoaded", initMarqueePengerjaan);

        Livewire.hook('message.processed', (message, component) => {
            initMarqueePengerjaan();
        });
    </script>
@endpush
