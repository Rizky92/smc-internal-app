<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body" class="table-responsive">
            <x-table style="width: 150rem">
                <x-slot name="columns">
                    <x-table.th width="250">Kecamatan</x-table.th>
                    <x-table.th width="70">No. RM</x-table.th>
                    <x-table.th width="150">No. Registrasi</x-table.th>
                    <x-table.th width="250">Nama Pasien</x-table.th>
                    <x-table.th width="500">Alamat</x-table.th>
                    <x-table.th width="50">Umur</x-table.th>
                    <x-table.th width="50">L / P</x-table.th>
                    <x-table.th>Diagnosa</x-table.th>
                    <x-table.th width="100">Agama</x-table.th>
                    <x-table.th width="100">Pendidikan</x-table.th>
                    <x-table.th width="100">Bahasa</x-table.th>
                    <x-table.th width="100">Suku</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->demografiPasien as $pasien)
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
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->demografiPasien" />
        </x-slot>
    </x-card>
</div>
