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
                    <input type="hidden" name="role" class="d-none">
                    @foreach ($this->permissions as $group => $items)
                        @foreach ($items as $key => $name)
                            <li class="{{ Arr::toCssClasses(['custom-control custom-checkbox', 'mt-3' => $loop->first && !$loop->parent->first]) }}">
                                <input class="custom-control-input" type="checkbox" id="permission-{{ $key }}" value="{{ $key }}" name="permissions">
                                <label for="permission-{{ $key }}" class="custom-control-label font-weight-normal">{{ $name }}</label>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-filter.search />
            <x-button class="btn-default ml-auto" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button class="ml-2 btn-primary" data-dismiss="modal" id="simpandata" title="Simpan" icon="fas fa-save" wire:click="$emit('siap.simpan')" />
        </x-slot>
    </x-modal>
</div>
