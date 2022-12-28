@props([
    'hasPaginator' => true,
    'replaceSearch' => null,
])

<div class="col-12">
    <div class="d-flex align-items-center justify-content-start">
        @if ($hasPaginator)
            <div class="d-flex align-items-center">
                <span class="text-sm pr-2">Tampilkan:</span>
                <div class="input-group input-group-sm" style="width: 4rem">
                    <select class="custom-control custom-select" name="perpage" wire:model.defer="perpage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </div>
                <span class="text-sm pl-2">per halaman</span>
            </div>
        @endif
        <button class="btn btn-link text-secondary btn-sm ml-auto" type="button" wire:click="resetFilters">
            Reset Filter
        </button>
        @if (is_null($replaceSearch))
            <div class="ml-2 input-group input-group-sm" style="width: 16rem">
                <input class="form-control" type="search" wire:model.defer="cari" wire:keydown.enter.stop="searchData" />
                <div class="input-group-append">
                    <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
                        <i class="fas fa-search"></i>
                        <span class="ml-1">Cari</span>
                    </button>
                </div>
            </div>
        @else
            {{ $replaceSearch }}
        @endif
    </div>
</div>
