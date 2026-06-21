@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.benutzer.index') }}" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-extrabold">
            <i class="fa-solid fa-user-plus text-brand-600 mr-2"></i>
            Neuen Benutzer anlegen
        </h1>
    </div>

    <div class="card p-6 space-y-5">
        <form action="{{ route('admin.benutzer.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1" for="name">Name</label>
                <input type="text" name="name" id="name"
                       value="{{ old('name') }}"
                       placeholder="z. B. Frau Müller"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-400"
                       required>
                @error('name')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="email">E-Mail-Adresse</label>
                <input type="email" name="email" id="email"
                       value="{{ old('email') }}"
                       placeholder="banker@schule.de"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-400"
                       required>
                @error('email')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="password">Passwort</label>
                <input type="password" name="password" id="password"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-400"
                       required minlength="8">
                @error('password')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="password_confirmation">Passwort bestätigen</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-400"
                       required minlength="8">
            </div>

            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3">
                <p class="text-sm font-semibold text-slate-600">Berechtigungen</p>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_manager" value="0">
                    <input type="checkbox" name="is_manager" value="1"
                           {{ old('is_manager') ? 'checked' : '' }}
                           class="w-5 h-5 rounded accent-brand-600">
                    <span class="text-sm">
                        <strong>Manager</strong> – darf neue Kunden anlegen
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_admin" value="0">
                    <input type="checkbox" name="is_admin" value="1"
                           {{ old('is_admin') ? 'checked' : '' }}
                           class="w-5 h-5 rounded accent-rose-600">
                    <span class="text-sm">
                        <strong>Admin</strong> – darf alles verwalten (nur für Lehrkräfte!)
                    </span>
                </label>

                <hr class="border-slate-200">

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="kann_einzahlen" value="0">
                    <input type="checkbox" name="kann_einzahlen" value="1"
                           {{ old('kann_einzahlen', '1') ? 'checked' : '' }}
                           class="w-5 h-5 rounded accent-sky-600">
                    <span class="text-sm">
                        <strong>Einzahlen</strong> – darf Geld einzahlen
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="kann_auszahlen" value="0">
                    <input type="checkbox" name="kann_auszahlen" value="1"
                           {{ old('kann_auszahlen', '1') ? 'checked' : '' }}
                           class="w-5 h-5 rounded accent-orange-600">
                    <span class="text-sm">
                        <strong>Auszahlen</strong> – darf Geld auszahlen
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="kann_arbeitszeit" value="0">
                    <input type="checkbox" name="kann_arbeitszeit" value="1"
                           {{ old('kann_arbeitszeit', '1') ? 'checked' : '' }}
                           class="w-5 h-5 rounded accent-violet-600">
                    <span class="text-sm">
                        <strong>Arbeitszeit</strong> – darf Arbeitszeiten erfassen und Lohn berechnen
                    </span>
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-lg">
                <i class="fa-solid fa-check mr-2"></i>Benutzer anlegen
            </button>
        </form>
    </div>
</div>
@endsection

