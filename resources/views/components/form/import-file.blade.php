<div class="form-group mt-3">
    <label for="{{ $id ?? 'upload' }}">{{ $label ?? 'Upload File' }}</label>
    <input
        type="file"
        id="{{ $id ?? 'upload' }}"
        @if(!empty($model)) wire:model="{{ $model }}" @endif
        class="form-control-file"
        accept="{{ $accept ?? '.xlsx, .xls' }}"
        {{ $attributes }} />
    @if (! empty($template))
        <a href="{{ asset($template) }}" download class="btn btn-link btn-sm mt-2">{{ $templateLabel ?? 'Template Import' }}</a>
    @endif

    @if (! empty($error))
        <x-form.error :name="$error" />
    @endif
</div>
