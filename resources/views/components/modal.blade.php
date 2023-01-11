@props([
    'livewire' => false,
    'id',
    'title' => '',
    'size' => 'lg',
    'scrollable' => true,
    'dismissable' => true,

    'header' => null,
    'body' => null,
    'footer' => null,
])

@php
    $sizes = ['sm', 'DEFAULT', 'lg', 'xl'];

    $finalClass = '';
    $sizeClass = null;
    $scrollableClass = null;

    if (in_array($size, $sizes)) {
        $sizeClass = 'modal-' . $size;
        $finalClass .= $sizeClass;
    }

    $finalClass .= ' ';

    if ($scrollable) {
        $scrollableClass = 'modal-dialog-scrollable';
        $finalClass .= $scrollableClass;
    }
@endphp

<div class="modal fade" id="{{ $id }}" {{ $livewire ? 'wire:ignore.self' : null }}>
    <div class="modal-dialog {{ $finalClass }}">
        <div {{ $attributes->merge(['class' => 'modal-content']) }}>

            <div {{ $header->attributes->merge(['class' => 'modal-header']) }}>
                <h4 class="modal-title">{{ $title }}</h4>
                {{ $header }}
                @if ($dismissable)
                    <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                        <span aria-hidden="true">&times</span>
                    </button>
                @endif
            </div>

            <div {{ $body->attributes->merge(['class' => 'modal-body']) }}>
                {{ $body ?? $slot }}
            </div>
            
            @if ($footer)
                <div {{ $footer->attributes->merge(['class' => 'modal-footer']) }}>
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
