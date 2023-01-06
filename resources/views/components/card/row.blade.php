<div {{ $attributes->merge(['class' => 'row']) }}>
    <div class="col-12">
        <div class="d-flex justify-content-start align-items-center">
            {{ $slot }}
        </div>
    </div>
</div>
