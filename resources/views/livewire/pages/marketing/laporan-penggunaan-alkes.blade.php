<div>
    <x-flash />

    <x-card use-loading use-default-filter>
        <x-slot name="body">
            @php
                $tabs = [
                    'audiometri' => ['label' => 'Audiometri', 'prop' => 'dataPenggunaanAlkesAudiometri'],
                    'spirometri' => ['label' => 'Spirometri', 'prop' => 'dataPenggunaanAlkesSpirometri'],
                    'treadmill' => ['label' => 'Treadmill', 'prop' => 'dataPenggunaanAlkesTreadmill'],
                    'ekg' => ['label' => 'EKG', 'prop' => 'dataPenggunaanAlkesEKG'],
                    'eeg' => ['label' => 'EEG', 'prop' => 'dataPenggunaanAlkesEEG'],
                    'echo' => ['label' => 'Echo', 'prop' => 'dataPenggunaanAlkesEcho'],
                    'usg' => ['label' => 'USG', 'prop' => 'dataPenggunaanAlkesUSG'],
                    'thorax' => ['label' => 'Thorax', 'prop' => 'dataPenggunaanAlkesThorax'],
                    'ctscan' => ['label' => 'CT Scan', 'prop' => 'dataPenggunaanAlkesCTScan'],
                    'lumbal' => ['label' => 'Lumbal', 'prop' => 'dataPenggunaanAlkesLumbal'],
                    'panoramik' => ['label' => 'Panoramik', 'prop' => 'dataPenggunaanAlkesPanoramik'],
                    'mri' => ['label' => 'MRI', 'prop' => 'dataPenggunaanAlkesMRI'],
                ];
            @endphp

            <x-navtabs livewire :selected="array_key_first($tabs)">
                <x-slot name="tabs">
                    @foreach ($tabs as $key => $tab)
                        <x-navtabs.tab :id="$key" :title="$tab['label']" />
                    @endforeach
                </x-slot>
                <x-slot name="contents">
                    @foreach ($tabs as $key => $tab)
                        <x-navtabs.content :id="$key">
                            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                                <x-slot name="columns">
                                    <x-table.th name="no_rawat" title="No. Rawat" />
                                    <x-table.th name="no_rkm_medis" title="No. RM" />
                                    <x-table.th name="nm_pasien" title="Pasien" />
                                    <x-table.th name="nm_perawatan" title="Tindakan" />
                                    <x-table.th name="nama_nakes" title="Nakes" />
                                    <x-table.th name="tgl_periksa" title="Tgl Periksa" />
                                    <x-table.th name="unit" title="Unit / Kamar" />
                                    <x-table.th name="biaya" title="Biaya" align="right" />
                                    <x-table.th name="status" title="Status" />
                                </x-slot>
                                <x-slot name="body">
                                    @forelse ($this->{$tab['prop']} as $item)
                                        <x-table.tr>
                                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                                            <x-table.td>{{ $item->nm_perawatan }}</x-table.td>
                                            <x-table.td>{{ $item->nama_nakes }}</x-table.td>
                                            <x-table.td>{{ $item->tgl_periksa }} {{ $item->jam ?? '' }}</x-table.td>
                                            <x-table.td>{{ $item->unit }}</x-table.td>
                                            <x-table.td class="text-right">{{ rp($item->biaya) }}</x-table.td>
                                            <x-table.td>{{ $item->status }}</x-table.td>
                                        </x-table.tr>
                                    @empty
                                        <x-table.tr-empty colspan="9" padding />
                                    @endforelse
                                </x-slot>
                            </x-table>
                            <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->{$tab['prop']}" />
                        </x-navtabs.content>
                    @endforeach
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
