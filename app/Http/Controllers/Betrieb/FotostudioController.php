<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FotostudioBild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class FotostudioController extends Controller
{
    private function betrieb(): Customer
    {
        $betrieb = Customer::findOrFail(session('betrieb')->id);
        abort_unless($betrieb->isFotostudio(), 403, 'Dieses Konto hat kein Fotostudio.');
        return $betrieb;
    }

    private function authorizeBild(FotostudioBild $bild): void
    {
        abort_if($bild->customer_id !== session('betrieb')->id, 403);
    }

    public function index()
    {
        $betrieb = $this->betrieb();
        $bilder  = $betrieb->fotostudioBilder()->orderBy('reihenfolge')->orderBy('created_at')->get();
        $slideshowUrl = $betrieb->fotostudio_token
            ? url('/fotostudio/' . $betrieb->fotostudio_token)
            : null;

        return view('betrieb.fotostudio.index', compact('betrieb', 'bilder', 'slideshowUrl'));
    }

    public function hochladen()
    {
        $betrieb = $this->betrieb();
        return view('betrieb.fotostudio.hochladen', compact('betrieb'));
    }

    public function store(Request $request)
    {
        $betrieb = $this->betrieb();

        $request->validate([
            'bilder'      => 'required|array|min:1',
            'bilder.*'    => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'titel.*'     => 'nullable|string|max:120',
            'sichtbar'    => 'nullable|in:0,1',
            'anzeige_von' => 'nullable|date',
            'anzeige_bis' => 'nullable|date|after_or_equal:anzeige_von',
            'reihenfolge' => 'nullable|integer|min:0|max:999',
            'anzeige_dauer' => 'nullable|integer|min:0|max:999',
        ]);

        // Zielverzeichnis physisch anlegen (Flysystem-unabhängig)
        $zielOrdner = storage_path("app/public/fotostudio/{$betrieb->id}");
        if (! is_dir($zielOrdner)) {
            mkdir($zielOrdner, 0755, true);
        }

        $gespeichert = 0;
        foreach ($request->file('bilder') as $index => $datei) {
            if (! $datei || ! $datei->isValid()) {
                continue;
            }

            $ext      = strtolower($datei->getClientOriginalExtension() ?: 'jpg');
            $neuName  = \Illuminate\Support\Str::uuid() . '.' . $ext;
            $datei->move($zielOrdner, $neuName);

            // Relativer Pfad ab storage/app/public – wird von Storage::url() korrekt aufgelöst
            $pfad = "fotostudio/{$betrieb->id}/{$neuName}";

            $betrieb->fotostudioBilder()->create([
                'titel'       => $request->input("titel.{$index}") ?: null,
                'dateiname'   => $datei->getClientOriginalName(),
                'pfad'        => $pfad,
                'sichtbar'    => $request->input('sichtbar', '1') === '1',
                'anzeige_von' => $request->input('anzeige_von') ?: null,
                'anzeige_bis' => $request->input('anzeige_bis') ?: null,
                'reihenfolge' => (int) ($request->input('reihenfolge', 0)),
                'anzeige_dauer' => (int) ($request->input('anzeige_dauer', 0)),
            ]);
            $gespeichert++;
        }

        if ($gespeichert === 0) {
            return back()->with(['type' => 'error', 'Meldung' => 'Kein Bild konnte gespeichert werden. Bitte nochmal versuchen.']);
        }

        return redirect()->route('betrieb.fotostudio.index')
            ->with(['type' => 'success', 'Meldung' => "{$gespeichert} Bild(er) erfolgreich hochgeladen!"]);
    }

    public function bearbeiten(FotostudioBild $bild)
    {
        $betrieb = $this->betrieb();
        $this->authorizeBild($bild);
        return view('betrieb.fotostudio.bearbeiten', compact('betrieb', 'bild'));
    }

    public function update(Request $request, FotostudioBild $bild)
    {
        $this->betrieb();
        $this->authorizeBild($bild);

        $data = $request->validate([
            'titel'       => 'nullable|string|max:120',
            'sichtbar'    => 'boolean',
            'anzeige_von' => 'nullable|date',
            'anzeige_bis' => 'nullable|date|after_or_equal:anzeige_von',
            'reihenfolge' => 'nullable|integer|min:0|max:999',
            'anzeige_dauer' => 'nullable|integer|min:0|max:999',
        ]);

        $bild->update([
            'titel'       => $data['titel'] ?? null,
            'sichtbar'    => $request->boolean('sichtbar'),
            'anzeige_von' => $data['anzeige_von'] ?? null,
            'anzeige_bis' => $data['anzeige_bis'] ?? null,
            'reihenfolge' => (int) ($data['reihenfolge'] ?? 0),
            'anzeige_dauer' => (int) ($data['anzeige_dauer'] ?? 0),
        ]);

        return redirect()->route('betrieb.fotostudio.index')
            ->with(['type' => 'success', 'Meldung' => 'Bild aktualisiert!']);
    }

    public function destroy(FotostudioBild $bild)
    {
        $this->betrieb();
        $this->authorizeBild($bild);

        $vollPfad = storage_path('app/public/' . $bild->pfad);
        if (is_file($vollPfad)) {
            @unlink($vollPfad);
        }
        $bild->delete();

        return redirect()->route('betrieb.fotostudio.index')
            ->with(['type' => 'warning', 'Meldung' => 'Bild gelöscht.']);
    }

    /** Reihenfolge per AJAX-artiger POST-Anfrage speichern */
    public function reihenfolge(Request $request)
    {
        $betrieb = $this->betrieb();
        $request->validate(['reihenfolge' => 'required|array']);

        foreach ($request->input('reihenfolge') as $id => $pos) {
            FotostudioBild::where('id', $id)
                ->where('customer_id', $betrieb->id)
                ->update(['reihenfolge' => (int) $pos]);
        }

        return redirect()->route('betrieb.fotostudio.index')
            ->with(['type' => 'success', 'Meldung' => 'Reihenfolge gespeichert!']);
    }
}




