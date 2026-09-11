<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto">Tahapan</x-filter.label>
                <x-filter.select class="ml-3" model="stage" :options="$this->pilihanStage" />
                <x-filter.label class="ml-3">Hasil</x-filter.label>
                <x-filter.select class="ml-3" model="hasil" :options="['semua' => 'Semua', 'berhasil' => 'Berhasil', 'gagal' => 'Gagal']" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                @if (! $isDeferred)
                    <div class="text-sm">
                        Total
                        <span class="font-weight-bold">{{ $this->ringkasanPengiriman['total'] }}</span>
                        kiriman &mdash;
                        <span class="badge badge-success">{{ $this->ringkasanPengiriman['berhasil'] }} berhasil</span>
                        <span class="badge badge-danger ml-1">{{ $this->ringkasanPengiriman['gagal'] }} gagal</span>
                    </div>
                @endif

                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 190rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" name="created_at" title="Waktu Diterima" />
                    <x-table.th style="width: 12ch" name="status" title="Hasil" />
                    <x-table.th style="width: 28ch" name="stage" title="Tahapan" />
                    <x-table.th style="width: 25ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 12ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th style="width: 35ch" name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 35ch" name="dokter_perujuk" title="Dokter Perujuk" />
                    <x-table.th style="width: 18ch" name="noorder" title="No. Order" />
                    <x-table.th style="width: 20ch" name="accession_number" title="Accession Number" />
                    <x-table.th style="width: 40ch" name="imaging_study_id" title="ImagingStudy ID" />
                    <x-table.th style="width: 45ch" name="study_instance_uid" title="Study Instance UID" />
                    <x-table.th style="width: 60ch" name="message" title="Keterangan" />
                    <x-table.th style="width: 22ch" name="error_code" title="Kode Error" />
                    <x-table.th style="width: 14ch" name="delivery_count" title="Jml. Kiriman" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->logPengirimanDicom as $item)
                        <x-table.tr>
                            <x-table.td>{{ carbon($item->created_at)->format('d-m-Y H:i:s') }}</x-table.td>
                            <x-table.td>
                                <x-badge :variant="$item->status ? 'success' : 'danger'">
                                    {{ $item->status ? 'Berhasil' : 'Gagal' }}
                                </x-badge>
                            </x-table.td>
                            <x-table.td>{{ $item->stage_label }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->dokter_perujuk }}</x-table.td>
                            <x-table.td>{{ $item->noorder ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->accession_number ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->imaging_study_id ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->study_instance_uid ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->error_message ?? ($item->message ?? '-') }}</x-table.td>
                            <x-table.td>{{ $item->error_code ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->delivery_count }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="14" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->logPengirimanDicom" />
        </x-slot>
    </x-card>
</div>
