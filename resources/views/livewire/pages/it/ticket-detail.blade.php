<div>
    <x-flash />

    <x-row>
        <div class="col-md-8">
            {{-- Ticket Info --}}
            <x-card :table="false">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <span class="text-muted mr-1">{{ $ticket->ticket_number }}</span>
                            <strong>{{ $ticket->title }}</strong>
                        </h3>
                        <x-button as="link" variant="secondary" size="sm" icon="fas fa-arrow-left" :href="route('admin.form-it')" title="Kembali" />
                    </div>
                </x-slot>
                <x-slot name="body">
                    <div class="mb-4">
                        <p style="white-space: pre-line">{{ $ticket->description }}</p>
                    </div>

                    @if ($ticket->attachments->count() > 0)
                        <hr />
                        <h6>
                            <i class="fas fa-paperclip mr-1"></i>
                            Lampiran ({{ $ticket->attachments->count() }})
                        </h6>
                        <div class="row mt-3">
                            @foreach ($ticket->attachments as $attachment)
                                <div class="col-sm-4 col-md-3 mb-3">
                                    <div class="card h-100 border shadow-sm">
                                        @if ($attachment->isImage())
                                            <a href="{{ $attachment->url }}" target="_blank">
                                                <img src="{{ $attachment->url }}" class="card-img-top preview-img" alt="{{ $attachment->original_name }}" style="height: 120px; object-fit: cover" />
                                            </a>
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 120px">
                                                <i
                                                    class="fas {{
                                                        match (true) {
                                                            str_contains($attachment->mime_type, 'pdf') => 'fa-file-pdf text-danger',
                                                            str_contains($attachment->mime_type, 'word') || str_contains($attachment->mime_type, 'officedocument') => 'fa-file-word text-primary',
                                                            str_contains($attachment->mime_type, 'excel') || str_contains($attachment->mime_type, 'sheet') => 'fa-file-excel text-success',
                                                            str_contains($attachment->mime_type, 'zip') || str_contains($attachment->mime_type, 'rar') => 'fa-file-archive text-warning',
                                                            default => 'fa-file text-muted',
                                                        }
                                                    }} fa-3x"></i>
                                            </div>
                                        @endif
                                        <div class="card-body p-2 border-top">
                                            <p class="card-text small text-truncate mb-1" title="{{ $attachment->original_name }}">
                                                {{ $attachment->original_name }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge badge-light text-muted" style="font-size: 10px">{{ $attachment->human_size }}</span>
                                                <a href="{{ $attachment->url }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-slot>
            </x-card>

            {{-- Tabs: Comments & Activities --}}
            <x-navtabs>
                <x-slot name="tabs">
                    <x-navtabs.tab id="tab-comments" title="Komentar ({{ $ticket->comments->count() }})" active />
                    <x-navtabs.tab id="tab-activities" title="Aktivitas" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="tab-comments" active>
                        {{-- Comment Form --}}
                        <div class="mb-4 pt-3">
                            <div class="form-group">
                                <textarea wire:model.defer="commentContent" class="form-control" rows="3" placeholder="Tulis komentar..."></textarea>
                                @error('commentContent')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" wire:model.defer="isInternal" class="custom-control-input" id="isInternal" />
                                    <label class="custom-control-label" for="isInternal">Catatan Internal (Hanya IT)</label>
                                </div>
                                <x-button variant="primary" size="sm" title="Kirim" icon="fas fa-paper-plane" wire:click="addComment" />
                            </div>
                        </div>

                        {{-- Comments List --}}
                        @forelse ($ticket->comments as $comment)
                            <div class="media mb-3 p-3 rounded {{ $comment->is_internal ? 'bg-light border-left border-warning' : 'border' }}">
                                <div class="media-body">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $comment->user->name ?? 'User' }}</strong>
                                        <small class="text-muted">{{ $comment->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                    @if ($comment->is_internal)
                                        <span class="badge badge-warning mb-2">Internal</span>
                                    @endif

                                    <p class="mb-0">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4">Belum ada komentar.</p>
                        @endforelse
                    </x-navtabs.content>

                    <x-navtabs.content id="tab-activities">
                        <div class="timeline timeline-inverse pt-3">
                            @foreach ($ticket->activities as $activity)
                                <div>
                                    <i
                                        class="fas {{
                                            match ($activity->type) {
                                                'status_changed' => 'fa-sync bg-info',
                                                'assigned' => 'fa-user-check bg-success',
                                                'note_added' => 'fa-comment bg-warning',
                                                'sla_breached' => 'fa-exclamation-triangle bg-danger',
                                                default => 'fa-info bg-primary',
                                            }
                                        }}"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="far fa-clock"></i>
                                            {{ $activity->created_at->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header border-0">
                                            <strong>{{ $activity->causer->name ?? 'System' }}</strong>
                                            {{ $activity->description }}
                                            <small class="text-muted ml-1">{{ $activity->created_at->format('d M Y') }}</small>
                                        </h3>
                                    </div>
                                </div>
                            @endforeach

                            <div>
                                <i class="far fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </div>

        <div class="col-md-4">
            {{-- Status Card --}}
            <x-card :table="false">
                <x-slot name="header">
                    <h3 class="card-title">Status & Detail</h3>
                </x-slot>
                <x-slot name="body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-sm">Status</dt>
                        <dd class="col-sm-7"><x-ticket.status-badge :status="$ticket->status" /></dd>

                        <dt class="col-sm-5 text-sm">Prioritas</dt>
                        <dd class="col-sm-7"><x-ticket.priority-badge :priority="$ticket->priority" /></dd>

                        <dt class="col-sm-5 text-sm">Kategori</dt>
                        <dd class="col-sm-7 text-sm">{{ $ticket->category->name ?? '-' }}</dd>

                        <dt class="col-sm-5 text-sm">Dibuat</dt>
                        <dd class="col-sm-7 small text-sm">{{ $ticket->created_at->format('d M Y H:i') }}</dd>
                    </dl>

                    @if ($ticket->isActive())
                        <hr />
                        <div class="form-group mb-2">
                            <label class="small">Ubah Status ke:</label>
                            <div class="btn-group btn-group-sm w-100">
                                @foreach (\App\Domain\Ticket\Enums\TicketStatus::allowedTransitions($ticket->status) as $transition)
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        onclick="confirm('Yakin ingin mengubah status ke {{ \App\Domain\Ticket\Enums\TicketStatus::label($transition) }}?') || event.stopImmediatePropagation()"
                                        wire:click="updateStatus('{{ $transition }}')">
                                        {{ \App\Domain\Ticket\Enums\TicketStatus::label($transition) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" wire:model.defer="statusNote" class="form-control form-control-sm" placeholder="Catatan perubahan (opsional)" />
                        </div>
                    @endif
                </x-slot>
            </x-card>

            {{-- SLA Card --}}
            @if ($ticket->isActive() && $ticket->sla_due_at)
                <x-card :table="false">
                    <x-slot name="header">
                        <h3 class="card-title">SLA Progress</h3>
                    </x-slot>
                    <x-slot name="body">
                        <div class="progress progress-sm active mb-2">
                            @php
                                $percentage = $ticket->sla_percentage;
                                $severity = $ticket->sla_severity;
                                $colorClass = match ($severity) {
                                    'breach' => 'bg-danger',
                                    'warn' => 'bg-warning',
                                    default => 'bg-success',
                                };
                            @endphp

                            <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>{{ $ticket->sla_remaining_label }}</span>
                            <span class="text-muted">Jatuh tempo: {{ $ticket->sla_due_at->format('d M H:i') }}</span>
                        </div>
                    </x-slot>
                </x-card>
            @endif

            {{-- People Card --}}
            <x-card :table="false">
                <x-slot name="header">
                    <h3 class="card-title">Pihak Terkait</h3>
                </x-slot>
                <x-slot name="body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 small">Pelapor</dt>
                        <dd class="col-sm-7 small">
                            <strong>{{ $ticket->reporter_display_name }}</strong>
                            <br />
                            <span class="text-muted text-xs">{{ $ticket->department->nama ?? '-' }}</span>
                            <br />
                            @if ($ticket->reporter_phone)
                                <i class="fas fa-phone-alt mr-1 text-xs"></i>
                                <span class="text-xs">{{ $ticket->reporter_phone }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 small mt-3">PIC IT</dt>
                        <dd class="col-sm-7 small mt-3">
                            @if ($ticket->assignee)
                                <strong>{{ $ticket->assignee->nama }}</strong>
                            @else
                                <span class="text-muted">Belum ada</span>
                                @if ($ticket->isActive())
                                    <br />
                                    <button type="button" class="btn btn-xs btn-primary mt-1" wire:click="assignToMe">Assign ke saya</button>
                                @endif
                            @endif
                        </dd>
                    </dl>
                </x-slot>
            </x-card>
        </div>
    </x-row>
</div>
