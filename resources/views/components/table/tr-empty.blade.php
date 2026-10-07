<tr {{ $attributes->except(['colspan', 'padding', 'text'])->merge(['class' => 'position-relative']) }}>
    <x-table.td-empty :attributes="$attributes->only(['colspan', 'padding', 'text'])" />
</tr>
