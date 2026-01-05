@props([
    'id' => null,
    'name' => null,
    'wireModel' => null,
    'options' => [],
    'selected' => [],
    'placeholder' => null,
    'disabled' => false,
    'required' => false,
    'livewireComponent' => null,
    'livewireEvent' => null,
    'livewireSyncEvent' => null,
    'class' => 'form-control form-control-sm',
    'size' => null,
    'allowClear' => true,
])

@php
    $id = $id ?? 'select-' . uniqid();
    $name = $name ?? $id;
    $hasLivewireEvent = ! empty($livewireEvent);
    $hasLivewireSyncEvent = ! empty($livewireSyncEvent);
    $livewireEventName = $livewireComponent && $livewireEvent ? $livewireComponent . '.' . $livewireEvent : $livewireEvent;
    $livewireSyncEventName = $livewireComponent && $livewireSyncEvent ? $livewireComponent . '.' . $livewireSyncEvent : $livewireSyncEvent;
    $forwardedWireModel = null;
    if (! empty($wireModel)) {
        $forwardedWireModel = $wireModel;
    } else {
        $forwardedWireModel = $attributes->get('wire:model') ?? ($attributes->get('wire:model.defer') ?? ($attributes->get('wire:model.lazy') ?? $attributes->get('wire:model.sync')));
    }
@endphp

@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />

        <style>
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: rgb(245, 245, 245);
                color: #1f2d3d;
                font-weight: 700;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: rgb(173, 173, 173);
                float: right;
            }

            .select2-container--default.select2-container--focus .select2-selection--multiple {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }
        </style>
    @endonce
@endpush

@push('js')
    @once
        <script src="{{ asset('js/select2.full.min.js') }}"></script>
    @endonce

    <script>
        document.addEventListener('livewire:load', function () {
            $('.multiple-select-component').each(function () {
                initMultipleSelect($(this));
            });
        });

        document.addEventListener('livewire:update', function () {
            $('.multiple-select-component').each(function () {
                initMultipleSelect($(this));
            });
        });

        function initMultipleSelect($element) {
            var id = $element.attr('id');
            var config = {
                placeholder: $element.data('placeholder') || 'Pilih opsi...',
                allowClear: $element.data('allow-clear') !== false,
                width: '100%',
            };

            if ($element.data('max-selection')) {
                config.maximumSelectionLength = $element.data('max-selection');
            }

            // Clear any existing Select2 instance
            if ($element.hasClass('select2-hidden-accessible')) {
                $element.select2('destroy');
            }

            $element.select2(config);

            var livewireEvent = $element.data('livewire-event');
            if (livewireEvent) {
                $element.on('change', function (e) {
                    var data = $(this).val();
                    Livewire.emit(livewireEvent, data);
                });
            }

            var livewireSyncEvent = $element.data('livewire-sync-event');
            if (livewireSyncEvent) {
                Livewire.on(livewireSyncEvent, function (data) {
                    // Ensure data is an array or null for clearing
                    var selectedValues = Array.isArray(data) ? data : data ? [data] : [];
                    $element.val(selectedValues).trigger('change');
                });
            }

            // If a Livewire model attribute was forwarded to this <select> (e.g. wire:model, wire:model.defer),
            // sanitize it and sync Select2 changes back to Livewire because the <select> is inside wire:ignore.
            (function () {
                var wireModelAttrRaw = $element.data('wire-model');

                if (!wireModelAttrRaw || livewireEvent) {
                    return;
                }

                var wireModelAttr = String(wireModelAttrRaw)
                    .trim()
                    .replace(/^['\"]|['\"]$/g, '');
                // strip any accidental leading dollar signs
                wireModelAttr = wireModelAttr.replace(/^\$+/, '');
                var isValidModelName = /^[a-zA-Z0-9_\.\[\]]+$/.test(wireModelAttr);

                $element.on('change', function () {
                    var data = $(this).val();
                    var $root = $element.closest('[wire\\:id]');
                    console.debug('multiple-select sync', { raw: wireModelAttrRaw, model: wireModelAttr, valid: isValidModelName });
                    if ($root.length && isValidModelName) {
                        var compId = $root.attr('wire:id');
                        try {
                            if (compId && window.Livewire && Livewire.find(compId) && typeof Livewire.find(compId).set === 'function') {
                                Livewire.find(compId).set(wireModelAttr, data);
                                return;
                            }
                        } catch (e) {
                            console.error('Livewire.set failed for', wireModelAttr, e);
                        }
                    }

                    // Fallback: emit an event with model name and value. Component can listen if needed.
                    Livewire.emit('multiple-select.sync', { name: isValidModelName ? wireModelAttr : wireModelAttrRaw, value: data });
                });
            })();
        }
    </script>
@endpush

<div class="form-group">
    <div wire:ignore>
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $attributes->merge(['class' => $class . ' select2 input-sm multiple-select-component']) }}
            @if($forwardedWireModel) data-wire-model="{{ $forwardedWireModel }}" @endif
            multiple
            data-placeholder="{{ $placeholder }}"
            data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
            @if($size) data-max-selection="{{ $size }}" @endif
            @if($livewireEventName) data-livewire-event="{{ $livewireEventName }}" @endif
            @if($livewireSyncEventName) data-livewire-sync-event="{{ $livewireSyncEventName }}" @endif
            @if($disabled) disabled @endif
            @if($required) required @endif>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" {{ in_array($value, (array) ($selected ?? [])) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    @if ($wireModel)
        <x-form.error name="{{ $wireModel }}" />
    @endif
</div>
