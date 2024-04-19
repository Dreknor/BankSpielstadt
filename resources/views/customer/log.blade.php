@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                Einzahlungen: {{session('customer')->payments->where('comment', 'LIKE', 'Einzahlung')->sum('amount')}} Radi
                            </div>
                            <div class="col">
                                Auszahlungen: {{session('customer')->payments->where('comment', 'LIKE', 'Auszahlung')->sum('amount')}}
                            </div>
                            <div class="col">
                                Steuer: {{session('customer')->payments->where('comment', 'LIKE', )->sum('amount')}}
                            </div>
                        </div>
                    </div>
                    <div class="card-body min-vh-50" style="background-color: lightyellow">
                        <div class="row">
                            <div class="col-6">
                                <h3>Buchungen</h3>
                                <ul class="list-group">
                                    @foreach($payments as $payment)
                                        <li class="list-group-item @if($payment->amount >0) text-success @else text-danger @endif">
                                            <div class="row">
                                                <div class="col-2">
                                                    {{optional($payment->created_at)->format('d.m.Y H:i')}}
                                                </div>
                                                <div class="col-4">
                                                    {{$payment->comment}}
                                                </div>
                                                <div class="col-2">
                                                    {{$payment->amount}} Radi
                                                </div>
                                                <div class="col-3">
                                                    {{optional($payment->banker)->name}}
                                                </div>


                                                <div class="col-1">
                                                    @if(auth()->user()->is_manager() and $payment->amount != 0 and $payment->comment != "Kredit")
                                                        <form method="post" class="form-inline" action="{{url('payments/delete/'.$payment->id)}}">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                            </div>
                                        </li>
                                    @endforeach
                                    <div class="list-group-item">
                                        {{$payments->links()}}
                                    </div>
                                </ul>
                            </div>
                            <div class="col-6">
                                <h3>Arbeitszeiten</h3>
                                <ul class="list-group">
                                    @foreach($working_times as $working_time)
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-4">
                                                    {{optional($working_time->start)->format('d.m.Y H:i')}} - {{optional($working_time->end)->format('d.m.Y H:i')}}
                                                </div>
                                                <div class="col-3">
                                                    {{$working_time->buisness->name}}
                                                </div>
                                                <div class="col-2">
                                                    {{$working_time->user->name}}
                                                </div>

                                                <div class="col-1">
                                                    @if(auth()->user()->is_manager())
                                                        <form method="post" class="form-inline" action="{{url('working_times/delete/'.$working_time->id)}}">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
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
