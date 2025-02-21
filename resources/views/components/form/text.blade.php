@props([
    'model',
])

<input
    {{
        $attributes
            ->merge([
                'class' => 'form-control form-control-sm',
                'type' => 'text',
                'style' => 'width: 20rem',
            ])
            ->when($model, fn ($attr) => $attr->merge(['wire:model.defer' => $model]))
    }} />
