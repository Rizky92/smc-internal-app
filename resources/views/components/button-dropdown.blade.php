<div {{ $attributes->class(['btn-group']) }}>
    <x-button :attributes="$attributes->whereStartsWith('action:')" />
</div>
