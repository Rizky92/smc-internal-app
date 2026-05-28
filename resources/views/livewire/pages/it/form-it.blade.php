<div>
    <x-flash />

    <livewire:pages.it.modal.input-form-it />
    <livewire:pages.it.modal.view-ticket-detail />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.select class="ml-2" style="width: 120px" model="status" :options="$this->statusOptions" placeholder="Status" placeholderValue="" />
                <x-filter.select class="ml-2" style="width: 120px" model="priority" :options="$this->priorityOptions" placeholder="Prioritas" placeholderValue="" />
                @can('form-it.create')
                    <x-button variant="primary" size="sm" title="Tiket Baru" icon="fas fa-plus" data-toggle="modal" data-target="#modal-input-tiket" class="btn-primary ml-auto" />
                @endcan
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="ticket_number" title="No. Tiket" />
                    <x-table.th name="title" title="Judul" />
                    <x-table.th title="Kategori" />
                    <x-table.th title="Departemen" />
                    <x-table.th name="priority" title="Prioritas" />
                    <x-table.th name="status" title="Status" />
                    <x-table.th title="SLA" />
                    <x-table.th title="PIC IT" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr class="cursor-pointer" onclick="window.location='{{ route('admin.form-it.detail', $item->id) }}'">
                            <x-table.td>{{ $item->ticket_number }}</x-table.td>
                            <x-table.td>{{ $item->title }}</x-table.td>
                            <x-table.td>{{ $item->category->name ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->department->nama ?? '-' }}</x-table.td>
                            <x-table.td>
                                <x-ticket.priority-badge :priority="$item->priority" />
                            </x-table.td>
                            <x-table.td>
                                <x-ticket.status-badge :status="$item->status" />
                            </x-table.td>
                            <x-table.td>
                                @php
                                    $slaSeverityClass = match ($item->sla_severity) {
                                        'breach' => 'text-danger font-weight-bold',
                                        'warn' => 'text-warning font-weight-bold',
                                        'ok' => 'text-success font-weight-bold',
                                        default => 'text-muted',
                                    };
                                @endphp

                                <small class="{{ $slaSeverityClass }}">
                                    {{ $item->sla_remaining_label }}
                                </small>
                            </x-table.td>

                            <x-table.td>{{ $item->assignee->nama ?? '-' }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
