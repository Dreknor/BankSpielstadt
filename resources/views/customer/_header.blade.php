{{-- Wiederverwendbarer Kunden-Kopf für Geld-Flows --}}
<div class="p-6 flex flex-col md:flex-row items-center gap-4 border-b-2 border-slate-100">
    <div class="flex-1 text-center md:text-left">
        <div class="text-sm text-slate-500 uppercase font-semibold">Kunde</div>
        <h1 class="text-3xl font-extrabold">{{ session('customer')->name }}</h1>
    </div>
    <div class="text-center md:text-right">
        <div class="text-sm text-slate-500 uppercase font-semibold">Kontostand</div>
        <div class="text-3xl font-extrabold {{ session('customer')->balance > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
            {{ session('customer')->balance }} Radi
        </div>
    </div>
</div>

