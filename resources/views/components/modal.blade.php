@props([
    'livewire' => false,
    'id',
    'title' => '',
    'size' => 'lg',
    'scrollable' => true,
    'dismissable' => true,
    'centered' => false,

    'header' => null,
    'body' => null,
    'footer' => null,
])

@php
    $sizes = ['sm', 'lg', 'xl'];

    $finalClass = [];

    if (in_array($size, $sizes) || $size !== 'default') {
        $finalClass = array_merge(['modal-' . $size], $finalClass);
    }

    if ($centered) {
        $finalClass = array_merge(['modal-dialog-centered'], $finalClass);
    }

    if ($scrollable) {
        $finalClass = array_merge(['modal-dialog-scrollable'], $finalClass);
    }

    $finalClass = collect($finalClass)->join(' ');
@endphp

@once
    @push('js')
        <script>
            $('.modal#{{ $id }}').on('show.bs.modal', e => {
                $('.modal#{{ $id }}').modal('handleUpdate')
            })
        </script>
    @endpush
@endonce

<div class="modal fade" id="{{ $id }}" {{ $livewire ? 'wire:ignore.self' : null }}>
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
