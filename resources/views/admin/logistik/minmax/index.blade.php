@extends('layouts.admin', [
    'title' => 'Stok Minmax Barang Logistik',
])

@once
    
@endonce

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('minmax-stok-barang')
        </div>
    </div>
@endsection
