@props([
    'columns' => null,
    'body' => null,
    'footer' => null,
    'sortable' => false,
    'sortColumns' => [],
    'zebra' => false,
    'hover' => false,
    'sticky' => false,
    'borderless' => false,
    'nowrap' => false,
])

@once
    @push('css')
        <style>
            .table {
                min-width: 100% !important;
                margin-bottom: 0 !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }

            .table.table-foot-fixed tfoot tr:nth-child(1) th {
                background-color: #fff;
                border-top: 0;
                position: -webkit-sticky;
                position: sticky;
                bottom: 0;
                z-index: 10;
            }
        </style>
    @endpush
@endonce

<div class="table-responsive">
    <table
        {{
            $attributes->class([
                'table table-sm text-sm' => true,
                'text-nowrap' => $nowrap,
                'table-hover' => $hover,
                'table-striped' => $zebra,
                'table-head-fixed table-foot-fixed' => $sticky,
                'table-borderless' => $borderless,
            ])
        }}>
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
</div>
