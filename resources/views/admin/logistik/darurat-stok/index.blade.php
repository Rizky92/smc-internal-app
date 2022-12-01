@extends('layouts.admin', [
    'title' => 'Darurat Stok Barang Logistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('darurat-stok-logistik')
        </div>
    </div>
@endsection
