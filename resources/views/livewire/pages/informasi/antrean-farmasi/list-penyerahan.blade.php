<section class="af-list">
    <h2 class="af-list-heading">{{ __('Siap Diambil') }}</h2>
    <div
        id="marquee-penyerahan"
        wire:key="marquee-penyerahan-{{ $this->dataPenyerahan->count() }}"
        class="marquee"
        data-direction="up"
        data-duration="30000"
        startVisible="true"
        data-gap="10"
        data-duplicated="false">
        @if ($this->dataPenyerahan->isEmpty())
            <p class="af-empty">{{ __('Belum ada obat yang siap diambil') }}</p>
        @else
            <ul class="af-rows">
                @foreach ($this->dataPenyerahan as $item)
                    <li class="af-row">
                        <span class="af-row-main">
                            <span class="af-row-name">{{ $item->nm_pasien }}</span>
                            <span class="af-row-detail">{{ trim($item->nm_poli) }}</span>
                            <span class="af-row-detail">{{ __('Dokter Peresep: :dokter', ['dokter' => $item->nm_dokter]) }}</span>
                        </span>

                        @if ($item->is_racikan == '1')
                            <span class="af-tag is-racikan">{{ __('Racikan') }}</span>
                        @else
                            <span class="af-tag">{{ __('Non racikan') }}</span>
                        @endif
                        <span class="af-row-time">{{ substr($item->jam_validasi, 0, 5) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

@push('js')
    <script>
        registerAntreanFarmasiList('penyerahan');
    </script>
@endpush
