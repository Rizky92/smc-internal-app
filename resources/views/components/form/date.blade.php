@props([
    'model',
])

<input
    {{
        $attributes
            ->merge([
                'class' => 'form-control form-control-sm',
                'type' => 'date',
                'style' => 'width: 9rem',
            ])
            ->when($model, fn ($attr) => $attr->merge(['wire:model.defer' => $model]))
    }} />
