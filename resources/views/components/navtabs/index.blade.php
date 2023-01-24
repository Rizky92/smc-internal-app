@props([
    'livewire' => false,
    'tabs' => null,
    'contents' => null,
])

<div>
    <ul class="nav nav-pills justify-content-center border-bottom-0 px-3" role="tablist" style="gap: 0.5rem">
        {{ $tabs }}
    </ul>
    <div class="tab-content">
        {{ $contents }}
    </div>
</div>
