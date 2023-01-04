@props([
    'columns' => null,
    'body' => null,
])

<table {{ $attributes->merge(['class' => 'table table-hover table-head-fixed table-striped table-sm text-sm']) }}>
    <thead>
        <tr>
            {{ $columns }}
        </tr>
    </thead>
    <tbody>
        {{ $body }}
    </tbody>
</table>
