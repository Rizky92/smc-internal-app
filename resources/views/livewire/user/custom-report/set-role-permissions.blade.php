<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#set-role-permissions').click(e => {
                        let selectedRoles = []
                        let selectedPermissions = []

                        inputRoles.each((i, el) => {
                            if (el.checked) {
                                selectedRoles.push(el.value)
                            }

                            if (el.indeterminate) {
                                let inputRolePermissions = Array.from(el.nextElementSibling.nextElementSibling.children)

                                inputRolePermissions.forEach(el => {
                                    let permissionCheckbox = el.children[0]

                                    if (permissionCheckbox.checked) {
                                        selectedPermissions.push(permissionCheckbox.value)
                                    }
                                })
                            }
                        })

                        inputPermissions.each((i, el) => {
                            if (el.checked) {
                                selectedPermissions.push(el.value)
                            }
                        })

                        @this.set('checkedRoles', selectedRoles)
                        @this.set('checkedPermissions', selectedPermissions)

                        @this.emit('custom-report.save')
                    })

                    $('input[type=checkbox]').change(function(e) {
                        let checked = $(this).prop("checked"),
                            container = $(this).parent(),
                            siblings = container.siblings()

                        container.find('input[type=checkbox]').prop({
                            indeterminate: false,
                            checked: checked
                        })

                        function checkSiblings(el) {
                            let parent = el.parent().parent(),
                                all = true

                            el.siblings().each(function() {
                                let returnValue = all = ($(this).children('input[type=checkbox]').prop("checked") === checked)

                                return returnValue
                            })

                            if (all && checked) {
                                parent.children('input[type=checkbox]').prop({
                                    indeterminate: false,
                                    checked: checked
                                })

                                checkSiblings(parent)
                            } else if (all && !checked) {
                                parent.children('input[type=checkbox]').prop("checked", checked)
                                parent.children('input[type=checkbox]').prop("indeterminate", (parent.find('input[type=checkbox]:checked').length > 0))

                                checkSiblings(parent)
                            } else {
                                el.parents("li").children('input[type=checkbox]').prop({
                                    indeterminate: true,
                                    checked: false
                                })
                            }
                        }

                        checkSiblings(container)
                    })

                    $('#modal-set-role-permissions').on('shown.bs.modal', e => {
                        @this.emit('custom-report.show-srp')
                    })

                    $('#modal-set-role-permissions').on('hide.bs.modal', e => {
                        @this.emit('custom-report.hide-srp')
                    })
                })
            </script>
        @endpush
    @endonce
    <x-modal :livewire="true" title="Set Role Permission untuk Custom Report" id="modal-set-role-permissions">
        <x-slot name="body">
            <x-row-col>
                <ul class="form-group" id="role_permissions" style="list-style: none">
                    @foreach ($this->availableRoles as $role)
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" id="role-{{ $role->id }}" name="roles" type="checkbox" value="{{ $role->id }}">
                            <label class="custom-control-label" for="role-{{ $role->id }}">{{ Str::of($role->name)->upper() }}</label>
                            <ul class="form-group" style="list-style: none">
                                @foreach ($role->permissions as $permission)
                                    <li class="custom-control custom-checkbox">
                                        <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $permission->id }}-{{ $role->id }}" name="permissions" data-role-id="{{ $role->id }}" type="checkbox" value="{{ $permission->id }}">
                                        <label class="custom-control-label font-weight-normal" for="permission-{{ $permission->id }}-{{ $role->id }}">{{ $permission->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                    <li>
                        <h6 class="font-weight-bold">Hak akses lainnya</h6>
                        <ul class="form-group px-0" style="list-style: none">
                            @foreach ($this->otherPermissions as $op)
                                <li class="custom-control custom-checkbox">
                                    <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $op->id }}" name="permissions" type="checkbox" value="{{ $op->id }}">
                                    <label class="custom-control-label font-weight-normal" for="permission-{{ $op->id }}">{{ $op->name }}</label>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
            </x-row-col>
        </x-slot>
        <x-slot name="footer" class="justify-content-start">
            <x-button class="btn-default ml-auto" data-dismiss="modal" title="Batal" />
            <x-button class="btn-primary ml-2" data-dismiss="modal" id="set-role-permissions" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
