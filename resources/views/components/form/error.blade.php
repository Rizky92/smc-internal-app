@props([
    'name',
])

@error($name)
    <div class="text-danger text-xs">{{ $message }}</div>
@enderror
