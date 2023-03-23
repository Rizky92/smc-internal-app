<div>
    <x-flash />

    <livewire:keuangan.modal.ubah-tanggal-jurnal />

    <x-card use-loading wire:init="loadProperties">
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%; width: 110rem" :striped="false" :hover="false">
                <x-slot name="columns">
                    <x-table.th style="width: 8ch" title="#" />
                    <x-table.th name="no_jurnal" style="width: 15ch" title="No. Jurnal" />
                    <x-table.th name="no_bukti" style="width: 18ch" title="No. Bukti" />
                    <x-table.th name="waktu_jurnal" style="width: 17ch" title="Waktu Jurnal" />
                    <x-table.th name="keterangan" style="width: 60ch" title="Keterangan" />
                    <x-table.th style="width: 11ch" title="Kode Akun" />
                    <x-table.th title="Nama Akun" />
                    <x-table.th style="width: 16ch" title="Debet" />
                    <x-table.th style="width: 16ch" title="Kredit" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->jurnal as $jurnal)
                        <x-table.tr>
                            @php
                                $count = $jurnal->detail->count();
                                $firstDetail = $jurnal->detail->first();
                            @endphp
                            <x-table.td rowspan="{{ $count }}" class="pl-3 py-1">
                                <x-button
                                    size="xs" variant="link" class="mt-n1"
                                    title="Edit" icon="fas fa-pencil-alt" id="edit-{{ $jurnal->no_jurnal }}"
                                    data-toggle="modal" data-target="#modal-ubah-tgl-jurnal"
                                    wire:click.prevent="$emit('utj.prepare', {
                                        noJurnal: '{{ $jurnal->no_jurnal }}',
                                        noBukti: '{{ $jurnal->no_bukti }}',
                                        keterangan: '{{ $jurnal->keterangan }}',
                                        tglJurnal: '{{ $jurnal->tgl_jurnal }}',
                                        jamJurnal: '{{ $jurnal->jam_jurnal }}'
                                    })" />
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $jurnal->no_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $jurnal->no_bukti }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $jurnal->tgl_jurnal }} {{ $jurnal->jam_jurnal }}</x-table.td>
                            <x-table.td rowspan="{{ $count }}">{{ $jurnal->keterangan }}</x-table.td>
                            <x-table.td class="p-1">{{ optional($firstDetail)->kd_rek }}</x-table.td>
                            <x-table.td>
                                @if (optional($firstDetail)->kredit > 0)
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                @endif {{ optional(optional($firstDetail)->rekening)->nm_rek }}
                            </x-table.td>
                            <x-table.td>{{ optional($firstDetail)->debet > 0 ? rp(optional($firstDetail)->debet) : null }}</x-table.td>
                            <x-table.td>{{ optional($firstDetail)->kredit > 0 ? rp(optional($firstDetail)->kredit) : null }}</x-table.td>
                        </x-table.tr>
                        @if ($jurnal->detail->skip(1)->count() > 0)
                            @foreach ($jurnal->detail->skip(1) as $detail)
                                <x-table.tr>
                                    <x-table.td class="p-1 border-0">{{ $detail->kd_rek }}</x-table.td>
                                    <x-table.td class="border-0">
                                        @if ($detail->kredit > 0)
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif {{ optional($detail->rekening)->nm_rek }}
                                    </x-table.td>
                                    <x-table.td class="border-0">{{ $detail->debet > 0 ? rp($detail->debet) : null }}</x-table.td>
                                    <x-table.td class="border-0">{{ $detail->kredit > 0 ? rp($detail->kredit) : null }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty colspan="8" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->jurnal" />
        </x-slot>
    </x-card>
</div>
