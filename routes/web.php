<?php

use App\Http\Controllers\AdminBenutzerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminBetriebController;
use App\Http\Controllers\AdminBoerseController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\Betrieb\BetriebController;
use App\Http\Controllers\Betrieb\ProduktController;
use App\Http\Controllers\Betrieb\KasseController;
use App\Http\Controllers\Betrieb\AbrechnungController;
use App\Http\Controllers\Betrieb\FotostudioController;
use App\Http\Controllers\FotostudioSlideshowController;
use App\Http\Controllers\Boerse\BoerseController;
use App\Http\Controllers\Boerse\BoerseHandelController;
use App\Http\Controllers\Boerse\BoerseErfassungController;
use App\Http\Controllers\Boerse\BoerseKasseController;
use App\Http\Controllers\Boerse\BoerseKurseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KontostandController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WorkingTimeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => env('REGISTER', false)]);

Route::get('/kontostand', [KontostandController::class, 'kontostand'])->name('kontostand');
Route::post('/kontostand/show', [KontostandController::class, 'auth'])->name('kontostand.read_key');

// Fotostudio Slideshow (öffentlich, kein Login nötig)
Route::get('/fotos',                        [FotostudioSlideshowController::class, 'landing'])->name('fotostudio.landing');
Route::get('/fotostudio/{token}',           [FotostudioSlideshowController::class, 'show'])->name('fotostudio.slideshow');
Route::get('/fotostudio/{token}/daten',     [FotostudioSlideshowController::class, 'daten'])->name('fotostudio.slideshow.daten');

Route::middleware(['auth'])->group(function (){


    Route::resource('costumer', CustomerController::class);
    Route::get('choose/customer', [CustomerController::class, 'choose']);
    Route::get('choose/customer/{customer}', [CustomerController::class, 'setCustomer']);
    Route::get('/autocomplete-search', [CustomerController::class, 'search']);

    Route::middleware(['isManager'])->group(function () {
        Route::get('create/customer', [CustomerController::class, 'createCustomer']);
        Route::post('customer/store', [CustomerController::class, 'store']);
    });

    Route::middleware(['isAdmin'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'index']);
        Route::get('gebuehr', [AdminController::class, 'gebuehr']);
        Route::get('export', [AdminController::class, 'export']);
        Route::get('deleteStart', [AdminController::class, 'delete']);
        Route::get('start', [AdminController::class, 'makeStartkapital']);
        Route::get('import', [AdminController::class, 'import'])->name('import');
        Route::post('import', [AdminController::class, 'storeImport'])->name('import.store');

        Route::get('strafe', [AdminController::class, 'strafe']);
        Route::post('strafe', [AdminController::class, 'storeStrafe']);

        Route::get('remove/key', [AdminController::class, 'removeKey']);
        Route::post('remove/key', [AdminController::class, 'storeRemoveKey']);

        // Benutzerverwaltung
        Route::get('admin/benutzer',                      [AdminBenutzerController::class, 'index'])->name('admin.benutzer.index');
        Route::get('admin/benutzer/erstellen',            [AdminBenutzerController::class, 'create'])->name('admin.benutzer.create');
        Route::post('admin/benutzer',                     [AdminBenutzerController::class, 'store'])->name('admin.benutzer.store');
        Route::get('admin/benutzer/{benutzer}/bearbeiten',[AdminBenutzerController::class, 'edit'])->name('admin.benutzer.edit');
        Route::put('admin/benutzer/{benutzer}',           [AdminBenutzerController::class, 'update'])->name('admin.benutzer.update');
        Route::delete('admin/benutzer/{benutzer}',        [AdminBenutzerController::class, 'destroy'])->name('admin.benutzer.destroy');

        // Log-Viewer
        Route::get('admin/logs',  [AdminLogController::class, 'index'])->name('admin.logs');
        Route::post('admin/logs/leeren', [AdminLogController::class, 'leeren'])->name('admin.logs.leeren');
    });

    // Push-Benachrichtigungen (alle eingeloggten User)
    Route::get('push/vapid-key',    [PushController::class, 'vapidKey'])->name('push.vapid-key');
    Route::post('push/abonnieren',  [PushController::class, 'abonnieren'])->name('push.abonnieren');
    Route::post('push/abbestellen', [PushController::class, 'abbestellen'])->name('push.abbestellen');

    Route::middleware(['hasCustomer'])->group(function (){
        Route::get('new/customer', [CustomerController::class, 'new']);
        Route::get('log', [CustomerController::class, 'log']);
        Route::delete('payments/delete/{payment}', [PaymentController::class, 'delete']);
        Route::delete('working_times/delete/{working_time}', [WorkingTimeController::class, 'destroy']);

        Route::post('set/key', [CustomerController::class, 'setKey'])->name('set.key');

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/home', [HomeController::class, 'index']);

        Route::get('einzahlen', [PaymentController::class, 'einzahlen']);
        Route::post('einzahlen', [PaymentController::class, 'storeEinzahlen']);
        Route::get('auszahlen', [PaymentController::class, 'auszahlen']);
        Route::post('auszahlen', [PaymentController::class, 'storeAuszahlen']);
        Route::get('kredit', [PaymentController::class, 'kredit']);
        Route::post('kredit', [PaymentController::class, 'storeKredit']);


        Route::get('arbeitszeit', [WorkingTimeController::class, 'create']);
        Route::post('arbeitszeit', [WorkingTimeController::class, 'store']);

        Route::get('ueberweisung', [PaymentController::class, 'ueberweisung']);
        Route::post('ueberweisung', [PaymentController::class, 'storeUeberweisung']);

    });
});

