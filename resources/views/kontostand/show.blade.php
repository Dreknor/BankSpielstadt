@extends('kontostand.layout')

@section('content')
    <div class="container">
        <!--vertical align on parent using my-auto-->
        <div class="row h-100">
            <div class="col-sm-12 my-auto">
                <br>
                <div class="card bg-info">
                    <div class="card-header m-auto text-white border-bottom mt-5">
                        <h1 class="mx-auto text-center" id="hinweis">
                            Hallo {{$user->name}},<br>
                        </h1>
                    </div>

                    <div class="card-body text-white">
                        <div class="row">
                            <div class="col-12">
                                <h4 class="text-center">
                                    <b>Aktueller Kontostand:<br></b>
                                    {{$user->balance}} Radi
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="w-25 mx-auto">
                            <div class="text-center text-light">
                                Läuft ab in:
                                <div class="autologouttimer">
                                    <div id="progressbar" class="progressbar color-red"></div>
                                </div><br/>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
@push('js')
    <script>

        $(document).ready(function() {
            /* Change time here to make the animation longer */
            $('#progressbar').animate({width: '0'}, {{config('bank.kontostand.logout')*1000}}, 'linear', function () {
                window.location.href = "{{route('kontostand')}}";
            });
        });
    </script>
@endpush
