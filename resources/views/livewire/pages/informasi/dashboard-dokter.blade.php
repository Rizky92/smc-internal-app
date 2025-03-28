<div class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        @foreach($collection as $index => $item)
            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $item->nm_dokter }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm text-sm text-nowrap table-striped table-borderless">
                                <thead>
                                    <th>Hari</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                </thead>
                                <tbody>
                                    @foreach($item->jadwal as $schedule)
                                        <tr>
                                            <td>{{ $schedule->hari_kerja }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>