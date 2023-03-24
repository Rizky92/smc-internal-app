@props([
    'columns' => null,
    'body' => null,
    'footer' => null,

    'sortable' => false,
    'sortColumns' => [],

    'striped' => true,
    'hover' => true,
])

<table {{ $attributes->class(['table table-head-fixed table-sm text-sm text-nowrap', 'table-hover' => $hover, 'table-striped' => $striped]) }}>
    <thead>
        <tr {{ $columns->attributes }}>
            {{ $columns }}
        </tr>
    </thead>
    <tbody {{ $body->attributes }}>
        {{ $body }}
    </tbody>
    @if ($footer)
        <tfoot {{ $footer->attributes }}>
            {{ $footer }}
        </tfoot>
    @endif
</table>
