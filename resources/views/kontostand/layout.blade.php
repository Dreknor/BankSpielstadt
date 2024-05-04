<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">

    <link rel="shortcut icon" href="{{asset('img/favicon.ico')}}" type="image/x-icon">
    <META HTTP-EQUIV="refresh" CONTENT="{{config('bank.kontostand.logout')}}; URL={{route('kontostand')}}">
    <title>{{env('APP_NAME')}}</title>

    <!-- CSS Files -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/paper-dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/palette-gradient.css') }}" rel="stylesheet">


    <link href="{{ asset('css/kontostand.css') }}" rel="stylesheet">


    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .center {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            border: 3px solid green;
        }


    </style>

    @stack('css')

</head>

<body id="app-layout" style="background-color: #0dcaf0; height: 200px">

<div class="container-fluid " style="top: 50%;">
        @yield('content')
</div>
@stack('js')
</body>
</html>
