@props([
    'columns' => null,
    'body' => null,
    'footer' => null,

    'sortable' => false,
    'sortColumns' => [],
])

<table {{ $attributes->merge(['class' => 'table table-hover table-striped table-head-fixed table-sm text-sm']) }}>
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
