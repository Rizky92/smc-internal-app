<div wire:init="loadProperties">
    <x-flash />

    <livewire:pages.keuangan.modal.ubah-tanggal-jurnal />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 110rem" sortable sticky nowrap>
                <x-slot name="columns">
                    @can('keuangan.jurnal-perbaikan.ubah-tanggal')
                        <x-table.th style="width: 8ch" title="#" />
                    @endcan

                    <x-table.th name="no_jurnal" style="width: 15ch" title="No. Jurnal" />
                    <x-table.th name="no_bukti" style="width: 18ch" title="No. Bukti" />
                    <x-table.th name="waktu_jurnal" style="width: 17ch" title="Waktu Jurnal" />
                    <x-table.th name="keterangan" title="Keterangan" />
                    <x-table.th style="width: 11ch" title="Kode Akun" />
                    <x-table.th title="Nama Akun" />
                    <x-table.th style="width: 16ch" title="Debet" />
                    <x-table.th style="width: 16ch" title="Kredit" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->jurnal as $jurnal)
                        @php
                            $odd = $loop->iteration % 2 === 0 ? '255 255 255' : '247 247 247';
                            $count = $jurnal->detail->count();
                            $firstDetail = $jurnal->detail->first();
                        @endphp

                        <x-table.tr style="background-color: rgb({{ $odd }})">
                            @can('keuangan.jurnal-perbaikan.ubah-tanggal')
                                <x-table.td rowspan="{{ $count }}" class="pl-3 py-1">
                                    <x-button
                                        size="xs"
                                        variant="link"
                                        class="mt-n1"
                                        title="Edit"
                                        icon="fas fa-pencil-alt"
                                        id="edit-{{ $jurnal->no_jurnal }}"
                                        data-toggle="modal"
                                        data-target="#modal-ubah-tgl-jurnal"
                                        wire:click.prevent="$emit('utj.prepare', {
                                        noJurnal: '{{ $jurnal->no_jurnal }}',
                                        noBukti: '{{ $jurnal->no_bukti }}',
                                        keterangan: '{{ $jurnal->keterangan }}',
                                        tglJurnal: '{{ $jurnal->tgl_jurnal }}',
                                        jamJurnal: '{{ $jurnal->jam_jurnal }}'
                                    })" />
                                </x-table.td>
                            @endcan

                            <x-table.td rowspan="{{ $count }}">
                                {{ $jurnal->no_jurnal }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $jurnal->no_bukti }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $jurnal->tgl_jurnal }}
                                {{ $jurnal->jam_jurnal }}
                            </x-table.td>
                            <x-table.td rowspan="{{ $count }}">
                                {{ $jurnal->keterangan }}
                            </x-table.td>
                            <x-table.td class="p-1">
                                {{ optional($firstDetail)->kd_rek }}
                            </x-table.td>
                            <x-table.td>
                                @if (optional($firstDetail)->kredit > 0)
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                @endif

                                {{ optional(optional($firstDetail)->rekening)->nm_rek }}
                            </x-table.td>
                            <x-table.td>
                                {{ optional($firstDetail)->debet > 0 ? rp(optional($firstDetail)->debet) : null }}
                            </x-table.td>
                            <x-table.td>
                                {{ optional($firstDetail)->kredit > 0 ? rp(optional($firstDetail)->kredit) : null }}
                            </x-table.td>
                        </x-table.tr>
                        @if ($jurnal->detail->skip(1)->count() > 0)
                            @foreach ($jurnal->detail->skip(1) as $detail)
                                <x-table.tr style="background-color: rgb({{ $odd }})">
                                    <x-table.td class="p-1 border-0">
                                        {{ $detail->kd_rek }}
                                    </x-table.td>
                                    <x-table.td class="border-0">
                                        @if ($detail->kredit > 0)
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        @endif

                                        {{ optional($detail->rekening)->nm_rek }}
                                    </x-table.td>
                                    <x-table.td class="border-0">
                                        {{ $detail->debet > 0 ? rp($detail->debet) : null }}
                                    </x-table.td>
                                    <x-table.td class="border-0">
                                        {{ $detail->kredit > 0 ? rp($detail->kredit) : null }}
                                    </x-table.td>
                                </x-table.tr>
                            @endforeach
                        @endif
                    @empty
                        <x-table.tr-empty :colspan="auth()->user() ->can('keuangan.jurnal-perbaikan.ubah-tanggal') ? 8 : 7" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->jurnal" />
        </x-slot>
    </x-card>
</div>
