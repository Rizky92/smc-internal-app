<div>
    <x-modal id="modal-role-permissions" title="Set Permission untuk Role" livewire>
        <x-slot name="body" class="position-relative py-0">
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
        <x-slot name="footer" class="justify-content-end">
            <x-filter.search />
            <x-button class="btn-default" data-dismiss="modal" id="batalsimpan" title="Batal" />
            <x-button class="btn-primary" data-dismiss="modal" id="simpandata" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
