<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-ubah-perizinan').on('shown.bs.modal', e => {
                        @this.emit('siap.show')
                    })

                    $('#modal-ubah-perizinan').on('hide.bs.modal', e => {
                        @this.emit('siap.hide')
                    })

                    $('#')
                })
            </script>
        @endpush
    @endonce
    <x-modal id="modal-ubah-perizinan" title="Set perizinan untuk {{ $roleName }}" livewire>
        <x-slot name="body">
            <form id="form-ubah-perizinan" wire:submit.prevent="$emit('siap.save')">
                <x-row-col>
                    <div class="form-group">
                        <label for="role-sekarang">Nama role :</label>
                        <input type="text" id="role-sekarang" wire:model.defer="roleName" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
                <x-row-col>
                    <ul class="form-group">
                        @foreach ($this->permissions as $group => $items)
                            @foreach ($items as $key => $name)
                                <li class="{{ Arr::toCssClasses(['custom-control custom-checkbox', 'mt-3' => $loop->first && !$loop->parent->first]) }}">
                                    <input class="custom-control-input" type="checkbox" id="permission-{{ $key }}" name="permissions" wire:model.defer="checkedPermissions.{{ $key }}" value="{{ $key }}">
                                    <label for="permission-{{ $key }}" class="custom-control-label font-weight-normal">{{ $name }}</label>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </x-row-col>
            </form>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-ubah-perizinan" />
        </x-slot>
    </x-modal>
</div>
