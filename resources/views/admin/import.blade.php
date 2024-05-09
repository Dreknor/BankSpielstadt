@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <form action="{{route('import')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group
                @if($errors->has('file'))
                    has-error
                @endif">
                    <label for="file">Datei</label>
                    <input type="file" name="file" id="file" class="form-control">
                    @if($errors->has('file'))
                        <span class="help-block
                        @if($errors->has('file'))
                            has-error
                        @endif">
                            {{$errors->first('file')}}
                        </span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Importieren</button>
        </div>
    </div>
@endsection
@push('js')

@endpush
