@extends('layouts.admin', [
    'title' => 'Laporan Statistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('laporan-statistik')
        </div>
    </div>
@endsection
