<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 pb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="pt-3 border-top">
                <x-filter.label constant-width>Instansi:</x-filter.label>
                <x-filter.select2 livewire name="perusahaan" :options="$this->dataPerusahaan" />
                <x-button size="sm" variant="primary" title="Kirim email" icon="fas fa-envelope" class="ml-auto" wire:click.prevent="sendEmail" :disabled="empty($this->checkedPasien)" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table style="min-width: 100%" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th-checkbox-all livewire id="chx-mcu-pama" lookup="chx-user-" model="checkedPasien" />
                    <x-table.th title="Penjamin" />
                    <x-table.th title="No. Rawat" />
                    <x-table.th title="No. RM" />
                    <x-table.th title="Nama" />
                    <x-table.th title="JK" />
                    <x-table.th title="Agama" />
                    <x-table.th title="Tgl. Daftar" />
                    <x-table.th title="Poli" />
                    <x-table.th title="E-mail" />
                    <x-table.th title="Instansi" />
                    <x-table.th title="NRP" />
                    <x-table.th title="Berkas" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPasienPoliMCU as $item)
                        <x-table.tr>
                            <x-table.td-checkbox livewire prefix="chx-user-" :key="$item->no_rawat" :id="$item->no_rawat" model="checkedPasien" obscure-checkbox />
                            <x-table.td>
                                {{ $item->penjamin->png_jawab }}
                            </x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->nm_pasien }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->jk }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->agama }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->tgl_registrasi }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->poliklinik->nm_poli }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->email }}
                            </x-table.td>
                            <x-table.td>
                                {{ optional($item->pasien->perusahaan)->nama_perusahaan }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->pasien->nip }}
                            </x-table.td>
                            <x-table.td>
                                @forelse ($item->berkasDigital as $berkas)
                                    <a href="{{ asset($berkas->lokasi_file) }}" target="_blank">
                                        {{ $berkas->lokasi_file }}
                                    </a>
                                @empty
                                    -
                                @endforelse
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPasienPoliMCU" />
        </x-slot>
    </x-card>
</div>
