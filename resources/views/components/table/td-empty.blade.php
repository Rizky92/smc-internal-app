@props(['colspan', 'padding' => false])

<td {{ $attributes->class(['text-muted text-center', 'px-3 py-4' => $padding])->merge(compact('colspan')) }}>
    Tidak ada data yang dapat ditampilkan saat ini.
</td>
