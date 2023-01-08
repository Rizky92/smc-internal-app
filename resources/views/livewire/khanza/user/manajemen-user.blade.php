<div>
    <x-flash />

    @once
        @push('js')
            <script>
                let inputNRP
                let inputNama

                $(document).ready(() => {
                    inputNRP = $('#user')
                    inputNama = $('#nama')
                })

                function loadData({
                    nrp,
                    nama
                }) {
                    inputNRP.val(nrp)
                    inputNama.val(nama)

                    @this.emit('prepareUser', nrp)
                }
            </script>
        @endpush
    @endonce

    {{-- <livewire:khanza.user.utils.set-hak-akses /> --}}

    <livewire:khanza.user.utils.transfer-hak-akses />

    <x-card>
        <x-slot name="header">
            <x-card.row wire:ignore>
                <div class="col-2">
                    <div class="form-group">
                        <label class="text-sm" for="user">NRP</label>
                        <input class="form-control form-control-sm bg-light" id="user" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="text-sm" for="nama">Nama</label>
                        <input class="form-control form-control-sm bg-light" id="nama" type="text" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-end h-100">
                        <button class="btn btn-sm btn-default mb-3" data-toggle="modal" data-target="#transfer-hak-akses" type="button" id="button-transfer-hak-akses">
                            <i class="fas fa-share-square"></i>
                            <span class="ml-1">Transfer hak akses</span>
                        </button>
                    </div>
                </div>
            </x-card>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>NRP</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Jabatan</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->users as $user)
                        <x-table.tr>
                            <x-table.td>
                                {{ $user->nip }}
                                <x-slot name="clickable" data-nrp="{{ $user->nip }}" data-nama="{{ $user->nama }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $user->nama }}</x-table.td>
                            <x-table.td>{{ $user->nm_jbtn }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->users" />
        </x-slot>
    </x-card>
</div>
