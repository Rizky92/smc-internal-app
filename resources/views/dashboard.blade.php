<x-base-layout title="Dashboard">
    <x-flash />

    <div class="px-5 mt-3">
        <div class="row">
            <div class="col-12">
                <h5 class="font-weight-normal">Selamat Datang, <span class="font-weight-bold">{{ Str::of(auth()->user()->nama)->title() }}!</span></h5>
            </div>
        </div>
        <div class="row"></div>
        <div class="row mt-3">
            <div class="col-12">
                <p>Menu tersedia:</p>
                <ul>
                    <li>Perawatan</li>
                    <li>Keuangan</li>
                    <li>Farmasi</li>
                    <li>Rekam Medis</li>
                    <li>Logistik</li>
                    <li>Admin</li>
                </ul>
            </div>
        </div>
    </div>

</x-base-layout>