/*
|--------------------------------------------------------------------------
| Betriebs-Kasse
|--------------------------------------------------------------------------
*/
Route::prefix('betrieb')->group(function () {
    // Öffentlich
    Route::get('login',  [BetriebController::class, 'loginForm'])->name('betrieb.login');
    Route::post('login', [BetriebController::class, 'login'])->name('betrieb.login.store');
    Route::get('logout', [BetriebController::class, 'logout'])->name('betrieb.logout');

    // Geschützt
    Route::middleware('hasBetrieb')->group(function () {
        Route::get('/',           [BetriebController::class, 'index'])->name('betrieb.index');

        // Produkte
        Route::get('produkte',                          [ProduktController::class, 'index'])->name('betrieb.produkte.index');
        Route::get('produkte/erstellen',                [ProduktController::class, 'create'])->name('betrieb.produkte.create');
        Route::post('produkte',                         [ProduktController::class, 'store'])->name('betrieb.produkte.store');
        Route::get('produkte/{product}/bearbeiten',     [ProduktController::class, 'edit'])->name('betrieb.produkte.edit');
        Route::put('produkte/{product}',                [ProduktController::class, 'update'])->name('betrieb.produkte.update');
        Route::delete('produkte/{product}',             [ProduktController::class, 'destroy'])->name('betrieb.produkte.destroy');

        // Kasse
        Route::get('kasse',            [KasseController::class, 'index'])->name('betrieb.kasse');
        Route::post('kasse/verkauf',   [KasseController::class, 'storeVerkauf'])->name('betrieb.kasse.verkauf');
        Route::post('kasse/einlage',   [KasseController::class, 'storeEinlage'])->name('betrieb.kasse.einlage');
        Route::post('kasse/entnahme',  [KasseController::class, 'storeEntnahme'])->name('betrieb.kasse.entnahme');

        // Abrechnung
        Route::get('abrechnung',         [AbrechnungController::class, 'index'])->name('betrieb.abrechnung');
        Route::get('abrechnung/drucken', [AbrechnungController::class, 'print'])->name('betrieb.abrechnung.print');

        // Fotostudio
        Route::get('fotos',                             [FotostudioController::class, 'index'])->name('betrieb.fotostudio.index');
        Route::get('fotos/hochladen',                   [FotostudioController::class, 'hochladen'])->name('betrieb.fotostudio.hochladen');
        Route::post('fotos',                            [FotostudioController::class, 'store'])->name('betrieb.fotostudio.store');
        Route::get('fotos/{bild}/bearbeiten',           [FotostudioController::class, 'bearbeiten'])->name('betrieb.fotostudio.bearbeiten');
        Route::put('fotos/{bild}',                      [FotostudioController::class, 'update'])->name('betrieb.fotostudio.update');
        Route::delete('fotos/{bild}',                   [FotostudioController::class, 'destroy'])->name('betrieb.fotostudio.destroy');
        Route::post('fotos/reihenfolge',                [FotostudioController::class, 'reihenfolge'])->name('betrieb.fotostudio.reihenfolge');
    });
});

