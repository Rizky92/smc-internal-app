@props([
    'livewire' => false,
    'id',
    'title' => '',
    'size' => 'lg',
    'centered' => false,
    'scrollable' => true,
    'dismissable' => true,
    'static' => false,
    'header' => null,
    'body' => null,
    'footer' => null,
])

@php
    $sizes = [
        'default' => null,
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
    ];

    $finalClass = collect()
        ->merge($sizes[$size])
        ->when($centered, fn ($c) => $c->merge('modal-dialog-centered'))
        ->when($scrollable, fn ($c) => $c->merge('modal-dialog-scrollable'))
        ->join(' ');
@endphp

@push('js')
    <script>
        $('.modal#{{ $id }}').on('show.bs.modal', (e) => {
            $('.modal#{{ $id }}').modal('handleUpdate');
        });
    </script>
@endpush

<div class="modal fade" id="{{ $id }}" {{ $livewire ? 'wire:ignore.self' : null }} {{ $static ? 'data-backdrop=static' : null }}>
    <div class="modal-dialog {{ $finalClass }}">
        <div {{ $attributes->merge(['class' => 'modal-content']) }}>
            @if ($header || $title)
                <div class="modal-header">
                    <h4 class="modal-title">{{ $title }}</h4>
                    {{ $header }}
                    @if ($dismissable)
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">&times</span>
                        </button>
                    @endif
                </div>
            @endif

            <div {{ $body->attributes->merge(['class' => 'modal-body']) }}>
                {{ $body }}
            </div>

            @if ($footer)
                <div {{ $footer->attributes->merge(['class' => 'modal-footer']) }}>
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
