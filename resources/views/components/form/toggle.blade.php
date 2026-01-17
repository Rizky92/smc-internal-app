@props([
    'id' => null,
    'label' => null,
    'model' => null,
    'checked' => null,
    'theme' => 'primary',
    'size' => 'sm',
])

@php
    $id = $id ?? 'toggle-' . uniqid();
@endphp

<div class="form-group d-flex align-items-center m-0">
    @if ($label)
        <label for="{{ $id }}" class="m-0 mr-2 font-weight-normal">{{ $label }}</label>
    @endif

    <div class="custom-control custom-switch">
        @php
            $inputAttrs = ['class' => 'custom-control-input'];
            if (! empty($model)) {
                $inputAttrs['wire:model.defer'] = $model;
            }
        @endphp

        <input type="checkbox" id="{{ $id }}" value="1" {{ $attributes->merge($inputAttrs) }} @if(!is_null($checked) && $checked) checked @endif />
        <label class="custom-control-label" for="{{ $id }}"></label>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:load', function () {
            document.querySelectorAll('.custom-control-input').forEach(function (el) {
                el.addEventListener('change', function () {
                    // Optionally update visible labels (if you want dynamic on/off text)
                    var parent = el.closest('.form-group');
                    if (!parent) return;
                    var on = parent.querySelector('.toggle-on');
                    var off = parent.querySelector('.toggle-off');
                    if (el.checked) {
                        if (on) on.classList.remove('d-none');
                        if (off) off.classList.add('d-none');
                    } else {
                        if (on) on.classList.add('d-none');
                        if (off) off.classList.remove('d-none');
                    }
                });

                // Initialize labels state
                el.dispatchEvent(new Event('change'));
            });
        });
    </script>
@endpush
