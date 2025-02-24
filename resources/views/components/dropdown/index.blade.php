@props([
    'button',
    'menu',
    'livewire' => false,
    'menuPosition' => 'left',
    'split' => false,
])

@php
    $buttonId = Str::slug($button->attributes->get('title'));

    if ($split) {
        $buttonId .= '-dropdown';
    }
@endphp

@if ($livewire)
    @push('js')
        <script>
            $(document).on('DOMContentLoaded', (e) => {
                let buttonComponent = $('button#{{ $buttonId }}');

                buttonComponent.data('toggle', 'dropdown');

                Livewire.hook('element.updating', (from, to, component) => {
                    buttonComponent.dropdown('dispose');
                });

                Livewire.hook('element.updated', (el, component) => {
                    buttonComponent.dropdown();
                });
            });
        </script>
    @endpush
@endif

<div {{ $attributes->merge(['class' => $split ? 'btn-group' : 'dropdown']) }} {{ $livewire ? 'wire:ignore' : null }}>
    @if ($split)
        <x-button :attributes="$button->attributes" />
        {{-- format-ignore-start --}}
        <x-button :attributes="$button->attributes
            ->only(['size', 'variant', 'outline'])
            ->merge(['id' => $buttonId, 'class' => 'dropdown-toggle dropdown-toggle-split', 'data-toggle' => 'dropdown'])"
        />
        {{-- format-ignore-end --}}
    @else
        <x-button :attributes="$button->attributes->merge(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'])" />
    @endif
    <div {{ $menu->attributes->class(['dropdown-menu', 'dropdown-menu-right' => $menuPosition === 'right']) }}>
        {{ $menu }}
    </div>
</div>
