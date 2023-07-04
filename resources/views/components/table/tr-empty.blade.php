<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    <x-table.td-empty :attributes="$attributes->only(['colspan', 'padding'])" />
</tr>