@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            {{ __('Kunde erstellen') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form autocomplete="off" class="form-horizontal" method="post" action="{{url('customer/store')}}">
                            @csrf
                            <div class="row mt-2">
                                <label>
                                    Name
                                    <input class="form-control w-100" name="name" autofocus
                                           type='text'
                                           autoComplete='off'>
                                </label>
                            </div>
                            <div class="row mt-2">
                                <label>
                                    Ist ein Betrieb?
                                    <select class="form-select w-100" name="buisness">
                                        <option value="0">nein</option>
                                        <option value="1">ja</option>
                                    </select>
                                </label>
                            </div>
                            <div class="row mt-2">
                                <label>
                                    Startkapital
                                    <input class="form-control w-100" name="startkapital" type="number" min="0">
                                </label>
                            </div>
                            <div class="row mt-2">
                                <label>
                                    Key-Nummer
                                    <input class="form-control w-100" name="key" type="text">
                                </label>
                            </div>

                            <div class="row mt-2">
                                <button type="submit" class="btn btn-success">speichern</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" />
@endpush
