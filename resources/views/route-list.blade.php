<x-base-layout title="Route list">
    @once
        @push('css')
            <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}" />
            <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}" />
        @endpush

        @push('js')
            <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('js/responsive.bootstrap4.min.js') }}"></script>
            <script>
                $(document).ready(function () {
                    $('#routes-table').DataTable();
                });
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header"></x-slot>
        <x-slot name="body">
            <div class="table-responsive p-3">
                <table class="table table-bordered table-hover" id="routes-table">
                    <thead>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Methods</th>
                        @if (config('infyom.routes_explorer.collections.api_calls_count'))
                            <th>Count</th>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($routes as ['url' => $url, 'name' => $name, 'methods' => $methods])
                            <tr>
                                <td class="text-monospace">{{ $name }}</td>
                                <td>{{ $url }}</td>
                                <td class="text-monospace">{{ $methods }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
</x-base-layout>
