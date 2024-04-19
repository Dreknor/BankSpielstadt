@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header text-center">
                    <div class="row">
                        <div class="col-1">
                            <img src="{{ asset('storage/images/'.session('customer')->name.'.jpg') }}" alt="" title="" class="img-fluid" >

                        </div>
                        <div class="col-7">
                            <h1>
                                @if(session('customer')->is_buisness())
                                @endif{{session('customer')->name}}
                            </h1>


                        </div>
                        <div class="col-4">
                            <h3 class="mt-2 {{(session('customer')->balance > 0)? 'text-success' : 'text-danger'}}">
                                Kontostand: {{session('customer')->balance}} Radi @if(session('customer')->kredit > 0) / <div class="text-danger"> Kredit: {{session('customer')->kredit}} Radi  </div> @endif
                            </h3>
                        </div>
                    </div>

                </div>
                <div class="card-body text-center min-vh-50">
                    <table class="table vh-50 nav-table">
                        <tr class="h-50">
                            <th class="align-middle w-50" style="background-color: lightgreen" onclick="location.href='{{url('einzahlen')}}'">
                                <h1>
                                    Einzahlen
                                </h1>
                            </th>
                            @if(session('customer')->balance > 0)
                                <th class="align-middle" style="background-color: lightcoral"  onclick="location.href='{{url('auszahlen')}}'">
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
                            @if(!session('customer')->is_buisness())
                                <th class="align-middle" style="background-color: lightblue"  onclick="location.href='{{url('arbeitszeit')}}'">
                                    <h1>
                                        Arbeitszeit
                                    </h1>
                                </th>
                            @elseif(session('customer')->kredit > 0 )
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
                        <tr >
                            <th colspan="2" style="background-color: lightgray"   onclick="location.href='{{url('new/customer')}}'">
                                <h2 class="pt-4">
                                    neuer Kunde
                                </h2>
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
