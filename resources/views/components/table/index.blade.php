@props([
    'columns' => null,
    'body' => null,
    'responsive' => true,
])

<table {{ $attributes->merge(['class' => 'table table-hover table-head-fixed table-striped table-sm text-sm']) }}>
    <thead>
        <tr {{ $columns->attributes }}>
            {{ $columns }}
        </tr>
    </thead>
    <tbody {{ $body->attributes }}>
        {{ $body }}
    </tbody>
</table>
