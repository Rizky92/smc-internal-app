<div>
    @if (session()->has('saved.content'))
        <div class="alert alert-{{ session('saved.type') }} alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('saved.content') }}
            </p>
        </div>
    @endif

    @once
        @push('js')
            <script>
                let inputNRP
                let inputNama
                let inputHakAkses

                $(document).ready(() => {
                    inputNRP = $('#user')
                    inputNama = $('#nama')
                    inputHakAkses = $('input[name=rolenames]')
                })

                function loadData({ nrp, nama, roleIds }) {
                    inputNRP.val(nrp)
                    inputNama.val(nama)

                    let roles = Array.from(roleIds.split(','))
                    inputHakAkses.each((i, el) => {
                        (el.value === roles[i])
                            ? el.checked = true
                            : el.checked = false
                    })
                }

                $('#simpandata').click(() => {
                    let nrp = inputNRP.val()
                    let roles = []

                    inputHakAkses.each((i, el) => {
                        if (el.checked) {
                            roles.push(el.value)
                        }
                    })

                    console.log(nrp, roles)

                    @this.simpan(nrp, roles)

                    clearData()
                })

                $('#batalsimpan').click(() => {
                    clearData()
                })

                const clearData = () => {
                    inputNRP.val('')
                    inputNama.val('')
                    inputHakAkses.each((i, el) => {
                        el.checked = false
                    })
                }
            </script>
        @endpush
    @endonce

    <div class="card">
        <div class="card-body">
            <div class="row" wire:ignore>
                <div class="col-2">
                    <div class="form-group">
                        <label for="user" class="text-sm">NRP</label>
                        <input type="text" class="form-control" id="user" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="nama" class="text-sm">Nama</label>
                        <input type="text" class="form-control" id="nama" readonly autocomplete="off">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="text-sm">Hak Akses</label>
                        <div class="d-flex justify-items-start align-items center mt-1">
                            @foreach ($this->roles as $id => $roleName)
                                <div class="custom-control custom-checkbox {{ !$loop->first ? 'ml-4' : '' }}">
                                    <input class="custom-control-input" type="checkbox" id="role-{{ $id }}" value="{{ $id }}" name="rolenames">
                                    <label for="role-{{ $id }}" class="custom-control-label">{{ $roleName }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <button type="button" class="btn btn-primary btn-sm" id="simpandata">
                            <i class="fas fa-save"></i>
                            <span class="ml-1">Simpan</span>
                        </button>
                        <button type="button" class="btn btn-default btn-sm ml-2" id="batalsimpan">Batal</button>
                        <span class="text-sm ml-auto pr-2">Tampilkan:</span>
                        <div class="input-group input-group-sm" style="width: 4rem">
                            <select name="perpage" class="custom-control custom-select" wire:model.defer="perpage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <span class="text-sm pl-2">per halaman</span>
                        <button type="button" class="btn btn-sm btn-default ml-4" wire:click="$emit('hardRefresh')">
                            <i class="fas fa-sync-alt"></i>
                            <span class="ml-1">Refresh</span>
                        </button>
                        <div class="input-group input-group-sm w-25 ml-2">
                            <input type="search" id="cari" name="cari" placeholder="Cari..." class="form-control" wire:model.defer="cari" wire:keydown.enter.stop="$refresh">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default" wire:click="$refresh">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jenis</th>
                        <th>Hak Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->users as $user)
                        <tr style="position: relative">
                            <td>
                                {{ $user->user_id }}
                                <a href="#" style="
                                    display: inline;
                                    position: absolute;
                                    left: 0; right: 0; top: 0; bottom: 0;" data-nrp="{{ $user->user_id }}" data-nama="{{ $user->nama }}" data-role-ids="{{ $user->roles->pluck('name', 'id')->flip()->join(',') }}" onclick="loadData(this.dataset)"></a>
                            </td>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->nm_jbtn }}</td>
                            <td>Petugas</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    @php($first = $loop->first ? '' : 'ml-1')
                                    <span class="{{ $first }} badge badge-dark">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->users->count() }} dari total {{ number_format($this->users->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
