@props([
    'colspan',
    'padding' => false,
    'text' => 'Tidak ada yang dapat ditampilkan saat ini',
])

<td {{ $attributes->class(['text-muted text-center', 'px-3 py-4' => $padding])->merge(compact('colspan')) }}>
    @unless (empty($text))
        {{ $text }}
    @else
        &nbsp;
    @endunless
</td>
