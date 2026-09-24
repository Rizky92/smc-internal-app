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
    <script>
        registerAntreanFarmasiList('penyerahan');
    </script>
@endpush
