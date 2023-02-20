<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    <x-table.td-empty :attributes="$attributes->merge(['padding' => true])->only(['colspan', 'padding'])" />
</tr>