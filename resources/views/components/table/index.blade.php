@props([
    'columns' => null,
    'body' => null,
    'footer' => null,

    'sortable' => false,
    'sortColumns' => [],

    'striped' => true,
    'hover' => true,
])

<table {{ $attributes->class(['table table-head-fixed table-sm text-sm', 'table-hover' => $hover, 'table-striped' => $striped]) }}>
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
