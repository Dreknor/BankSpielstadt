<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @stack('css')
</head>
<body>
@php
    \Carbon\Carbon::setlocale(config('app.locale'));
@endphp
<table class="table table-bordered table-hover">
    <tr>
        <th colspan="7">
            Übersicht Betriebe
        </th>
    </tr>
    <tr>
        <th>
            Name
        </th>
        @for($x = \Carbon\Carbon::today()->startOfWeek(); $x < \Carbon\Carbon::today()->endOfWeek()->subDays(2); $x->addDay())
            <th>
                {{$x->translatedFormat('l')}}
            </th>
        @endfor
        <th>
            Gesamt
        </th>
    </tr>
    @foreach($customers as $buisness)
        <tr>
            <td>
                {{$buisness->name}}
            </td>
            @for($y = \Carbon\Carbon::today()->startOfWeek(); $y < \Carbon\Carbon::today()->endOfWeek()->subDays(2); $y->addDay())
                <td>
                    {{$buisness->daily_balance($y)}}
                </td>
            @endfor
            <td>
                {{$buisness->payments()
                ->whereNot('comment', 'LIKE', 'Kredit')
                ->whereNot('comment', 'LIKE', 'Startkapital')
                ->sum('amount')}}
            </td>
        </tr>
    @endforeach
</table>


</body>
</html>
