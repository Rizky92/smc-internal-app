<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    {{-- <x-table.th name="id" title="#" /> --}}
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collectionProperty as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->id }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="1" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
