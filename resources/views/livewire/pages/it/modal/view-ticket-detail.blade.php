<x-modal id="modal-view-detail" title="Detail Tiket" size="xl" livewire>
    <x-slot name="body">
        @if ($ticket)
            @include('livewire.pages.it.ticket-detail')
        @else
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data tiket...</p>
            </div>
        @endif
    </x-slot>
    <x-slot name="footer">
        <x-button data-dismiss="modal" title="Tutup" />
    </x-slot>

    @push('js')
        <script>
            Livewire.on('show-modal-detail', () => {
                $('#modal-view-detail').modal('show');
            });
        </script>
    @endpush
</x-modal>
