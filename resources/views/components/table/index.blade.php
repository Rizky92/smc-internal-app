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
    @push('js')
        <script>
            $(document).on('DOMContentLoaded', e => {
                Livewire.on('element.updated', (msg, comp) => {
                    let clientHeight = document.documentElement.clientHeight

                    let navbarHeight = document.querySelector('nav.main-header.navbar')?.clientHeight ?? 0
                    let cardHeaderHeight = document.querySelector('[card-section-header]')?.clientHeight ?? 0
                    let cardFooterHeight = document.querySelector('[card-section-footer]')?.clientHeight ?? 0
                    let paginatorHeight = document.querySelector('[table-paginator]')?.clientHeight ?? 0
                    let titleHeight = document.querySelector('[page-title]')?.clientHeight ?? 0

                    let tableEl = document.querySelector('table.table')
                    let tableHeight = tableEl?.clientHeight ?? 0

                    let adjustedTableHeight = clientHeight - (cardHeaderHeight + paginatorHeight + titleHeight + navbarHeight) - 14

                    let isTableHeaderSticky = tableEl.classList.contains('table-head-fixed')
                    let isTableHeightReachesMinimum = adjustedTableHeight > 300
                    let isTableHeightRequireAdjustment = tableHeight > adjustedTableHeight

                    if (
                        isTableHeaderSticky &&
                        isTableHeightReachesMinimum &&
                        isTableHeightRequireAdjustment
                    ) {
                        $('div.table-responsive').height(adjustedTableHeight)
                    }
                })
            })
        </script>
    @endpush
@endonce

<div class="table-responsive" wire:ignore.self>
    <table {{ $attributes->class([
        'table table-sm text-sm' => true,
        'text-nowrap' => $nowrap,
        'table-hover' => $hover,
        'table-striped' => $zebra,
        'table-head-fixed table-foot-fixed' => $sticky,
        'table-borderless' => $borderless,
    ]) }}>
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
