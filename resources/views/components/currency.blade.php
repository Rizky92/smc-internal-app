@props(['name' => 'Rp.', 'value' => 0, 'decimal' => 0, 'default' => null])

<div class="d-flex justify-content-between">
    <span>{{ $name }}</span>
    <span>
        {{ is_null($default) ? number_format($value, $decimal, ',', '.') : $default }}
    </span>
</div>
