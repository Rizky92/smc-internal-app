@props([
    'button',
    'menu',
    'livewire' => false,
    'menuPosition' => 'left',
])

@if ($livewire)    
    @push('js')
        <script>
            $(document).on('DOMContentLoaded', e => {
                let buttonId = "{{ Str::slug($button->attributes->get('title')) }}"

                let buttonComponent = $(`button#${buttonId}`)

                buttonComponent.data('toggle', 'dropdown')

                Livewire.hook('element.updating', (from, to, component) => {
                    buttonComponent.dropdown('dispose')
                })

                Livewire.hook('element.updated', (el, component) => {
                    buttonComponent.dropdown()
                })
            })
        </script>
    @endpush
@endif


<div {{ $attributes->merge(['class' => 'dropdown']) }} {{ $livewire ? 'wire:ignore' : null }}>
    <x-button :attributes="$button->attributes->merge(['size' => 'sm', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'])" />
    <div {{ $menu->attributes->merge(['class' => 'dropdown-menu']) }}>
        {{ $menu }}
    </div>
</div>
