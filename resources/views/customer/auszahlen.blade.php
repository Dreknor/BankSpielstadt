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
                    <div class="card-body min-vh-50" style="background-color: ">
                        <form action="{{url('auszahlen')}}" method="post" class="form-inline w-100 mt-5 ">
                            @csrf
                            <label class=" w-75">
                                <h4>Wieviele Radi möchte der Kunde abheben?</h4>
                                <input type="number" step="0.5" min="0.5" max="{{session('customer')->balance}}" class="form-control" name="amount" autofocus>
                            </label>
                            <button type="submit" class="btn btn-info">speichern</button>
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
