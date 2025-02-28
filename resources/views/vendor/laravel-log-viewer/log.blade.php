<x-base-layout title="Log Viewer">
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
                    $('#logviewer-table tr').click((e) => {
                        let asd = e.currentTarget.querySelector('.stack');
                        let qwe = e.currentTarget.querySelector('p.title');

                        asd.style['display'] = asd.style['display'] === 'none' ? 'block' : 'none';

                        qwe.style['max-height'] = asd.style['display'] === 'none' ? '50px' : '100%';

                        qwe.style['overflow'] = asd.style['display'] === 'none' ? 'hidden' : 'auto';
                    });

                    $('#logviewer-table').DataTable({
                        order: [$('#logviewer-table').data('orderingIndex'), 'desc'],
                        stateSave: true,
                        stateSaveCallback: (settings, data) => {
                            window.localStorage.setItem('datatable', JSON.stringify(data));
                        },
                        stateLoadCallback: (settings) => {
                            var data = JSON.parse(window.localStorage.getItem('datatable'));
                            if (data) data.start = 0;

                            return data;
                        },
                    });
                });
            </script>
        @endpush
    @endonce

    <x-card>
        <x-slot name="header"></x-slot>
        <x-slot name="body">
            @if ($logs === null)
                <p>Log file exceeds configured limit ({{ config('logviewer.max_file_size') / 1024 / 1024 }} MB)!</p>
            @endif

            <div class="p-3 table-responsive">
                <table id="logviewer-table" class="table table-hover table-sm text-sm" data-ordering-index="{{ $standardFormat ? 2 : 0 }}">
                    <thead>
                        <tr>
                            @if ($standardFormat)
                                <th style="width: 8ch">Level</th>
                                <th style="width: 10ch">Context</th>
                                <th style="width: 15ch">Date</th>
                            @else
                                <th>Line number</th>
                            @endif
                            <th>Content</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $key => $log)
                            <tr data-display="stack{{ $key }}" style="cursor: pointer">
                                @if ($standardFormat)
                                    <td class="nowrap text-{{ $log['level_class'] }}">
                                        <i class="fas fa-{{ $log['level_img'] }}"></i>
                                        <span class="ml-1">
                                            {{ $log['level'] }}
                                        </span>
                                    </td>
                                    <td class="text">{{ $log['context'] }}</td>
                                @endif

                                <td class="date">{{ $log['date'] }}</td>
                                <td class="text">
                                    <p class="title m-0 p-0" style="max-height: 50px; overflow: hidden">
                                        {{ $log['text'] }}
                                    </p>
                                    @if (isset($log['in_file']))
                                        <br />
                                        {{ $log['in_file'] }}
                                    @endif

                                    @if ($log['stack'])
                                        <div class="stack text-xs" id="stack{{ $key }}" style="display: none; white-space: pre-wrap; font-family: monospace">
                                            {{ trim($log['stack']) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot>
        <x-slot name="footer">
            @if ($current_file)
                <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
                    <i class="fas fa-download"></i>
                    <span class="ml-1">Download</span>
                </a>

                <span class="px-2">&bull;</span>

                <a
                    id="clean-log"
                    href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
                    <i class="fas fa-sync"></i>
                    <span class="ml-1">Clean</span>
                </a>

                <span class="px-2">&bull;</span>

                <a
                    id="delete-log"
                    href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
                    <i class="far fa-trash-alt"></i>
                    <span class="ml-1">Delete</span>
                </a>

                @if (count($files) > 1)
                    <span class="px-2">&bull;</span>

                    <a id="delete-all-log" href="?delall=true{{ $current_folder ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ml-1">Delete All</span>
                    </a>
                @endif
            @endif
        </x-slot>
    </x-card>
</x-base-layout>
