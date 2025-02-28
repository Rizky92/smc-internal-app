<div class="container">
    <h1 class="text-center p-4">ANTREAN POLIKLINIK</h1>
    <div class="row">
        @foreach ($this->pintu as $item)
            <div class="col-3">
                <a href="{{ route('antrean-per-pintu', ['kd_pintu' => $item->kd_pintu]) }}" class="text-uppercase text-decoration-none text-white">
                    <div class="card text-white bg-gradient-success mb-3" style="max-width: 18rem; font-size: 0.75rem">
                        <div class="card-header">
                            {{ $item->nm_pintu }}
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
