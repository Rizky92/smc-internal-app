@once
    @push('css')
        <style>
            @media print {
                tr {
                    font-size: 0.7em;
                }
            }
        </style>
    @endpush
@endonce

<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    {{ $slot }}
</tr>
