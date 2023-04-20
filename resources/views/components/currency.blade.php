@props(['name' => 'Rp.', 'value' => 0, 'default' => null])

<div class="d-flex justify-content-between">
    <span>{{ $name }}</span>
    <span>{{ $default ?? number_format($value, 0, ',', '.') }}</span>
</div>