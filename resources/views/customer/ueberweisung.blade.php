@extends('layouts.app')

@section('content')


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-8">
                                <h1>
                                    {{session('customer')->name}}
                                </h1>
                            </div>
                            <div class="col-4">
                                <h3 class="mt-2 {{(session('customer')->balance > 0)? 'text-success' : 'text-danger'}}">
                                    Kontostand: {{session('customer')->balance}} Radi
                                </h3>
                            </div>
                        </div>

                    </div>
                    <div class="card-body min-vh-50" style="background-color: lightblue">
                        <form action="{{url('ueberweisung')}}" method="post" class="form-horizontal w-100 mt-5 ">
                            @csrf
                            <div class="form-row mt-2">
                                <label class=" w-100">
                                    <h4>An welchen Betrieb soll Geld geschickt werden?</h4>
                                    <select class="form-select" name="buisness" required>
                                        <option disabled selected></option>
                                        @foreach($buisnesses as $buisness)
                                            <option value="{{$buisness->id}}">{{$buisness->name}}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="form-row mt-2">
                                <label class=" w-100">
                                    <h4>Wofür wird das Geld überwiesen?</h4>
                                    <input class="form-control" name="reason" type="text" required>
                                </label>
                            </div>

                            <div class="form-row mt-2">
                                <label class=" w-100">
                                    <h4>Wieviel Radi sollen überwiesen werden?</h4>
                                    <input type="number" step="1" min="1" max="{{session('customer')->balance}}" class="form-control" name="amount" required>
                                </label>
                            </div>

                            <div class="form-row mt-4">
                                <button type="submit" class="btn btn-lg btn-success w-100">Geld senden</button>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer" style="background-color: lightgray" onclick="location.href='{{url('/')}}'">
                        <h2 class="pt-4 m-auto text-center">
                            zurück
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
