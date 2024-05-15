@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Kunden</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{$customer_count}}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Betriebe
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{$buisness_count}}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Arbeitszeiten
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-2 font-weight-bold text-gray-800">
                                            {{$working_times_today_count}} (heute)
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            / {{$working_times}} (gesamt)
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Lohnzahlungen (heute)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{$lohn}} Radi
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hand-holding-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-md-6 mb-6">
                <div class="card border-left-success shadow h-100 ">
                    <div class="card-header">
                        <div class=" font-weight-bold text-uppercase mb-1">
                            Kontostand Betriebe
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th>
                                    Name
                                </th>
                                <th>
                                    Kontostand
                                </th>
                                <th></th>
                            </tr>
                            @foreach($buisnesses as $buisness)
                                <tr class="@if($buisness->balance < abs($buisness->daily_balance())) text-danger @endif">
                                    <td>
                                        <a href="{{url('choose/customer/'.$buisness->id)}}">
                                            {{$buisness->name}}
                                        </a>
                                    </td>
                                    <td>
                                        {{$buisness->balance}}
                                    </td>
                                    <td>
                                        {{$buisness->daily_balance()}}
                                    </td>

                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 mb-6">
                <div class="card border-left-success shadow h-100 ">
                    <div class="card-header">
                        <div class=" font-weight-bold text-uppercase mb-1">
                            Kontostand Bewohner
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="customerTable">
                            <thead>
                            <tr>
                                <th>
                                    Name
                                </th>
                                <th>
                                    Kontostand
                                </th>
                                <th>
                                    Arbeitszeiten
                                </th>
                                <th>
                                    Arbeitszeit (min)
                                </th>
                            </tr>
                            </thead>
                           <tbody>
                           @foreach($customers as $customer)
                               <tr>
                                   <td>
                                       <a href="{{url('choose/customer/'.$customer->id)}}">
                                           {{$customer->name}}
                                       </a>
                                   </td>
                                   <td>
                                       {{$customer->balance}}
                                   </td>
                                   <td>
                                       {{$customer->working_times->count()}}
                                   </td>
                                   <td>
                                          @if($customer->working_times->count() > 0)
                                             {{$customer->working_times->sum('duration')}}
                                          @endif
                                    </td>
                               </tr>
                           @endforeach
                           </tbody>


                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-auto">
                <div class="card">
                    <div class="card-body">
                        <a href="{{url('start')}}" class="btn btn-outline-info">Startkapital verteilen</a>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="card">
                    <div class="card-body">
                        <a href="{{url('gebuehr')}}" class="btn btn-outline-info">Kontoführungsgebühr und Zinsen kassieren</a>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="card">
                    <div class="card-body">
                        <a href="{{url('export')}}" class="btn btn-outline-info">Export</a>

                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="card">
                    <div class="card-body">
                        <a href="{{url('deleteStart')}}" class="btn btn-outline-danger">Startgeld löschen</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>




<script>
    $('#customerTable').DataTable({layout: {
        top: {
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        },
    }});
</script>
@endpush
@push('css')
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.7/datatables.min.css" rel="stylesheet">

@endpush
