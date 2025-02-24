<x-base-layout title="Dashboard">
    <x-flash />

    <div class="px-5 mt-3">
        <div class="row">
            <div class="col-12">
                <h5 class="font-weight-normal">
                    Selamat Datang,
                    <span class="font-weight-bold">{{ str(user()->nama)->title() }}!</span>
                </h5>
            </div>
        </div>
    </div>
</x-base-layout>
