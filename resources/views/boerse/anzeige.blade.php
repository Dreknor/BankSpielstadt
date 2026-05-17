<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📈 Radi-Börse — Kurstafel</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ─── Grundlayout ─────────────────────────────────────────────── */
        * { box-sizing: border-box; }
        body {
            background: #0d1117;
            color: #e6edf3;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ─── Karten-Rahmen je Richtung ───────────────────────────────── */
        .kachel {
            border: 2px solid #30363d;
            border-radius: 1.25rem;
            background: #161b22;
            transition: border-color 0.4s, box-shadow 0.4s;
        }
        .kachel-hoch {
            border-color: #238636;
            box-shadow: 0 0 28px rgba(35,134,54,0.35);
        }
        .kachel-runter {
            border-color: #da3633;
            box-shadow: 0 0 28px rgba(218,54,51,0.35);
        }
        .kachel-gleich {
            border-color: #d29922;
            box-shadow: 0 0 16px rgba(210,153,34,0.2);
        }

        /* ─── Preis-Farben ────────────────────────────────────────────── */
        .preis-hoch   { color: #3fb950; }
        .preis-runter { color: #f85149; }
        .preis-gleich { color: #d29922; }

        /* ─── Flash-Animationen beim Update ──────────────────────────── */
        @keyframes blitz-hoch {
            0%   { background: #161b22; }
            30%  { background: #0d2818; }
            100% { background: #161b22; }
        }
        @keyframes blitz-runter {
            0%   { background: #161b22; }
            30%  { background: #2d0f0e; }
            100% { background: #161b22; }
        }
        .flash-hoch   { animation: blitz-hoch   1.2s ease-in-out; }
        .flash-runter { animation: blitz-runter 1.2s ease-in-out; }

        /* ─── Ticker-Band ─────────────────────────────────────────────── */
        @keyframes ticker {
            0%   { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }
        .ticker-track {
            display: flex;
            white-space: nowrap;
            animation: ticker 28s linear infinite;
        }
        .ticker-track:hover { animation-play-state: paused; }

        /* ─── Puls-Animation für Kursänderung ─────────────────────────── */
        @keyframes puls {
            0%,100% { transform: scale(1); }
            50%      { transform: scale(1.08); }
        }
        .puls { animation: puls 0.5s ease-in-out; }

        /* ─── Progress-Bar für Anteil-Verteilung ─────────────────────── */
        .bar-bg { background: #21262d; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 8px; border-radius: 999px; transition: width 0.6s ease; }
    </style>
</head>
<body>

{{-- ════════════════════════════════════════════════════════ HEADER ══ --}}
<header style="background: linear-gradient(135deg,#1a1000 0%,#2d1f00 50%,#1a1000 100%);
               border-bottom: 2px solid #d29922;">
    <div class="max-w-screen-2xl mx-auto px-6 py-4 flex items-center justify-between gap-4">

        {{-- Logo --}}
        <div class="flex items-center gap-3 flex-shrink-0">
            <span class="text-5xl leading-none">📈</span>
            <div>
                <div class="text-2xl font-black tracking-widest" style="color:#ffd700;">RADI-BÖRSE</div>
                <div class="text-xs font-semibold tracking-[0.3em] text-amber-400 uppercase">Kinderspielstadt</div>
            </div>
        </div>

        {{-- Titel --}}
        <div class="hidden md:block text-center">
            <div class="text-xl font-bold tracking-widest text-amber-200 uppercase">Aktuelle Kurse</div>
            <div class="text-xs text-amber-400 mt-0.5">Kursanzeige · automatische Aktualisierung</div>
        </div>

        {{-- Uhrzeit --}}
        <div class="text-right flex-shrink-0">
            <div id="uhr" class="text-4xl font-black tabular-nums" style="color:#ffd700; font-variant-numeric:tabular-nums;">--:--:--</div>
            <div id="datum" class="text-sm text-amber-400 mt-0.5"></div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════ TICKER-BAND ══ --}}
<div style="background:#161b22; border-bottom:1px solid #21262d; overflow:hidden; height:36px; display:flex; align-items:center;">
    <div class="ticker-track text-sm font-semibold" id="ticker-band" style="color:#8b949e;">
        @foreach($betriebe as $b)
            @php
                $color = $b['richtung'] === 'hoch' ? '#3fb950' : ($b['richtung'] === 'runter' ? '#f85149' : '#ffd700');
                $pfeil = $b['richtung'] === 'hoch' ? '▲' : ($b['richtung'] === 'runter' ? '▼' : '▬');
                $sign  = $b['aenderung'] >= 0 ? '+' : '';
            @endphp
            <span class="mx-6" style="color:{{ $color }}">
                {{ $b['name'] }} &nbsp;
                <span style="color:#ffd700">{{ $b['kurs'] }} Radi</span>
                &nbsp;{{ $pfeil }} {{ $sign }}{{ $b['aenderung'] }} ({{ $sign }}{{ $b['aenderung_pct'] }}%)
            </span>
            <span class="mx-2" style="color:#30363d;">│</span>
        @endforeach
        {{-- dupliziert für nahtlosen Loop --}}
        @foreach($betriebe as $b)
            @php
                $color = $b['richtung'] === 'hoch' ? '#3fb950' : ($b['richtung'] === 'runter' ? '#f85149' : '#ffd700');
                $pfeil = $b['richtung'] === 'hoch' ? '▲' : ($b['richtung'] === 'runter' ? '▼' : '▬');
                $sign  = $b['aenderung'] >= 0 ? '+' : '';
            @endphp
            <span class="mx-6" style="color:{{ $color }}">
                {{ $b['name'] }} &nbsp;
                <span style="color:#ffd700">{{ $b['kurs'] }} Radi</span>
                &nbsp;{{ $pfeil }} {{ $sign }}{{ $b['aenderung'] }} ({{ $sign }}{{ $b['aenderung_pct'] }}%)
            </span>
            <span class="mx-2" style="color:#30363d;">│</span>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ KURS-GRID ══ --}}
<main class="flex-1 p-6">
    <div id="kurse-grid" class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        {{-- Initiales Rendering durch PHP --}}
        @forelse($betriebe as $b)
            @php
                $richtungCls = $b['richtung'] === 'hoch' ? 'kachel-hoch' : ($b['richtung'] === 'runter' ? 'kachel-runter' : 'kachel-gleich');
                $preisCls    = $b['richtung'] === 'hoch' ? 'preis-hoch' : ($b['richtung'] === 'runter' ? 'preis-runter' : 'preis-gleich');
                $pfeil       = $b['richtung'] === 'hoch' ? '▲' : ($b['richtung'] === 'runter' ? '▼' : '▬');
                $sign        = $b['aenderung'] >= 0 ? '+' : '';
                $sparkClr    = $b['richtung'] === 'hoch' ? '#3fb950' : ($b['richtung'] === 'runter' ? '#f85149' : '#fbbf24');
                $pctVerkauft = $b['anteile_gesamt'] > 0 ? round($b['anteile_verkauft'] / $b['anteile_gesamt'] * 100) : 0;
            @endphp
            <div class="kachel {{ $richtungCls }} p-6" data-id="{{ $b['id'] }}">

                {{-- Name --}}
                <div class="text-slate-400 text-base font-semibold uppercase tracking-widest truncate mb-1">
                    {{ $b['name'] }}
                </div>

                {{-- Kurs --}}
                <div class="font-black leading-none mt-2" style="font-size:clamp(2.5rem,5vw,4rem);">
                    <span class="kurs-wert" style="color:#ffd700;">{{ $b['kurs'] }}</span>
                    <span class="text-xl font-semibold text-slate-500"> Radi</span>
                </div>

                {{-- Änderung --}}
                <div class="mt-3 text-2xl font-bold {{ $preisCls }} aenderung-wert">
                    {{ $pfeil }} {{ $sign }}{{ $b['aenderung'] }} Radi
                    <span class="text-lg font-semibold opacity-80">({{ $sign }}{{ $b['aenderung_pct'] }}%)</span>
                </div>

                {{-- Sparkline --}}
                <div class="sparkline-wrap mt-4" style="color:{{ $sparkClr }}; height:56px;">
                    {{-- wird via JS gefüllt --}}
                </div>

                {{-- Separator --}}
                <div class="my-4" style="border-top:1px solid #21262d;"></div>

                {{-- Anteile --}}
                <div class="flex justify-between text-sm text-slate-500 mb-2">
                    <span>{{ $b['anteile_verkauft'] }} vergeben</span>
                    <span style="color:#8b949e;">von {{ $b['anteile_gesamt'] }}</span>
                    <span class="{{ $b['anteile_frei'] > 0 ? 'text-amber-400' : 'text-slate-600' }}">
                        {{ $b['anteile_frei'] }} frei
                    </span>
                </div>
                <div class="bar-bg">
                    <div class="bar-fill" style="width:{{ $pctVerkauft }}%; background:{{ $sparkClr }};"></div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-24 text-slate-600">
                <div class="text-6xl mb-4">📉</div>
                <div class="text-2xl font-bold">Noch keine Betriebe an der Börse</div>
                <div class="text-slate-500 mt-2">Der Admin muss zuerst Betriebe freischalten.</div>
            </div>
        @endforelse
    </div>
</main>

{{-- ═══════════════════════════════════════════════════════ FOOTER ══ --}}
<footer style="background:#161b22; border-top:1px solid #21262d;">
    <div class="max-w-screen-2xl mx-auto px-6 py-3 flex items-center justify-between text-sm flex-wrap gap-2">
        <div class="text-slate-500">
            🔄 Nächste Aktualisierung in
            <span id="countdown" class="font-bold text-amber-400">60</span>s
        </div>
        <div class="text-slate-600">
            Zuletzt: <span id="letzte-aktualisierung" class="text-slate-400">—</span>
        </div>
        <div style="color:#30363d;">
            <a href="/boerse/login" class="hover:text-amber-400 transition">🔐 Börsenmitarbeiter-Login</a>
        </div>
    </div>
    {{-- schmaler Ladebalken oben beim Refresh --}}
    <div id="ladebalken" style="height:3px; background:transparent; position:relative; overflow:hidden;">
        <div id="ladebalken-inner" style="height:100%; width:0%; background:#ffd700; transition:width 0.4s ease;"></div>
    </div>
</footer>

{{-- ══════════════════════════════════════════════════════ JAVASCRIPT ══ --}}
<script>
    // ── Initiale Daten aus PHP ─────────────────────────────────────────────
    let daten = @json($betriebe);
    const INTERVALL = 60; // Sekunden zwischen Aktualisierungen

    // ── Uhrzeit ────────────────────────────────────────────────────────────
    function aktualisiereUhr() {
        const jetzt = new Date();
        const uhr = jetzt.toLocaleTimeString('de-DE', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        const dat = jetzt.toLocaleDateString('de-DE', { weekday:'long', day:'2-digit', month:'long', year:'numeric' });
        document.getElementById('uhr').textContent  = uhr;
        document.getElementById('datum').textContent = dat;
    }
    setInterval(aktualisiereUhr, 1000);
    aktualisiereUhr();

    // ── Countdown + automatischer Refresh ────────────────────────────────
    let countdown = INTERVALL;

    setInterval(() => {
        countdown--;
        document.getElementById('countdown').textContent = countdown;
        if (countdown <= 0) {
            holeNeueDaten();
            countdown = INTERVALL;
        }
    }, 1000);

    async function holeNeueDaten() {
        zeigeLadebalken();
        try {
            const res = await fetch('/boerse/anzeige/daten', { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const neueDaten = await res.json();

            animiereAenderungen(daten, neueDaten);
            daten = neueDaten;
            aktualisiereGrid(daten);
            aktualisiereTickerBand(daten);

            document.getElementById('letzte-aktualisierung').textContent =
                new Date().toLocaleTimeString('de-DE');
        } catch (err) {
            console.warn('Aktualisierung fehlgeschlagen:', err);
        }
        versteckeLadebalken();
    }

    // ── Sparkline (SVG-Linie) ──────────────────────────────────────────────
    function sparkline(preise, w = 260, h = 56) {
        if (!preise || preise.length < 2) {
            return `<svg viewBox="0 0 ${w} ${h}" style="width:100%;height:${h}px">
                        <line x1="0" y1="${h/2}" x2="${w}" y2="${h/2}"
                              stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.3"/>
                    </svg>`;
        }
        const pad  = 6;
        const min  = Math.min(...preise);
        const max  = Math.max(...preise);
        const span = max - min || 1;
        const n    = preise.length;

        const punkte = preise.map((p, i) => {
            const x = pad + (i / (n - 1)) * (w - 2 * pad);
            const y = h - pad - ((p - min) / span) * (h - 2 * pad);
            return { x: +x.toFixed(2), y: +y.toFixed(2) };
        });

        // Fläche unterhalb der Linie (Gradient-Füllung)
        const flaechePunkte = [
            `${punkte[0].x},${h}`,
            ...punkte.map(p => `${p.x},${p.y}`),
            `${punkte[n-1].x},${h}`,
        ].join(' ');

        const liniePunkte = punkte.map(p => `${p.x},${p.y}`).join(' ');
        const letzterP    = punkte[n - 1];

        const farbId = 'grad_' + Math.random().toString(36).slice(2, 8);

        return `<svg viewBox="0 0 ${w} ${h}" style="width:100%;height:${h}px" overflow="visible">
            <defs>
                <linearGradient id="${farbId}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="currentColor" stop-opacity="0.25"/>
                    <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <polygon points="${flaechePunkte}" fill="url(#${farbId})"/>
            <polyline points="${liniePunkte}"
                      fill="none" stroke="currentColor"
                      stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="${letzterP.x}" cy="${letzterP.y}" r="4"
                    fill="#0d1117" stroke="currentColor" stroke-width="2.5"/>
        </svg>`;
    }

    // ── Eine Kachel als HTML rendern ─────────────────────────────────────
    function renderKachel(b) {
        const richtungCls = b.richtung === 'hoch' ? 'kachel-hoch'
                          : b.richtung === 'runter' ? 'kachel-runter' : 'kachel-gleich';
        const preisCls    = b.richtung === 'hoch' ? 'preis-hoch'
                          : b.richtung === 'runter' ? 'preis-runter' : 'preis-gleich';
        const sparkClr    = b.richtung === 'hoch' ? '#3fb950'
                          : b.richtung === 'runter' ? '#f85149' : '#fbbf24';
        const pfeil       = b.richtung === 'hoch' ? '▲'
                          : b.richtung === 'runter' ? '▼' : '▬';
        const sign        = b.aenderung >= 0 ? '+' : '';
        const pct         = b.anteile_gesamt > 0
                            ? Math.round(b.anteile_verkauft / b.anteile_gesamt * 100) : 0;
        const freiCls     = b.anteile_frei > 0 ? 'color:#fbbf24' : 'color:#4b5563';

        return `<div class="kachel ${richtungCls} p-6" data-id="${b.id}">

            <div style="color:#8b949e; font-size:0.78rem; font-weight:700;
                        letter-spacing:0.15em; text-transform:uppercase; truncate; margin-bottom:4px;">
                ${escHtml(b.name)}
            </div>

            <div class="font-black leading-none" style="font-size:clamp(2.5rem,5vw,4rem); margin-top:8px;">
                <span class="kurs-wert" style="color:#ffd700;">${b.kurs}</span>
                <span style="font-size:1.2rem; font-weight:600; color:#4b5563;"> Radi</span>
            </div>

            <div class="${preisCls} aenderung-wert"
                 style="margin-top:12px; font-size:1.5rem; font-weight:700;">
                ${pfeil} ${sign}${b.aenderung} Radi
                <span style="font-size:1rem; opacity:0.8;">(${sign}${b.aenderung_pct}%)</span>
            </div>

            <div class="sparkline-wrap" style="color:${sparkClr}; height:56px; margin-top:16px;">
                ${sparkline(b.verlauf_preise)}
            </div>

            <div style="border-top:1px solid #21262d; margin:16px 0;"></div>

            <div style="display:flex; justify-content:space-between;
                        font-size:0.8rem; color:#6b7280; margin-bottom:8px;">
                <span>${b.anteile_verkauft} vergeben</span>
                <span style="color:#8b949e;">von ${b.anteile_gesamt}</span>
                <span style="${freiCls}">${b.anteile_frei} frei</span>
            </div>
            <div class="bar-bg">
                <div class="bar-fill"
                     style="width:${pct}%; background:${sparkClr};"></div>
            </div>
        </div>`;
    }

    // ── Gesamtes Grid neu aufbauen ─────────────────────────────────────
    function aktualisiereGrid(betriebe) {
        const grid = document.getElementById('kurse-grid');
        if (!betriebe || betriebe.length === 0) {
            grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:6rem 0; color:#4b5563;">
                <div style="font-size:4rem;">📉</div>
                <div style="font-size:1.5rem; font-weight:700; margin-top:1rem;">Noch keine Betriebe an der Börse</div>
            </div>`;
            return;
        }
        grid.innerHTML = betriebe.map(renderKachel).join('');
        // Sparklines initial zeichnen
        betriebe.forEach(b => {
            const el = document.querySelector(`[data-id="${b.id}"] .sparkline-wrap`);
            if (el) el.innerHTML = sparkline(b.verlauf_preise);
        });
    }

    // ── Animations-Flash bei Kursänderung ────────────────────────────
    function animiereAenderungen(alt, neu) {
        neu.forEach(b => {
            const altB = alt.find(a => a.id === b.id);
            if (!altB || altB.kurs === b.kurs) return;
            const el = document.querySelector(`[data-id="${b.id}"]`);
            if (!el) return;
            const cls = b.kurs > altB.kurs ? 'flash-hoch' : 'flash-runter';
            el.classList.remove('flash-hoch', 'flash-runter');
            void el.offsetWidth; // reflow für Animation-Neustart
            el.classList.add(cls);
            // Preis-Puls
            const preisTxt = el.querySelector('.kurs-wert');
            if (preisTxt) {
                preisTxt.classList.remove('puls');
                void preisTxt.offsetWidth;
                preisTxt.classList.add('puls');
            }
            setTimeout(() => el.classList.remove(cls), 1300);
        });
    }

    // ── Ticker-Band aktualisieren ─────────────────────────────────────
    function aktualisiereTickerBand(betriebe) {
        const eintrag = b => {
            const clr  = b.richtung === 'hoch' ? '#3fb950'
                       : b.richtung === 'runter' ? '#f85149' : '#ffd700';
            const pf   = b.richtung === 'hoch' ? '▲' : b.richtung === 'runter' ? '▼' : '▬';
            const sign = b.aenderung >= 0 ? '+' : '';
            return `<span style="color:${clr};margin:0 1.5rem;">
                        ${escHtml(b.name)} &nbsp;
                        <span style="color:#ffd700;">${b.kurs} Radi</span>
                        &nbsp;${pf} ${sign}${b.aenderung} (${sign}${b.aenderung_pct}%)
                    </span>
                    <span style="color:#30363d;margin:0 0.5rem;">│</span>`;
        };
        const html = betriebe.map(eintrag).join('') + betriebe.map(eintrag).join('');
        document.getElementById('ticker-band').innerHTML = html;
    }

    // ── Ladebalken ────────────────────────────────────────────────────
    function zeigeLadebalken() {
        const lb = document.getElementById('ladebalken-inner');
        lb.style.width = '0%';
        lb.style.background = '#ffd700';
        lb.style.transition = 'none';
        setTimeout(() => { lb.style.transition = 'width 1.5s ease'; lb.style.width = '85%'; }, 10);
    }
    function versteckeLadebalken() {
        const lb = document.getElementById('ladebalken-inner');
        lb.style.transition = 'width 0.3s ease';
        lb.style.width = '100%';
        setTimeout(() => { lb.style.width = '0%'; lb.style.transition = 'none'; }, 400);
    }

    // ── HTML escapen ──────────────────────────────────────────────────
    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // ── Beim ersten Laden Sparklines zeichnen ─────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        daten.forEach(b => {
            const el = document.querySelector(`[data-id="${b.id}"] .sparkline-wrap`);
            if (el) el.innerHTML = sparkline(b.verlauf_preise);
        });
    });
</script>
</body>
</html>

