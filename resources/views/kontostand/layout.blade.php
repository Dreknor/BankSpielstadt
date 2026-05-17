<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <META HTTP-EQUIV="refresh" CONTENT="{{ config('bank.kontostand.logout') }}; URL={{ route('kontostand') }}">
    <title>{{ config('app.name') }}</title>

    <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">

    {{-- Tailwind CSS (Vite) --}}
    @vite(['resources/css/app.css'])

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/c8f58e3eb6.js" crossorigin="anonymous"></script>

    @stack('css')

</head>

<body class="min-h-screen bg-gradient-to-br from-sky-400 via-brand-500 to-indigo-600 font-sans text-white flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        @yield('content')
    </div>
    @stack('js')
</body>
</html>
