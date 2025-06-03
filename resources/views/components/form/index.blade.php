@props([
    'livewire' => false,
    'submit' => null,
    'action' => null,
    'method' => 'POST',
    'files' => false,
    'autocomplete' => false,
])

@php
    $methodHTML = 'POST';

    if (Str::upper($method) === 'GET') {
        $methodHTML = 'GET';
    }
@endphp

<form
    {{
        $attributes
            ->merge(['method' => $methodHTML, 'autocomplete' => $autocomplete ? 'on' : 'off'])
            ->when($files, fn ($attr) => $attr->merge(['enctype' => 'multipart/form-data']))
            ->when($livewire, fn ($attr) => $attr->merge(['wire:submit.prevent' => $submit]))
            ->when(! $livewire && ! is_null($action), fn ($attr) => $attr->merge(['action' => $action]))
    }}>
    @csrf
    @if (! $livewire)
        @method($method)
    @endif

    {{ $slot }}
</form>
