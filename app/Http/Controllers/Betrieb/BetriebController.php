<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BetriebController extends Controller
{
    public function loginForm()
    {
        if (session('betrieb')) {
            return redirect('/betrieb');
        }
        if (session('boerse')) {
            return redirect('/boerse');
        }
        // Börse wird mit angezeigt – aber mit klarem Hinweis. Login leitet auf /boerse.
        $betriebe = \App\Models\Customer::buisness()
            ->whereNotNull('betrieb_pin')
            ->get();
        return view('betrieb.login', compact('betriebe'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'betrieb_id' => 'required|exists:customers,id',
            'pin'        => 'required|string',
        ]);

        $betrieb = Customer::where('id', $request->betrieb_id)
                           ->where('buisness', 1)
                           ->whereNotNull('betrieb_pin')
                           ->first();

        if (! $betrieb || $betrieb->betrieb_pin !== $request->pin) {
            return back()->with(['type' => 'error', 'Meldung' => 'PIN falsch. Bitte nochmal versuchen.']);
        }

        // Börse hat ihr eigenes Frontend
        if ($betrieb->isBoerse()) {
            Session::forget('betrieb');
            Session::put('boerse', now()->toDateTimeString());
            return redirect('/boerse');
        }

        Session::put('betrieb', $betrieb);

        return redirect('/betrieb');
    }

    public function logout()
    {
        Session::forget('betrieb');
        Session::forget('boerse');
        return redirect('/betrieb/login');
    }

    public function index()
    {
        $betrieb = session('betrieb');
        // Session-Objekt aktualisieren (Kassenbestand kann sich geändert haben)
        $betrieb = Customer::findOrFail($betrieb->id);
        Session::put('betrieb', $betrieb);

        $produkte = $betrieb->products()->where('active', true)->orderBy('name')->get();
        $kassenbestand = $betrieb->kassenbestand();

        return view('betrieb.index', compact('betrieb', 'produkte', 'kassenbestand'));
    }
}


