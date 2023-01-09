<div class="modal fade" id="transfer-akses-custom-report" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transfer Hak Akses Custom Report</h4>
                <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                    <span aria-hidden="true">&times</span>
                </button>
            </div>
            <div class="modal-body p-0" style="overflow-x: hidden">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-start align-items-start px-3 pt-3">
                            <div class="d-flex flex-column align-items-start w-50">
                                <dt>User:</dt>
                                <dd>{{ $nrp }} {{ $nama }}</dd>
                            </div>
                            <div class="d-flex flex-column align-items-start w-50">
                                <dt>Hak Akses akan Diberikan:</dt>
                                <dd>
                                    <ul class="d-flex flex-wrap p-0" style="list-style: none; row-gap: 0.5rem; column-gap: 0.25rem">
                                        @foreach ($roles as $role)
                                            <li>
                                                <span class="badge badge-light text-sm font-weight-normal border">{{ $role }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 table-responsive">
                        <table class="table table-hover table-striped table-sm text-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NRP</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->availableUsers as $user)
                                    <tr style="position: relative">
                                        <td>
                                            <input id="user-{{ $user->nip }}" type="checkbox" wire:model.defer="checkedUsers" value="{{ $user->nip }}">
                                            <label for="user-{{ $user->nip }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer"></label>
                                        </td>
                                        <td>
                                            {{ $user->nip }}
                                        </td>
                                        <td>{{ $user->nama }}</td>
                                        <td>{{ $user->nm_jbtn }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <div class="input-group input-group-sm" style="width: 16rem">
                    <input class="form-control" type="search" wire:model.defer="customReportCariUser" wire:keydown.enter.stop="$refresh" />
                    <div class="input-group-append">
                        <button class="btn btn-sm btn-default" type="button" wire:click="$refresh">
                            <i class="fas fa-search"></i>
                            <span class="ml-1">Cari</span>
                        </button>
                    </div>
                </div>
                <button class="btn btn-sm btn-default ml-auto" data-dismiss="modal" type="button" wire:click="resetModal">Batal</button>
                <button class="btn btn-sm btn-primary" type="button" data-dismiss="modal" wire:click="$emit('transferPermissions')">
                    <i class="fas fa-save"></i>
                    <span class="ml-1">Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>
