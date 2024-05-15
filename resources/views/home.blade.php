@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header text-center">
                    <div class="row">
                        <div class="col-1">
                            <img src="{{ asset('storage/images/'.$customer->name.'.jpg') }}" alt="" title="" class="img-fluid" >

                        </div>
                        <div class="col-7">
                            <h1>
                                @if($customer->is_buisness())
                                @endif{{$customer->name}}
                            </h1>


                        </div>
                        <div class="col-4">
                            <h3 class="mt-2 {{($customer->balance > 0)? 'text-success' : 'text-danger'}}">
                                Kontostand: {{$customer->balance}} Radi @if($customer->kredit > 0) / <div class="text-danger"> Kredit: {{$customer->kredit}} Radi  </div> @endif
                            </h3>
                        </div>
                    </div>

                </div>

                @if(!$customer->is_buisness() and (!$customer->key or $customer->key = null ))
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h3>
                                Bitte geben Sie den Key ein
                            </h3>
                        </div>
                        <form method="post" action="{{url('set/key')}}">
                            @csrf
                            <div class="row mt-2">
                                <label>
                                    Key-Nummer
                                    <input class="form-control w-100" name="key" type="text" autofocus>
                                </label>
                            </div>
                            <div class="row mt-2">
                                <button type="submit" class="btn btn-success">speichern</button>
                            </div>
                        </form>
                    </div>

                @endif

                <div class="card-body text-center min-vh-50">
                    <table class="table vh-50 nav-table">
                        <tr class="h-50">
                            <th class="align-middle w-50" style="background-color: lightgreen"  onclick="location.href='{{url('einzahlen')}}'">
                                <h1>
                                    Einzahlen
                                </h1>
                            </th>
                            @if($customer->balance > 0)
                                <th class="align-middle" style="background-color: lightcoral" onclick="location.href='{{url('auszahlen')}}'">
                                    <h1>
                                        Auszahlen
                                    </h1>
                                </th>
                            @else
                                <th class="align-middle" style="background-color: gray">
                                    <h3>
                                        keine Auszahlung
                                    </h3>
                                </th>
                            @endif
                        </tr>
                        <tr class="h-50">

                            @if(!$customer->is_buisness())
                                <th class="align-middle"  style="background-color: lightblue"  onclick="location.href='{{url('arbeitszeit')}}'">
                                    <h1>
                                        Arbeitszeit
                                    </h1>
                                </th>
                            @elseif($customer->kredit > 0 )
                                <th class="align-middle" style="background-color: lightgray">
                                    <h5>
                                        kein Kredit
                                    </h5>
                                </th>
                            @else
                                <th class="align-middle" style="background-color: #f8b157" onclick="location.href='{{url('kredit')}}'">
                                    <h1>
                                        Kredit
                                    </h1>
                                </th>
                            @endif
                            <th class="align-middle" style="background-color: lightyellow"  onclick="location.href='{{url('log')}}'">
                                <h1>
                                    Log
                                </h1>
                            </th>
                        </tr>
                        @if(auth()->user()->is_admin and !$customer->is_buisness() )
                            <tr class="h-50">
                                <th class="align-middle" colspan="2"  style="background-color: lightcoral"   onclick="location.href='{{url('strafe')}}'">
                                    <h1>
                                        Strafe
                                    </h1>
                                </th>
                            </tr>
                        @endif
                        @if($customer->is_buisness())
                            <tr class="h-50">
                                    <th class="align-middle" colspan="2" style="background-color: lightblue"  onclick="location.href='{{url('ueberweisung')}}'">
                                        <h1>
                                            Überweisung
                                        </h1>
                                    </th>
                            </tr>

                            @endif
                        <tr >
                            <th colspan="4" style="background-color: lightgray"   onclick="location.href='{{url('new/customer')}}'">
                                <h2 class="pt-4">
                                    neuer Kunde
                                </h2>
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        @if($customer->is_buisness() and $customer->bonus->count()>0)
            <div class="col-auto">
                <ul class="list-group">
                    @foreach($customer->bonus as $bonus)
                        <li class="list-group-item">
                            <div class="row">
                                {{$bonus->start}} - {{$bonus->end}}
                            </div>
                            <div class="row">
                                {{$bonus->bonus}} Radi (@if ($bonus->bonus_type == 'hourly') je Stunde @else einmalig @endif )
                            </div>

                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
