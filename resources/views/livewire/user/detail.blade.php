<div>
    <div class="modal fade" id="detail_modal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail perizinan role</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @if ($this->currentUser)
                    <div class="modal-body">
                        <p>Role yang dimiliki:</p>
                        <ul>
                            @foreach ($this->currentUser->roles as $currentRole)
                                <li>
                                    <p>
                                        {{ $currentRole->name }}:
                                    </p>
                                    <ul>
                                        @foreach ($currentRole->permissions as $permissionRole)
                                            <li>{{ $permissionRole->name }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                        <p>Permission yang dimiliki:</p>
                        <ul>
                            @foreach ($this->currentUser->permissions as $otherPermission)
                                <li>{{ $otherPermission->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
