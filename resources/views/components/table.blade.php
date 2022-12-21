@props([
    'header' => [],
    'data' => [],
])

<table class="table table-hover table-striped table-sm text-sm">
    <thead>
        <tr>
            @foreach ($header as $name => $title)
                <th>{{ $title }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                @foreach ($header as $name => $title)
                    <td>{{ $item->{$name} }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>