/*
|--------------------------------------------------------------------------
| Admin: Betriebsdaten
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('betriebe/pin',                          [AdminBetriebController::class, 'pinIndex'])->name('admin.betriebe.pin');
    Route::post('betriebe/pin',                         [AdminBetriebController::class, 'pinStore'])->name('admin.betriebe.pin.store');
    Route::get('betriebe/{customer}/kasse',             [AdminBetriebController::class, 'kasse'])->name('admin.betriebe.kasse');
    Route::delete('betriebe/transaktion/{transaktion}', [AdminBetriebController::class, 'deleteTransaktion'])->name('admin.betriebe.transaktion.delete');
    Route::get('betriebe/{customer}/produkte',          [AdminBetriebController::class, 'produkte'])->name('admin.betriebe.produkte');
    Route::get('betriebe/{customer}/fotostudio',        [AdminBetriebController::class, 'fotostudio'])->name('admin.betriebe.fotostudio');
    Route::post('betriebe/{customer}/fotostudio',       [AdminBetriebController::class, 'fotostudioStore'])->name('admin.betriebe.fotostudio.store');
});

/*
|--------------------------------------------------------------------------
| Radi-Börse (Aktien)
|--------------------------------------------------------------------------
*/
Route::prefix('boerse')->group(function () {
    // Öffentlich (auch ohne Login)
    Route::get('login',         [BoerseController::class, 'loginForm'])->name('boerse.login');
    Route::post('login',        [BoerseController::class, 'login']);
    Route::get('logout',        [BoerseController::class, 'logout'])->name('boerse.logout');
    Route::get('hilfe',         [BoerseController::class, 'hilfe'])->name('boerse.hilfe');
    Route::get('hilfe/drucken', [BoerseController::class, 'hilfeDrucken'])->name('boerse.hilfe.drucken');

    // Öffentliches Kurs-Display (Monitor-Ansicht, kein Login)
    Route::get('anzeige',       [BoerseController::class, 'anzeige'])->name('boerse.anzeige');
    Route::get('anzeige/daten', [BoerseController::class, 'anzeigedaten'])->name('boerse.anzeige.daten');

    // Geschützt
    Route::middleware('hasBoerse')->group(function () {
        Route::get('/', [BoerseController::class, 'dashboard'])->name('boerse.index');

        // Handel
        Route::get('handel',                        [BoerseHandelController::class, 'index'])->name('boerse.handel');
        Route::get('handel/suche/kinder',           [BoerseHandelController::class, 'searchKinder']);
        Route::get('handel/suche/inhaber/{customer}', [BoerseHandelController::class, 'searchInhaber']);
        Route::get('handel/{customer}/kaufen',      [BoerseHandelController::class, 'kaufenForm']);
        Route::post('handel/{customer}/kaufen',     [BoerseHandelController::class, 'kaufen']);
        Route::get('handel/{customer}/verkaufen',   [BoerseHandelController::class, 'verkaufenForm']);
        Route::post('handel/{customer}/verkaufen',  [BoerseHandelController::class, 'verkaufen']);
        Route::get('handel/{customer}/rueckkauf',   [BoerseHandelController::class, 'rueckkaufForm']);
        Route::post('handel/{customer}/rueckkauf',  [BoerseHandelController::class, 'rueckkauf']);

        // Erfassung
        Route::get('erfassung',                       [BoerseErfassungController::class, 'index'])->name('boerse.erfassung');
        Route::post('erfassung/{customer}',           [BoerseErfassungController::class, 'store']);
        Route::get('erfassung/vorschau/{customer}',   [BoerseErfassungController::class, 'vorschau']);

        // Kasse
        Route::get('kasse',              [BoerseKasseController::class, 'index'])->name('boerse.kasse');
        Route::post('kasse/einlage',     [BoerseKasseController::class, 'einlage']);
        Route::post('kasse/entnahme',    [BoerseKasseController::class, 'entnahme']);
        Route::post('kasse/bestaetigen', [BoerseKasseController::class, 'bestaetigen']);

        // Kurse & Berichte
        Route::get('kurse',                          [BoerseKurseController::class, 'index'])->name('boerse.kurse');
        Route::get('kurse/{customer}',               [BoerseKurseController::class, 'verlauf']);
        Route::get('bericht/kurstafel',              [BoerseKurseController::class, 'kurstafel']);
        Route::post('bericht/kurstafel/bestaetigen', [BoerseKurseController::class, 'kurstafelBestaetigen']);
        Route::get('bericht/tagesabschluss',         [BoerseKurseController::class, 'tagesabschluss']);
    });
});

/*
|--------------------------------------------------------------------------
| Admin: Börse
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('boerse',                            [AdminBoerseController::class, 'index'])->name('admin.boerse');
    Route::get('boerse/aufgaben',                   [AdminBoerseController::class, 'aufgaben'])->name('admin.boerse.aufgaben');
    Route::get('boerse/aktivieren',                 [AdminBoerseController::class, 'aktivierenForm']);
    Route::post('boerse/aktivieren',                [AdminBoerseController::class, 'aktivieren']);
    Route::post('boerse/{customer}/deaktivieren',   [AdminBoerseController::class, 'deaktivieren']);
    Route::get('boerse/{customer}/kurs',            [AdminBoerseController::class, 'kursForm']);
    Route::post('boerse/{customer}/kurs',           [AdminBoerseController::class, 'kursUpdate']);
    Route::get('boerse/dividende/neu',              [AdminBoerseController::class, 'dividendeForm']);
    Route::post('boerse/dividende',                 [AdminBoerseController::class, 'dividende']);
    Route::get('boerse/abschluss/vorbereiten',      [AdminBoerseController::class, 'abschlussForm']);
    Route::post('boerse/abschluss',                 [AdminBoerseController::class, 'abschluss']);
    Route::get('boerse/pin',                        [AdminBoerseController::class, 'pinForm']);
    Route::post('boerse/pin',                       [AdminBoerseController::class, 'pinUpdate']);
    Route::post('boerse/markierung',                [AdminBoerseController::class, 'setBoerseBetrieb']);
    Route::post('boerse/markierung/entfernen',      [AdminBoerseController::class, 'clearBoerseBetrieb']);
    Route::get('boerse/bericht',                    [AdminBoerseController::class, 'bericht']);
});

