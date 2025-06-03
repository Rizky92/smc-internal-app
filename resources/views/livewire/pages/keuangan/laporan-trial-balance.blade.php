<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-button class="ml-auto" size="sm" variant="danger" title="Rekalkulasi ulang" icon="fas fa-sync-alt" wire:click.prevent="resetCache" outline />
                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kd_rek" title="Kode Akun" />
                    <x-table.th name="nm_rek" title="Nama" />
                    <x-table.th name="balance" title="Balance" />
                    <x-table.th title="Saldo Awal" />
                    <x-table.th title="Debet" />
                    <x-table.th title="Kredit" />
                    <x-table.th title="Saldo Akhir" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataTrialBalancePerTanggal as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->kd_rek }}</x-table.td>
                            <x-table.td>{{ $item->nm_rek }}</x-table.td>
                            <x-table.td>{{ $item->balance }}</x-table.td>
                            <x-table.td>
                                {{ rp($item->saldo_awal) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($item->total_debet) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($item->total_kredit) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($item->saldo_akhir) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="7" padding />
                    @endforelse
                    @if ($this->dataTrialBalancePerTanggal)
                        <x-table.tr>
                            <x-table.td></x-table.td>
                            <x-table.td class="font-weight-bold" colspan="3">TOTAL :</x-table.td>
                            <x-table.td class="font-weight-bold">
                                {{ rp($this->totalDebetKreditTrialBalance->total_debet) }}
                            </x-table.td>
                            <x-table.td class="font-weight-bold">
                                {{ rp($this->totalDebetKreditTrialBalance->total_kredit) }}
                            </x-table.td>
                            <x-table.td></x-table.td>
                        </x-table.tr>
                    @endif
                </x-slot>
            </x-table>
        </x-slot>
        {{--
            <x-slot name="footer">
            <x-paginator :data="$this->collectionProperty" />
            </x-slot>
        --}}
    </x-card>
</div>
