@props([
    'columns' => null,
    'body' => null,
    'responsive' => true,
])

<div {{ $attributes->merge(['class' => $responsive ? 'table-responsive' : null]) }}>
    <table {{ $attributes->merge(['class' => 'table table-hover table-head-fixed table-striped table-sm text-sm']) }}>
        <thead>
            <tr {{ $column->attributes }}>
                {{ $columns }}
            </tr>
        </thead>
        <tbody {{ $body->attributes }}>
            {{ $body }}
        </tbody>
    </table>
</div>
