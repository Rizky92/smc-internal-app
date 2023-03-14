<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-role-permissions').on('shown.bs.modal', e => {
                        @this.emit('siap.show')
                    })

                    $('#modal-role-permissions').on('hide.bs.modal', e => {
                        @this.emit('siap.hide')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal id="modal-role-permissions" title="Set perizinan untuk {{ $roleName }}" livewire>
        <x-slot name="body">
            <x-row-col>
                <ul class="form-group" id="role_permissions">
                    <input type="hidden" name="role" class="d-none" wire:model.defer="roleId">
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
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search method="$refresh" />
            <x-button size="sm" class="ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button size="sm" variant="primary" class="ml-2" data-dismiss="modal" id="simpandata" title="Simpan" icon="fas fa-save" wire:click="$emit('siap.save')" />
        </x-slot>
    </x-modal>
</div>
