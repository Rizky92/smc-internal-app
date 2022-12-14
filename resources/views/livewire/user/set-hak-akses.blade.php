<div class="d-flex align-items-end h-100">
    @once
        @push('js')
            <script>
                let dataModal

                $(document).on('livewire:load', () => {
                    dataModal = $('#hak-akses')
                })

                Livewire.on('setUser', console.log)
            </script>
        @endpush
    @endonce
    <button type="button" class="btn btn-default mb-3" data-toggle="modal" data-target="#hak-akses">
        <i class="fas fa-info-circle"></i>
        <span class="ml-1">Set hak akses</span>
    </button>
    <div class="modal fade" id="hak-akses">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Setup hak akses untuk user</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
