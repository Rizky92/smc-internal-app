<div>
    @if (session()->has('saved.content'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('saved.content') }}
            </p>
        </div>
    @endif

    <div class="card">
        @once
            @push('css')
                <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
                <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
            @endpush
            @push('js')
                <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
                <script>
                    let hakAksesMultipleSelect

                    $(document).ready(() => {
                        hakAksesMultipleSelect = $('.select2').select2({
                            theme: 'bootstrap4'
                        })
                    })

                    $('.select2').change(() => console.log($('.select2').val()))

                    const loadData = (kode, role) => {
                        @this.getItem(barang)

                        hakAksesMultipleSelect.val(supplier)

                        hakAksesMultipleSelect.trigger('change')
                    }

                    $('#simpandata').click(() => @this.simpan(hakAksesMultipleSelect.val()))
                </script>
            @endpush
        @endonce
        <div class="card-body border-bottom" id="input">
            <div class="row">
                <div class="col-2">
                    <div class="form-group">
                        <label for="user" class="text-sm">NRP</label>
                        <input type="text" class="form-control" id="user" readonly autocomplete="off" wire:model.defer="userId">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="name" class="text-sm">Nama</label>
                        <input type="text" class="form-control" id="name" readonly autocomplete="off" wire:model.defer="name">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" wire:ignore>
                        <label for="hak_akses" class="text-sm">Hak Akses</label>
                        <select class="form-control select2" name="hak_akses" id="hak_akses" multiple>
                            @foreach ($roles as $id => $role)
                                <option value="{{ $id }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
                        <button type="button" class="btn btn-primary btn-sm" id="simpandata">
                            Simpan
                        </button>
                        <button type="button" wire:click="exportToExcel" class="ml-2 btn btn-default btn-sm">
                            <i class="fas fa-file-excel"></i>
                            <span class="ml-1">Export ke Excel</span>
                        </button>
                        <div class="input-group input-group-sm w-25 ml-auto">
                            <input type="search" id="cari" name="cari" placeholder="Cari..." class="form-control" wire:model.defer="cari" wire:keydown.enter.stop="$emit('refreshData')">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default" wire:click="$emit('refreshData')">
                                    <i class="fas fa-sync"></i>
                                    <span class="ml-1">Refresh</span>
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
                    @foreach ($users as $user)
                        <tr style="position: relative">
                            <td>
                                {{ $user->user_id }}
                                <a href="#" style="position: absolute; left: 0; right: 0; top: 0; down: 0; text-decoration: none"></a>
                            </td>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->nm_jbtn }}</td>
                            <td>Petugas</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    @php($first = $loop->first ? '' : 'ml-1')
                                    <span class="{{ $first }} badge badge-sm">{{ $role }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $users->count() }} dari total {{ number_format($users->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
