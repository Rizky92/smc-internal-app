<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Kolom</x-table.th>
                    <x-table.th>Judul Menu</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->hakAksesKhanza as $hakAkses)
                        <x-table.tr>
                            <x-table.td>
                                {{ $hakAkses->nama_kolom }}
                                <x-slot name="clickable" data-kolom="{{ $hakAkses->nama_kolom }}" data-judul-menu="{{ $hakAkses->judul_menu }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $hakAkses->judul_menu }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->hakAksesKhanza" />
        </x-slot>
    </x-card>
</div>
