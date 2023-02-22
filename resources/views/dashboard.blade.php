<x-base-layout title="Dashboard">
    <x-flash />

    {{-- <div class="px-5 mt-3">
        <div class="row">
            <div class="col-12">
                <h5 class="font-weight-normal">Selamat Datang, <span class="font-weight-bold">{{ Str::of(auth()->user()->nama)->title() }}!</span></h5>
            </div>
        </div>
    </div> --}}

    <div class="timeline">

        <div class="time-label">
            <span class="bg-white text-dark text-sm px-3 border font-weight-bold">10 Feb. 2014</span>
        </div>

        @for ($i = 0; $i < 10; $i++)    
            <div class="{{ Arr::toCssClasses(['mt-3' => $i === 0]) }}">
                <i class="fas fa-angle-right bg-dark"></i>
                <div class="timeline-item border-0 shadow-none bg-transparent" style="margin-top: -0.2rem">
                    <div class="timeline-body mt-n1 d-flex justify-content-start align-items-start">
                        <span class="badge badge-secondary text-xs" style="margin-inline-end: 1.25rem">12:05</span>
                        <div class="mt-n1">
                            <h6 style="margin-top: 0.2rem; margin-bottom: 0.25rem">
                                Mengunjungi <a href="#">Jurnal PO Supplier</a>    
                            </h6>
                            <ul class="nav justify-content-start" style="row-gap: 2rem">
                                <li class="mr-1 nav-item">Dashboard</li>
                                <li class="mx-1 nav-item">/</li>
                                <li class="mx-1 nav-item">Keuangan</li>
                                <li class="mx-1 nav-item">/</li>
                                <li class="ml-1 nav-item active">Jurnal PO Supplier</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endfor

        <div>
            <i class="fas fa-clock fa-fw bg-gray"></i>
        </div>
    </div>
</x-base-layout>
