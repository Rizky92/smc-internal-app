<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.label constant-width>Tahun:</x-filter.label>
                <div class="input-group input-group-sm ml-2" style="width: 5rem">
                    <x-filter.select model="tahun" :options="$this->dataTahun" constant-width />
                </div>
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table style="min-width: 100%">
                <x-slot name="columns">
                    {{-- <x-table.th name="id" title="#" /> --}}
                    <x-table.th name="thn" title="Tahun" />
                    <x-table.th name="kd_rek" title="Kode Akun" />
                    <x-table.th name="nm_rek" title="Nama AKun" />
                    <x-table.th name="tipe" title="Tipe" />
                    <x-table.th name="saldo_awal" title="Saldo Awal" />
                    <x-table.th name="debet" title="Total Debet" />
                    <x-table.th name="kredit" title="Total Kredit" />
                    <x-table.th name="saldo_akhir" title="Saldo Akhir" />
                </x-slot>
                <x-slot name="body">
                    <x-table.tr>
                        <x-table.td colspan="2"></x-table.td>
                        <x-table.td class="font-weight-bold">PENDAPATAN</x-table.td>
                        <x-table.td colspan="5"></x-table.td>
                    </x-table.tr>
                    @foreach ($this->labaRugiPerRekening->get('K') as $rekening)
                        <x-table.tr>
                            <x-table.td>{{ $rekening->thn }}</x-table.td>
                            <x-table.td>{{ $rekening->kd_rek }}</x-table.td>
                            <x-table.td>{{ $rekening->nm_rek }}</x-table.td>
                            <x-table.td>{{ $rekening->tipe }}</x-table.td>
                            <x-table.td>{{ rp($rekening->saldo_awal) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->debet) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->kredit) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->saldo_akhir) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                    <x-table.tr>
                        <x-table.td colspan="2"></x-table.td>
                        <x-table.td class="font-weight-bold">BEBAN & BIAYA</x-table.td>
                        <x-table.td colspan="5"></x-table.td>
                    </x-table.tr>
                    @foreach ($this->labaRugiPerRekening->get('D') as $rekening)
                        <x-table.tr>
                            <x-table.td>{{ $rekening->thn }}</x-table.td>
                            <x-table.td>{{ $rekening->kd_rek }}</x-table.td>
                            <x-table.td>{{ $rekening->nm_rek }}</x-table.td>
                            <x-table.td>{{ $rekening->tipe }}</x-table.td>
                            <x-table.td>{{ rp($rekening->saldo_awal) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->debet) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->kredit) }}</x-table.td>
                            <x-table.td>{{ rp($rekening->saldo_akhir) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
