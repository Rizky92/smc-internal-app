@props([
    'columns' => null,
    'body' => null,
    'footer' => null,
    'responsive' => true,
])

<table {{ $attributes->merge(['class' => 'table table-hover table-striped table-sm text-sm']) }}>
    <thead>
        <tr {{ $columns->attributes }}>
            {{ $columns }}
        </tr>
    </thead>
    <tbody {{ $body->attributes }}>
        {{ $body }}
    </tbody>
    <tfoot {{ optional($footer)->attributes }}>
        {{ $footer }}
    </tfoot>
</table>
