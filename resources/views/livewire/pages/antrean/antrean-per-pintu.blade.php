<div>
    <div class="container-fluid">
        <div class="row p-2">
            <div class="col-5 text-center">
                <livewire:pages.antrean.antrean-di-panggil
                    :kd_pintu="$this->kd_pintu"
                />
                <livewire:pages.antrean.list-dokter
                    :kd_pintu="$this->kd_pintu"
                />
            </div>
            <livewire:pages.antrean.list-antrean :kd_pintu="$this->kd_pintu" />
        </div>
    </div>
</div>
