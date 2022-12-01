@extends('layouts.admin', [
    'title' => 'Penggunaan Obat Per Dokter Peresep',
])

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('penggunaan-obat-per-dokter-peresep')
        </div>
    </div>
@endsection
