<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table style="min-width: 100%" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th-checkbox-all
                        livewire
                        id="chx-mcu-pama"
                        lookup="chx-user-"
                        model="checkedPasien"
                    />
                    <x-table.th title="No. RM" />
                    <x-table.th title="Nama" />
                    <x-table.th title="No. KTP" />
                    <x-table.th title="JK" />
                    <x-table.th title="Tempat / Tgl. Lahir" />
                    <x-table.th title="Alamat" />
                    <x-table.th title="Agama" />
                    <x-table.th title="Tgl. Daftar" />
                    <x-table.th title="E-mail" />
                    <x-table.th title="status" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPasien as $item)
                        <x-table.tr>
                            <x-table.td-checkbox
                                livewire
                                prefix="chx-user-"
                                :key="$item->no_rkm_medis"
                                :id="$item->no_rkm_medis"
                                model="checkedPasien"
                            />
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->no_ktp }}</x-table.td>
                            <x-table.td>{{ $item->jk }}</x-table.td>
                            <x-table.td>{{ $item->tmp_lahir }} / {{ $item->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                            <x-table.td>{{ $item->agama }}</x-table.td>
                            <x-table.td>{{ $item->tgl_daftar }}</x-table.td>
                            <x-table.td>{{ $item->email }}</x-table.td>
                            <x-table.td>Belum</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPasien" />
        </x-slot>
    </x-card>
</div>
