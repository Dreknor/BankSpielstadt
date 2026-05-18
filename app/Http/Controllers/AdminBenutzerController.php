<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminBenutzerController extends Controller
{
    public function index(): View
    {
        $benutzer = User::orderBy('name')->get();
        return view('admin.benutzer.index', compact('benutzer'));
    }

    public function create(): View
    {
        return view('admin.benutzer.erstellen');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'is_admin'   => 'boolean',
            'is_manager' => 'boolean',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'is_admin'   => $request->boolean('is_admin'),
            'is_manager' => $request->boolean('is_manager'),
        ]);

        return redirect()->route('admin.benutzer.index')->with([
            'type'    => 'success',
            'Meldung' => 'Benutzer "' . $request->name . '" wurde angelegt.',
        ]);
    }

    public function edit(User $benutzer): View
    {
        return view('admin.benutzer.bearbeiten', compact('benutzer'));
    }

    public function update(Request $request, User $benutzer): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => "required|email|unique:users,email,{$benutzer->id}",
            'password'   => ['nullable', 'confirmed', Password::min(8)],
            'is_admin'   => 'boolean',
            'is_manager' => 'boolean',
        ]);

        // Verhindern, dass man seinen eigenen Admin-Status entzieht
        $istSelbst = auth()->id() === $benutzer->id;
        $isAdmin   = $istSelbst ? true : $request->boolean('is_admin');
        $isManager = $istSelbst ? $benutzer->is_manager : $request->boolean('is_manager');

        $daten = [
            'name'       => $request->name,
            'email'      => $request->email,
            'is_admin'   => $isAdmin,
            'is_manager' => $isManager,
        ];

        if (filled($request->password)) {
            $daten['password'] = Hash::make($request->password);
        }

        $benutzer->update($daten);

        return redirect()->route('admin.benutzer.index')->with([
            'type'    => 'success',
            'Meldung' => 'Benutzer "' . $benutzer->name . '" wurde gespeichert.',
        ]);
    }

    public function destroy(User $benutzer): RedirectResponse
    {
        if (auth()->id() === $benutzer->id) {
            return redirect()->route('admin.benutzer.index')->with([
                'type'    => 'error',
                'Meldung' => 'Du kannst dich selbst nicht löschen.',
            ]);
        }

        $name = $benutzer->name;
        $benutzer->delete();

        return redirect()->route('admin.benutzer.index')->with([
            'type'    => 'warning',
            'Meldung' => 'Benutzer "' . $name . '" wurde geloescht.',
        ]);
    }
}

