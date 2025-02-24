<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-refresh class="ml-3" />
                <x-filter.select2 class="ml-3" name="Kode Penjamin" livewire show-key :options="$this->penjamin" placeholder="SEMUA" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kd_rek" title="Kode Akun" />
                    <x-table.th name="nm_rek" title="Nama Akun" />
                    <x-table.th name="debet" title="Debet" />
                    <x-table.th name="kredit" title="Kredit" />
                    <x-table.th name="total" title="Laba/Rugi" />
                </x-slot>
                <x-slot name="body">
                    <x-table.tr>
                        <x-table.td></x-table.td>
                        <x-table.td class="font-weight-bold">PENDAPATAN</x-table.td>
                        <x-table.td colspan="3"></x-table.td>
                    </x-table.tr>
                    @forelse ($this->labaRugiPerRekening->get('K') as $rekening)
                        <x-table.tr>
                            <x-table.td>
                                {{ $rekening->kd_rek }}
                            </x-table.td>
                            <x-table.td>
                                {{ $rekening->nm_rek }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->debet) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->kredit) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->total) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                    <x-table.tr>
                        <x-table.td></x-table.td>
                        <x-table.td class="font-weight-bold">TOTAL PENDAPATAN</x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalDebetPendapatan']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalKreditPendapatan']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalPendapatan']) }}
                        </x-table.td>
                    </x-table.tr>
                    <x-table.tr>
                        <x-table.td colspan="5">&nbsp;</x-table.td>
                    </x-table.tr>

                    <x-table.tr>
                        <x-table.td></x-table.td>
                        <x-table.td class="font-weight-bold">BEBAN & BIAYA</x-table.td>
                        <x-table.td colspan="3"></x-table.td>
                    </x-table.tr>
                    @forelse ($this->labaRugiPerRekening->get('D') as $rekening)
                        <x-table.tr>
                            <x-table.td>
                                {{ $rekening->kd_rek }}
                            </x-table.td>
                            <x-table.td>
                                {{ $rekening->nm_rek }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->debet) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->kredit) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($rekening->total) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="5" padding />
                    @endforelse
                    <x-table.tr>
                        <x-table.td></x-table.td>
                        <x-table.td class="font-weight-bold">TOTAL BEBAN & BIAYA</x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalDebetBeban']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalKreditBeban']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalBebanDanBiaya']) }}
                        </x-table.td>
                    </x-table.tr>
                    <x-table.tr>
                        <x-table.td colspan="5">&nbsp;</x-table.td>
                    </x-table.tr>

                    <x-table.tr>
                        <x-table.td></x-table.td>
                        <x-table.td class="font-weight-bold">PENDAPATAN BERSIH</x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalPendapatan']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['totalBebanDanBiaya']) }}
                        </x-table.td>
                        <x-table.td class="font-weight-bold">
                            {{ rp($this->totalLabaRugiPerRekening['labaRugi']) }}
                        </x-table.td>
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
