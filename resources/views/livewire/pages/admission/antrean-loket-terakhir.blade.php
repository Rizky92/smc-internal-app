@php
    $prefixes = ['A', 'B', 'C', 'D', 'E', 'F'];
@endphp

<div wire:poll.5000ms class="row g-3 mt-1">
    @foreach ($prefixes as $p)
        <div class="col-lg-4">
            <div class="card card-outline card-success" style="height: 10vh">
                <div class="card-body text-center p-2 justify-content-center d-flex align-items-center">
                    <h1 class="m-0 text-success" style="font-size: 2rem">
                        {{ optional($this->antreanTerakhir->firstWhere('prefix', $p))->nomor ?? $p . '000' }}
                    </h1>
                </div>
            </div>
        </div>
    @endforeach
</div>
