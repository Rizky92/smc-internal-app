<div class="row mt-2">
    <div class="col">
        <div class="card card-outline card-success h-100">
            <div class="card-header">
                <h5 class="text-uppercase">List Dokter</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column">
                    <table width="100%" class="table table-sm text-sm table-bordered table-striped">
                        @forelse ($this->listDokter as $item)
                            <tr>
                                <td align="left">{{ $item->nm_dokter }}</td>
                                <td style="width: 15ch">
                                    {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted p-4">Tidak ada yang dapat ditampilkan saat ini</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
