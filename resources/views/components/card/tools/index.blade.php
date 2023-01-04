<div {{ $attributes->merge(['class' => 'row']) }}>
    <div class="col-12">
        <div class="d-flex align-items-center">
            {{ $slot }}
        </div>
    </div>
</div>
