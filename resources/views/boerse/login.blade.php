@extends('boerse.layouts.app')
@section('content')
<div class="max-w-md mx-auto bg-white rounded-2xl shadow-kid p-8 mt-12 border-2 border-amber-200">
    <div class="text-center mb-6">
        <div class="text-6xl mb-2">📈</div>
        <h1 class="text-3xl font-extrabold text-amber-700">Radi-Börse</h1>
        <p class="text-slate-600 mt-2">Bitte den Börsen-PIN eingeben.</p>
    </div>
    <form method="POST" action="/boerse/login" class="space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">PIN</label>
            <input type="password" name="pin" autofocus required
                   class="w-full text-2xl text-center border-2 border-amber-300 rounded-xl px-4 py-3 focus:outline-none focus:border-amber-500">
        </div>
        <button class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xl font-bold py-4 rounded-xl shadow-kid">
            🔓 Anmelden
        </button>
    </form>
    <div class="mt-6 text-center">
        <a href="/boerse/hilfe" class="text-amber-700 underline font-semibold">❓ Was muss ich hier tun?</a>
    </div>
</div>
@endsection

