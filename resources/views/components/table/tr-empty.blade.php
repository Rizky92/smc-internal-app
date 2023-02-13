@props(['colspan'])

<tr {{ $attributes->merge(['class' => 'position-relative']) }}>
    <td colspan="{{ $colspan }}">
        <p class="p-3 text-muted text-center">Tidak ada data yang dapat ditampilkan saat ini.</p>
    </td>
</tr>