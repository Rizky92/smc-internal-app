@extends('layouts.admin', [
    'title' => 'Laporan Statistik asd asd asd',
])

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('rekam-medis-data-table-component')
        </div>
    </div>
@endsection
