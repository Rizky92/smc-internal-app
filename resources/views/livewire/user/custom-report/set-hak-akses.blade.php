<div>
    @once
        @push('js')
            <script>
                $('#simpandata').click(e => {
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

                    @this.set('checkedRoles', selectedRoles)
                    @this.set('checkedPermissions', selectedPermissions)

                    @this.customReportSyncRolesAndPermissions()
                })

                $('input[type="checkbox"]').change(function(e) {
                    var checked = $(this).prop("checked"),
                        container = $(this).parent(),
                        siblings = container.siblings()

                    container.find('input[type="checkbox"]').prop({
                        indeterminate: false,
                        checked: checked
                    })

                    function checkSiblings(el) {
                        var parent = el.parent().parent(),
                            all = true

                        el.siblings().each(function() {
                            let returnValue = all = ($(this).children('input[type="checkbox"]').prop("checked") === checked)

                            return returnValue
                        })

                        if (all && checked) {
                            parent.children('input[type="checkbox"]').prop({
                                indeterminate: false,
                                checked: checked
                            })

                            checkSiblings(parent)
                        } else if (all && !checked) {
                            parent.children('input[type="checkbox"]').prop("checked", checked)
                            parent.children('input[type="checkbox"]').prop("indeterminate", (parent.find('input[type="checkbox"]:checked').length > 0))

                            checkSiblings(parent)
                        } else {
                            el.parents("li").children('input[type="checkbox"]').prop({
                                indeterminate: true,
                                checked: false
                            })
                        }
                    }

                    checkSiblings(container)
                })
            </script>
        @endpush
    @endonce
    <x-modal :livewire="true" title="Set Role Permission untuk Custom Report">
        <x-slot name="body">
            <x-row-col>
                <ul class="form-group" id="role_permissions" style="list-style: none; margin: 0; padding: 0;">
                    @foreach ($this->availableRoles as $role)
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" id="role-{{ $role->id }}" name="roles" type="checkbox" value="{{ $role->id }}">
                            <label class="custom-control-label" for="role-{{ $role->id }}">{{ Str::of($role->name)->upper() }}</label>
                            <ul class="form-group" style="margin: 0; padding: 0;">
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
                        <ul class="form-group px-0" style="list-style: none; margin: 0; padding: 0;">
                            @foreach ($this->otherAvailabelPermissions as $op)
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
        <x-slot name="footer" class="justify-content-end">
            <x-button class="btn-default" id="batalsimpan" data-dismiss="modal" title="Batal" />
            <x-button class="btn-primary" id="simpandata" data-dismiss="modal" title="Simpan" icon="fas fa-save" />
        </x-slot>
    </x-modal>
</div>
