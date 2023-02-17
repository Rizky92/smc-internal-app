@props([
    'button',
    'menu',
    'livewire' => false,
])

@if ($livewire)    
    @once
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
    @endonce
@endif


<div {{ $attributes->merge(['class' => 'dropdown mb-3']) }} wire:ignore>
    <x-button :attributes="$button->attributes->merge(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'])" />
    <div {{ $menu->attributes->merge(['class' => 'dropdown-menu']) }}>
        {{ $menu }}
    </div>
</div>
