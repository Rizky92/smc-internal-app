@extends('layouts.admin', [
    'title' => 'Manajemen User',
])

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('manajemen-user')
        </div>
    </div>
@endsection
