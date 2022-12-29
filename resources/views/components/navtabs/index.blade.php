@props([
    'livewire' => false,
    'tabs' => null,
    'contents' => null,
])

<div>
    <ul class="nav nav-tabs nav-fill border-bottom-0" role="tablist">
        {{ $tabs }}
    </ul>
    <div class="tab-content">
        {{ $contents }}
    </div>
</div>
