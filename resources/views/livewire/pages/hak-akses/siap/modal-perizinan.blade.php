<div>
    @once
        @push('js')
            <script>
                $(document).on('DOMContentLoaded', e => {
                    $('#modal-perizinan').on('shown.bs.modal', e => {
                        @this.emit('siap.show')
                    })

                    $('#modal-perizinan').on('hide.bs.modal', e => {
                        @this.emit('siap.hide')
                    })
                })

                $(document).on('role-created', e => {
                    $('#modal-perizinan').modal('hide')
                })

                $(document).on('role-updated', e => {
                    $('#modal-perizinan').modal('hide')
                })
            </script>
        @endpush
    @endonce

    <x-modal id="modal-perizinan" title="Set perizinan untuk {{ $roleName }}" livewire>
        <x-slot name="body" class="p-0" style="overflow-x: hidden">
            <form id="form-perizinan" wire:submit.prevent="{{ $roleId !== -1 ? 'update' : 'create' }}">
                <x-row-col class="sticky-top bg-white pt-3 pb-2 px-3">
                    <x-flash />
                    <div class="{{ Arr::toCssClasses(['form-group', 'mt-3' => session()->has(['flash.type', 'flash.message'])]) }}">
                        <label for="role-sekarang">Nama role:</label>
                        <input type="text" id="role-sekarang" wire:model.defer="roleName" class="form-control form-control-sm" />
                    </div>
                </x-row-col>
                <x-row-col class="mt-1">
                    <ul class="form-group">
                        @foreach ($this->permissions as $group => $items)
                            @foreach ($items as $key => $name)
                                <li class="{{ Arr::toCssClasses(['custom-control custom-checkbox', 'mt-3' => $loop->first && ! $loop->parent->first]) }}">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="permission-{{ $key }}"
                                        name="permissions"
                                        value="{{ $key }}"
                                        wire:model.defer="checkedPermissions.{{ $key }}" />
                                    <label for="permission-{{ $key }}" class="custom-control-label font-weight-normal">
                                        {{ $name }}
                                    </label>
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
            <x-button size="sm" variant="primary" type="submit" class="ml-2" id="simpandata" title="Simpan" icon="fas fa-save" form="form-perizinan" />
        </x-slot>
    </x-modal>
</div>
