<div>
    <header class="d-flex flex-wrap justify-content-center py-2 pb-2 mb-4 border-bottom shadow header">
        <div class="container-fluid d-flex justify-content-center">
            <h1 class="text-uppercase text-success">{{ \App\Models\Aplikasi\Pintu::where('kd_pintu', $this->kd_pintu)->first()->nm_pintu }}</h1>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <livewire:pages.antrean.antrean-di-panggil :kd_pintu="$this->kd_pintu" />
                <livewire:pages.antrean.list-dokter :kd_pintu="$this->kd_pintu" />
            </div>
            <livewire:pages.antrean.list-antrean :kd_pintu="$this->kd_pintu" />
        </div>
    </div>
</div>