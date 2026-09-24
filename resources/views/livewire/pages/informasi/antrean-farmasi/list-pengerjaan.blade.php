<section class="af-list">
    <h2 class="af-list-heading">{{ __('Sedang Dikerjakan') }}</h2>
    <div
        id="marquee-pengerjaan"
        wire:key="marquee-pengerjaan-{{ $this->dataPengerjaan->count() }}"
        class="marquee"
        data-direction="up"
        data-duration="30000"
        startVisible="true"
        data-gap="10"
        data-duplicated="false">
        @if ($this->dataPengerjaan->isEmpty())
            <p class="af-empty">{{ __('Belum ada resep yang sedang dikerjakan') }}</p>
        @else
            <ul class="af-rows">
                @foreach ($this->dataPengerjaan as $item)
                    <li class="af-row">
                        <span class="af-row-main">
                            <span class="af-row-name">
                                {{ $item->nm_pasien }}
                                <span class="af-row-poli">({{ trim($item->nm_poli) }})</span>
                            </span>
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
        registerAntreanFarmasiList('pengerjaan');
    </script>
@endpush
