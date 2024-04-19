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
                        <form action="{{url('arbeitszeit')}}" method="post" class="form-horizontal w-100 mt-5 ">
                            @csrf
                            <div class="form-row">
                                <label class=" w-100">
                                    <h4>An welchem Tag war die Arbeitszeit?</h4>
                                    <select class="form-select" name="day">
                                        <option @if($day == 1) selected @endif value="1">Montag</option>
                                        @if(\Carbon\Carbon::today()->dayOfWeek > 1)
                                            <option @if($day == 2) selected @endif  value="2">Dienstag</option>
                                        @endif
                                        @if(\Carbon\Carbon::today()->dayOfWeek > 2)
                                            <option @if($day == 3) selected @endif  value="3">Mittwoch</option>
                                        @endif
                                        @if(\Carbon\Carbon::today()->dayOfWeek > 3)
                                            <option @if($day == 4) selected @endif  value="4">Donnerstag</option>
                                        @endif
                                        @if(\Carbon\Carbon::today()->dayOfWeek > 4)
                                            <option @if($day == 5) selected @endif  value="5">Freitag</option>
                                        @endif

                                    </select>
                                </label>
                            </div>
                            <div class="form-row mt-2">
                                <label class=" w-100">
                                    <h4>In welchem Betrieb wurde gearbeitet?</h4>
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
                                    <h4>Ist der Kund Chef in diesem Betrieb?</h4>
                                    <select class="form-select" name="manager">
                                        <option value="0">nein</option>
                                        <option value="1">ja</option>
                                    </select>
                                </label>
                            </div>

                            <div class="row mt-3">
                                <div class="col-6 ">
                                    <label class=" w-100 text-center">
                                        <h4>Anfangszeit</h4>
                                        <div class="row">
                                            <div class="col-2 offset-4">
                                                <label class=" w-100">Stunde
                                                    <input type="number" min="8" max="13" name="start_hour" class="form-control" required>
                                                </label>
                                            </div>
                                            <div class="col-1">
                                               <label class="fw-bold">
                                                   :
                                               </label>
                                            </div>
                                            <div class="col-2">
                                                <label class=" w-100">Minute
                                                    <input type="number" min="0" max="60"  step="5" name="start_minute"class="form-control" required>
                                                </label>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class=" w-100 text-center">
                                        <h4>Endzeit</h4>
                                        <div class="row">
                                            <div class="col-2 offset-4">
                                                <label class=" w-100">Stunde
                                                    <input type="number" min="8" max="13" name="end_hour" class="form-control" required>
                                                </label>
                                            </div>
                                            <div class="col-1">
                                                <label class="fw-bold">
                                                    :
                                                </label>
                                            </div>
                                            <div class="col-2">
                                                <label class=" w-100">Minute
                                                    <input type="number" min="0" max="60" step="5" name="end_minute"class="form-control" required>
                                                </label>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>



                            <div class="form-row mt-4">
                                <button type="submit" class="btn btn-lg btn-success w-100">speichern</button>
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
