@props([
    'model' => null,
    'method' => 'POST',
])

@php($postMethods = ['POST', 'PUT', 'PATCH', 'DELETE'])

<form method="{{ in_array(str($method)->upper(), $postMethods) ? 'POST', 'GET' }}" {{ $attributes }}>
    @inarray(str($method)->upper(), $postMethods)
        @csrf
        @method(str($method)->upper())
    @endinarray
    {{ $slot }}
</form>