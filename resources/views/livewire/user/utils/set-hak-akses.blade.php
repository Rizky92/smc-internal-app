<div class="modal fade" id="hak-akses" wire:ignore.self>
    @once
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

                @this.simpan(inputNRP.val(), selectedRoles, selectedPermissions)
            })

            $('input[type=checkbox]').change(function(e) {
                var checked = $(this).prop("checked"),
                    container = $(this).parent(),
                    siblings = container.siblings()

                container.find('input[type=checkbox]').prop({
                    indeterminate: false,
                    checked: checked
                })

                function checkSiblings(el) {
                    var parent = el.parent().parent(),
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
        </script>
    @endonce
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Setup hak akses untuk user</h4>
                <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                    <span aria-hidden="true">&times</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="form-group" id="role_permissions">
                            @foreach ($this->roles as $role)
                                <li class="custom-control custom-checkbox">
                                    <input class="custom-control-input" id="role-{{ $role->id }}" name="roles" type=checkbox value="{{ $role->id }}">
                                    <label class="custom-control-label" for="role-{{ $role->id }}">{{ Str::of($role->name)->upper() }}</label>
                                    <ul class="form-group">
                                        @foreach ($role->permissions as $permission)
                                            <li class="custom-control custom-checkbox">
                                                <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $permission->id }}-{{ $role->id }}" name="permissions" data-role-id="{{ $role->id }}" type=checkbox value="{{ $permission->id }}">
                                                <label class="custom-control-label font-weight-normal" for="permission-{{ $permission->id }}-{{ $role->id }}">{{ $permission->name }}</label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                            <li>
                                <h6>Hak akses lainnya</h6>
                                <ul class="form-group">
                                    @foreach ($this->otherPermissions as $op)
                                        <li class="custom-control custom-checkbox">
                                            <input class="custom-control-input custom-control-input-secondary" id="permission-{{ $op->id }}" name="permissions" type=checkbox value="{{ $op->id }}">
                                            <label class="custom-control-label font-weight-normal" for="permission-{{ $op->id }}">{{ $op->name }}</label>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button class="btn btn-sm btn-default" id="batalsimpan" data-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-sm btn-primary" id="simpandata" type="button">
                    <i class="fas fa-save"></i>
                    <span class="ml-1">Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>
