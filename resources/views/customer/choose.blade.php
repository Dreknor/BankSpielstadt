@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            {{ __('Kunde wählen') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form autocomplete="off" class="form-horizontal">
                            <div class="row">
                                    <label>
                                        Bitte Name eingeben
                                        <input class="form-control w-100" id="search" autofocus
                                               type='text'
                                               autoComplete='off'>
                                    </label>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <ul class="list-group" id="ergebnis">

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js">
    </script>
    <script type="text/javascript">
        var route = "{{ url('autocomplete-search') }}";
        $('#search').typeahead({
            source: function (query, process) {
                return $.get(route, {
                    query: query
                }, function (data) {
                    console.log(data)
                    return process(data);
                });
            },
            afterSelect: function (item){
                window.location.href = "{{url('choose/customer')}}" + '/'+ item.id;
            }
        });
    </script>
@endpush

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" />
@endpush
