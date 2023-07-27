<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="min-width: 100%" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="id" title="No. Rawat" />
                    <x-table.th name="id" title="Tgl. Registrasi" />
                    <x-table.th name="id" title="Pasien" />
                    <x-table.th name="id" title="No. RM" />
                    <x-table.th name="id" title="Pengobatan" />
                    <x-table.th name="id" title="No. Telp" />
                    <x-table.th name="id" title="Alamat" />
                </x-slot>
                <x-slot name="body">
                    {{-- @dump($this->dataLaporanPemakaianObatTB) --}}
                    @forelse ($this->dataLaporanPemakaianObatTB as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>
                                {{-- @dump($item->pemberianObat) --}}
                                @forelse (optional($item->pemberianObat) ?? [] as $obat)
                                    <p class="m-0 p-0">
                                        {{ optional(optional($obat)->obat)->nama_brng }} <br>
                                        {{ optional($obat)->jml }}
                                    </p>
                                @empty
                                    -
                                @endforelse
                            </x-table.td>
                            <x-table.td>{{ $item->no_tlp }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="7" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanPemakaianObatTB" />
        </x-slot>
    </x-card>
</div>
