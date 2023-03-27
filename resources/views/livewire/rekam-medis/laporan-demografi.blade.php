<div>
    <x-flash />

    <x-card use-default-filter use-loading wire:init="loadProperties">
        <x-slot name="body">
            <x-table style="width: 150rem" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th title="Kecamatan" width="250" />
                    <x-table.th title="No. RM" width="70" />
                    <x-table.th title="No. Registrasi" width="150" />
                    <x-table.th title="Nama Pasien" width="250" />
                    <x-table.th title="Alamat" width="500" />
                    <x-table.th title="Umur" width="50" />
                    <x-table.th title="L / P" width="50" />
                    <x-table.th title="Diagnosa" />
                    <x-table.th title="Agama" width="100" />
                    <x-table.th title="Pendidikan" width="100" />
                    <x-table.th title="Bahasa" width="100" />
                    <x-table.th title="Suku" width="100" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->demografiPasien as $pasien)
                        <x-table.tr>
                            <x-table.td>{{ $pasien->kecamatan }}</x-table.td>
                            <x-table.td>{{ $pasien->no_rm }}</x-table.td>
                            <x-table.td>{{ $pasien->no_rawat }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->almt }}</x-table.td>
                            <x-table.td>{{ $pasien->umur }}</x-table.td>
                            <x-table.td>{{ $pasien->jk }}</x-table.td>
                            <x-table.td>{{ $pasien->diagnosa }}</x-table.td>
                            <x-table.td>{{ $pasien->agama }}</x-table.td>
                            <x-table.td>{{ $pasien->pendidikan }}</x-table.td>
                            <x-table.td>{{ $pasien->bahasa }}</x-table.td>
                            <x-table.td>{{ $pasien->suku }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->demografiPasien" />
        </x-slot>
    </x-card>
</div>
