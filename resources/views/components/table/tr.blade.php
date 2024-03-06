@push('css')
    <style>
        @media print {
            tr {
                font-size: 9px;
            }
        }
    </style>
@endpush

<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    {{ $slot }}
</tr>