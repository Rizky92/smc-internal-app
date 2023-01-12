<div>
    <x-modal :livewire="true" title="Transfer Role Permission untuk Custom Report" id="custom-report-transfer-permissions">
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <x-row-col>
                <div class="d-flex justify-content-start px-3 pt-3">
                    <div class="w-50">
                        <label>User:</label>
                        <p>{{ "{$nrp} {$nama}" }}</p>
                    </div>
                    <div class="w-50">
                        <label>Hak akses diberikan:</label>
                        <ul class="d-flex flex-wrap p-0 m-0" style="list-style: none; row-gap: 0.5rem; column-gap: 0.25rem">
                            @foreach ($roles as $role)
                                <li class="badge badge-light text-sm font-weight-normal border">{{ $role }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </x-row-col>
            <x-row-col class="mt-2">
                <div class="table-responsive">
                    <x-table>
                        <x-slot name="columns">
                            <x-table.th>#</x-table.th>
                            <x-table.th>NRP</x-table.th>
                            <x-table.th>Nama</x-table.th>
                            <x-table.th>Jabatan</x-table.th>
                        </x-slot>
                        <x-slot name="body">
                            @foreach ($this->availableUsers as $user)
                                <x-table.tr>
                                    <x-table.td>
                                        <input id="user-{{ $user->nip }}" type="checkbox" wire:model.defer="checkedUsers" value="{{ $user->nip }}">
                                        <label for="user-{{ $user->nip }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
                                    </x-table.td>
                                    <x-table.td>{{ $user->nip }}</x-table.td>
                                    <x-table.td>{{ $user->nama }}</x-table.td>
                                    <x-table.td>{{ $user->nm_jbtn }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search model="customReportSearchUsers" method="$refresh" />
            <x-button class="btn-default ml-auto" data-dismiss="modal" wire:click="resetModal" title="Batal" />
            <x-button class="btn-primary ml-2" data-dismiss="modal" wire:click="transferPermissions" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>