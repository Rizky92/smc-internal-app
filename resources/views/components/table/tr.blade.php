@push('css')
    <style>
        @media print {
            tr {
                font-size: 0.70em;
            }
        }
    </style>
@endpush

<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    {{ $slot }}
</tr>