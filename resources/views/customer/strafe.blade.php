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
                        <form action="{{url('strafe')}}" method="post" class="form-inline w-100 mt-5 ">
                            @csrf
                            <div class="form-row">
                                <label class=" w-75">
                                    <h4>Wieviele Radi muss der Kunde Strafe zahlen?</h4>
                                    <input type="number" step="1" min="1" max="" class="form-control" name="amount" autofocus>
                                </label>
                            </div>

                            <div class="form-row">
                                <label class=" w-75">
                                    <h4>Wofür ist die Strafe?</h4>
                                    <input type="text" class="form-control" name="comment" autofocus>
                                </label>
                            </div>

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
