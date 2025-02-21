<div>
    @once
        @push('js')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    $('#modal-lihat-aktivitas').on('shown.bs.modal', e => {
                        @this.emit('siap.show-la')
                    })

                    $('#modal-lihat-aktivitas').on('hide.bs.modal', e => {
                        @this.emit('siap.hide-la')
                    })
                })
            </script>
        @endpush
    @endonce

    <x-modal livewire id="modal-lihat-aktivitas" title="Lihat Aktivitas">
        <x-slot name="body">
            <div class="timeline">
                @forelse ($this->aktivitasUser as $date => $timeline)
                    <div class="time-label">
                        <span class="bg-white text-dark text-sm px-3 border font-weight-bold">
                            {{ carbon_immutable($date)->format('d F Y') }}
                        </span>
                    </div>

                    @foreach ($timeline as $item)
                        <div class="{{ Arr::toCssClasses(['mt-3' => $loop->first && ! $loop->parent->first]) }}">
                            <i class="fas fa-angle-right bg-dark"></i>
                            <div class="timeline-item border-0 shadow-none bg-transparent" style="margin-top: -0.2rem">
                                <div class="timeline-body mt-n1 d-flex justify-content-start align-items-start">
                                    <span class="badge badge-secondary text-xs" style="margin-inline-end: 1.25rem">
                                        {{ carbon_immutable($item->waktu)->format('H:i') }}
                                    </span>
                                    <div class="mt-n1 flex-fill">
                                        <h6 style="margin-top: 0.2rem; margin-bottom: 0.25rem">
                                            Mengunjungi
                                            <a href="{{ Route::has($item->route_name) ? route($item->route_name) : '#' }}">
                                                {{ str($item->breadcrumbs)->afterLast('/')->trim() }}
                                            </a>
                                        </h6>
                                        <div class="d-flex w-100 justify-content-start align-items-baseline">
                                            <p class="m-0 d-block">
                                                {{ $item->breadcrumbs }}
                                            </p>
                                            <div class="bg-light border rounded px-2 ml-auto" style="padding-top: 0.0625rem; padding-bottom: 0.0625rem">
                                                <span class="text-sm font-weight-medium text-monospace">
                                                    {{ $item->ip_address }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="time-label">
                        <span class="bg-white text-dark text-sm px-3 border font-weight-bold">
                            {{ now()->format('d F Y') }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <i class="fas fa-times bg-dark"></i>
                        <div class="timeline-item border-0 shadow-none bg-transparent" style="margin-top: -0.2rem">
                            <div class="timeline-body mt-n1 d-flex justify-content-start align-items-start">
                                <div class="mt-n1">
                                    <p class="m-0">User ini belum pernah membuka {{ config('app.name') }}!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse

                <div>
                    <i class="fas fa-clock fa-fw bg-gray"></i>
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-filter.search /> --}}
            <x-button size="sm" title="Keluar" data-dismiss="modal" />
        </x-slot>
    </x-modal>
</div>
