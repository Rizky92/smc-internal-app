<div>
    <x-flash />

    <x-row>
        {{-- Ralan --}}
        <div class="col-6">
            <h3 class="mb-3"><i class="fas fa-calendar-alt"></i> Rawat Jalan</h3>
            <x-row>
                <div class="col-6">
                    <x-card>
                        <x-slot name="header">

                        </x-slot>
                        <x-slot name="body">
                            {{-- <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                                <x-slot name="columns">
                                    <x-table.th name="id" title="#" />
                                </x-slot>
                                <x-slot name="body">
                                    
                                    @forelse ($this->collectionProperty as $item)
                                        <x-table.tr>
                                            <x-table.td>{{ $item->id }}</x-table.td>
                                        </x-table.tr>
                                    @empty
                                        <x-table.tr-empty colspan="1" padding />
                                    @endforelse
                                
                                </x-slot>
                            </x-table> --}}
                        </x-slot>
                    </x-card>
                </div>
            
                <div class="col-6">
                    <x-card class=''>
                        <x-slot name="header">
                            {{-- <h3 class="text-lg font-medium text-gray-900">Dashboard MOD</h3> --}}
                        </x-slot>
                        <x-slot name="body"></x-slot>
                    </x-card>
                </div>
            </x-row>
        </div>

        {{-- Ranap --}}
        <div class="col-6">
            <h3 class="mb-3"><i class="fas fa-bed"></i> Rawat Inap</h3>
            <x-row>
                <div class="col-6">
                    <x-card>
                        <x-slot name="header">

                        </x-slot>
                        <x-slot name="body">
                        </x-slot>
                    </x-card>
                </div>
            
                <div class="col-6">
                    <x-card class=''>
                        <x-slot name="header">
                            {{-- <h3 class="text-lg font-medium text-gray-900">Dashboard MOD</h3> --}}
                        </x-slot>
                        <x-slot name="body"></x-slot>
                    </x-card>
                </div>
            </x-row>
        </div>
    </x-row>

    <x-row>
        <div class="col-6">
            <x-row>
                {{-- IGD TRIAGE PROFILE --}}
                <x-card class='col-6 p-8' :table="false">
                    <x-slot name="header">
                        <h5 class="mb-8">IGD TRIAGE PROFILE</h5>
                    </x-slot>
                    <x-slot name="body">
                        <div>
                            <div class="d-flex justify-content-between">
                                <span>Critical (Red)</span>
                                <span>7 pts</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar w-75 bg-danger" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between">
                                <span>Urgent (Yellow)</span>
                                <span>7 pts</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar w-75 bg-warning" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between">
                                <span>Standard (Green)</span>
                                <span>7 pts</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar w-75 bg-success" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between">
                                <span>Deceased (Black)</span>
                                <span>7 pts</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar w-75 bg-dark" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </x-slot>
                </x-card>
                {{-- JAMINAN BREAKDOWN --}}
                <div class="col-6">
                    <x-card class="col-6 p-8" :table="false">
                        <x-slot name="header">
        
                        </x-slot>
                        <x-slot name="body">
        
                        </x-slot>
                    </x-card>
                </div>
            </x-row>
        </div>
        {{-- CAPACITY UTILIZATION MATRIX --}}
        <div class="col-6">
            <x-row>
                <div class="col-12">
                    <x-card>
                        <x-slot name="header">
        
                        </x-slot>
                        <x-slot name="body">
        
                        </x-slot>
                    </x-card>
                </div>
            </x-row>
        </div>
    </x-row>
</div>
