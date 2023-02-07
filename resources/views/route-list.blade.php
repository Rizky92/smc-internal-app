<x-base-layout title="Route list">
    @once
        @push('css')
            <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
            <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
            <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
        @endpush
        @push('js')
            <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
            <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.js') }}"></script>
            <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
            <script>
                $(document).ready(function() {
                    $('#routes-table').DataTable();
                });
            </script>
        @endpush
    @endonce
    <x-card>
        <x-slot name="body">
            <div class="table-responsive p-3">
                <table class="table table-sm table-bordered table-hover" id="routes-table">
                    <thead>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Methods</th>
                        @if (config('infyom.routes_explorer.collections.api_calls_count'))
                            <th>Count</th>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($routes as $route)
                            <tr>
                                <td>{!! $route['name'] !!}</td>
                                <td>{!! $route['url'] !!}</td>
                                <td>{!! $route['methods'] !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-card>
</x-base-layout>