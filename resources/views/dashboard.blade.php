@extends('layouts.admin', [
    'title' => 'Dashboard',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <ul class="form-group">
                <li class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="p1" value="option1">
                    <label for="p1" class="custom-control-label">Parent</label>
                    <ul class="form-group">
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="c11" value="option1">
                            <label for="c11" class="custom-control-label">Child 1</label>
                        </li>
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="c12" value="option1">
                            <label for="c12" class="custom-control-label">Child 2</label>
                        </li>
                        <li class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="c13" value="option1">
                            <label for="c13" class="custom-control-label">Child 3</label>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
@endsection
