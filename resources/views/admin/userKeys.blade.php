@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card border-left-success shadow h-100 ">
                    <div class="card-header">
                        <h5 class="card-title">
                            {{ __('Kunde wählen') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{url('remove/key')}}" method="post">
                            @csrf
                            <div class="row">
                                <label>
                                    Bitte Key eingeben
                                    <input name="key" class="form-control w-100" id="search" autofocus type='text'  autoComplete='off'>
                                </label>
                            </div>
                            <button class="btn btn-primary mt-2" type="submit">Key entfernen</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-4">
        <div class="card border-left-success shadow h-100 ">
        <div class="card-header">
            <div class=" font-weight-bold text-uppercase mb-1">
                Bewohner-Keys
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
                        Key
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
                            {{$customer->key}}
                        </td>
                    </tr>
                @endforeach
                </tbody>


            </table>
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
