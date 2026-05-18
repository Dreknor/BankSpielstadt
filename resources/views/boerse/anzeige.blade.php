<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📈 Radi-Börse — Kurstafel</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ─── Grundlayout ─────────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100vh;
            overflow: hidden;
            background: #0d1117;
            color: #e6edf3;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* ─── Karten-Rahmen je Richtung ───────────────────────────────── */
        .kachel {
            border: 2px solid #30363d;
            border-radius: 1.25rem;
            background: #161b22;
            transition: border-color 0.4s, box-shadow 0.4s;
        }
        .kachel-hoch   { border-color: #238636; box-shadow: 0 0 28px rgba(35,134,54,0.35); }
        .kachel-runter { border-color: #da3633; box-shadow: 0 0 28px rgba(218,54,51,0.35); }
        .kachel-gleich { border-color: #d29922; box-shadow: 0 0 16px rgba(210,153,34,0.2); }

        /* ─── Preis-Farben ────────────────────────────────────────────── */
        .preis-hoch   { color: #3fb950; }
        .preis-runter { color: #f85149; }
        .preis-gleich { color: #d29922; }

        /* ─── Flash-Animationen beim Update ──────────────────────────── */
        @keyframes blitz-hoch   { 0%{background:#161b22} 30%{background:#0d2818} 100%{background:#161b22} }
        @keyframes blitz-runter { 0%{background:#161b22} 30%{background:#2d0f0e} 100%{background:#161b22} }
        .flash-hoch   { animation: blitz-hoch   1.2s ease-in-out; }
        .flash-runter { animation: blitz-runter 1.2s ease-in-out; }

        /* ─── Ticker-Band ─────────────────────────────────────────────── */
        @keyframes ticker {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track {
            display: inline-flex;
            white-space: nowrap;
            animation: ticker 120s linear infinite;
        }
        .ticker-track:hover { animation-play-state: paused; }

        /* ─── Puls-Animation für Kursänderung ─────────────────────────── */
        @keyframes puls { 0%,100%{transform:scale(1)} 50%{transform:scale(1.08)} }
        .puls { animation: puls 0.5s ease-in-out; }

        /* ─── Progress-Bar für Anteil-Verteilung ─────────────────────── */
        .bar-bg   { background: #21262d; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 8px; border-radius: 999px; transition: width 0.6s ease; }

        /* ─── Seiten-Fade ─────────────────────────────────────────────── */
        @keyframes seite-rein  { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes seite-raus  { from { opacity: 1; transform: translateY(0); }   to { opacity: 0; transform: translateY(-12px); } }
        .fade-rein { animation: seite-rein  0.5s ease forwards; }
        .fade-raus { animation: seite-raus  0.4s ease forwards; }

        /* ─── Main-Bereich exakt einfüllen ───────────────────────────── */
        #haupt-bereich {
            flex: 1;
            overflow: hidden;
            padding: 1rem 1.5rem;
            display: flex;
            flex-direction: column;
        }
        #kurse-grid {
            flex: 1;
            display: grid;
            gap: 1rem;
            overflow: hidden;
            /* Spalten werden per JS gesetzt */
        }
        #kurse-grid .kachel {
            overflow: hidden;
            padding: 1rem 1.2rem;
            min-height: 0; /* Grid-Kinder dürfen schrumpfen */
        }

        /* ─── Footer kompakt ─────────────────────────────────────────── */
        #boerse-footer {
            background: #161b22;
            border-top: 1px solid #21262d;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

{{-- ════════════════════════════════════════════════════════ HEADER ══ --}}
<header id="boerse-header" style="background:linear-gradient(135deg,#1a1000 0%,#2d1f00 50%,#1a1000 100%);
               border-bottom:2px solid #d29922; flex-shrink:0;">
    <div style="max-width:100%; padding:0.75rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div style="display:flex; align-items:center; gap:0.75rem; flex-shrink:0;">
            <span style="font-size:2.5rem; line-height:1;">📈</span>
            <div>
                <div style="font-size:1.5rem; font-weight:900; letter-spacing:0.15em; color:#ffd700;">RADI-BÖRSE</div>
                <div style="font-size:0.7rem; font-weight:700; letter-spacing:0.3em; color:#fbbf24; text-transform:uppercase;">Kinderspielstadt</div>
            </div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:1rem; font-weight:700; letter-spacing:0.2em; color:#fde68a; text-transform:uppercase;">Aktuelle Kurse</div>
            <div style="font-size:0.7rem; color:#fbbf24; margin-top:2px;">automatische Aktualisierung</div>
        </div>
        <div style="text-align:right; flex-shrink:0;">
            <div id="uhr" style="font-size:2.2rem; font-weight:900; color:#ffd700; font-variant-numeric:tabular-nums;">--:--:--</div>
            <div id="datum" style="font-size:0.72rem; color:#fbbf24; margin-top:2px;"></div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════ TICKER-BAND ══ --}}
<div id="ticker-container" style="background:#161b22; border-bottom:1px solid #21262d;
     height:34px; overflow:hidden; display:flex; align-items:center; flex-shrink:0;">
    <div class="ticker-track text-sm font-semibold" id="ticker-band" style="color:#8b949e;">
        @foreach($betriebe as $b)
            @php
                $color = $b['richtung'] === 'hoch' ? '#3fb950' : ($b['richtung'] === 'runter' ? '#f85149' : '#ffd700');
                $pfeil = $b['richtung'] === 'hoch' ? '▲' : ($b['richtung'] === 'runter' ? '▼' : '▬');
                $sign  = $b['aenderung'] >= 0 ? '+' : '';
            @endphp
            <span style="color:{{ $color }}; margin:0 1.5rem;">
                {{ $b['name'] }} &nbsp;
                <span style="color:#ffd700;">{{ $b['kurs'] }} Radi</span>
                &nbsp;{{ $pfeil }} {{ $sign }}{{ $b['aenderung'] }} ({{ $sign }}{{ $b['aenderung_pct'] }}%)
            </span>
            <span style="color:#30363d; margin:0 0.5rem;">│</span>
        @endforeach
        {{-- dupliziert für nahtlosen Loop --}}
        @foreach($betriebe as $b)
            @php
                $color = $b['richtung'] === 'hoch' ? '#3fb950' : ($b['richtung'] === 'runter' ? '#f85149' : '#ffd700');
                $pfeil = $b['richtung'] === 'hoch' ? '▲' : ($b['richtung'] === 'runter' ? '▼' : '▬');
                $sign  = $b['aenderung'] >= 0 ? '+' : '';
            @endphp
            <span style="color:{{ $color }}; margin:0 1.5rem;">
                {{ $b['name'] }} &nbsp;
                <span style="color:#ffd700;">{{ $b['kurs'] }} Radi</span>
                &nbsp;{{ $pfeil }} {{ $sign }}{{ $b['aenderung'] }} ({{ $sign }}{{ $b['aenderung_pct'] }}%)
            </span>
            <span style="color:#30363d; margin:0 0.5rem;">│</span>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ KURS-GRID ══ --}}
<main id="haupt-bereich">
    <div id="kurse-grid">
        {{-- wird via JS befüllt --}}
    </div>
</main>

{{-- ═══════════════════════════════════════════════════════ FOOTER ══ --}}
<footer id="boerse-footer">
    <div style="padding:0.4rem 1.5rem; display:flex; align-items:center; justify-content:space-between; font-size:0.78rem; flex-wrap:wrap; gap:0.5rem;">
        <div style="color:#6b7280;">
            🔄 Nächste Aktualisierung in
            <span id="countdown" style="font-weight:700; color:#fbbf24;">60</span>s
        </div>
        <div id="seiten-anzeige" style="display:flex; gap:6px; align-items:center;"></div>
        <div style="color:#4b5563;">
            Zuletzt: <span id="letzte-aktualisierung" style="color:#6b7280;">—</span>
            &nbsp;·&nbsp;
            <a href="/boerse/login" style="color:#4b5563; text-decoration:none;">🔐 Login</a>
        </div>
    </div>
    <div id="ladebalken" style="height:3px; overflow:hidden; position:relative;">
        <div id="ladebalken-inner" style="height:100%; width:0%; background:#ffd700; transition:width 0.4s ease;"></div>
    </div>
</footer>

{{-- ══════════════════════════════════════════════════════ JAVASCRIPT ══ --}}
<script>
    // ── Initiale Daten aus PHP ─────────────────────────────────────────────
    let daten = @json($betriebe);
    const DATEN_INTERVALL  = 60;   // Sekunden bis Daten-Refresh vom Server
    const SEITEN_INTERVALL = 10;   // Sekunden pro Seite

    let aktuelleSeite  = 0;
    let anzahlSeiten   = 1;
    let seitenTimer    = null;
    let seitenWechselLaeuft = false;

    // ── Uhrzeit ────────────────────────────────────────────────────────────
    function aktualisiereUhr() {
        const n = new Date();
        document.getElementById('uhr').textContent   = n.toLocaleTimeString('de-DE', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
        document.getElementById('datum').textContent = n.toLocaleDateString('de-DE', {weekday:'long',day:'2-digit',month:'long',year:'numeric'});
    }
    setInterval(aktualisiereUhr, 1000);
    aktualisiereUhr();

    // ── Countdown bis Daten-Refresh ───────────────────────────────────────
    let countdown = DATEN_INTERVALL;
    setInterval(() => {
        countdown--;
        document.getElementById('countdown').textContent = countdown;
        if (countdown <= 0) { holeNeueDaten(); countdown = DATEN_INTERVALL; }
    }, 1000);

    // ── Karten pro Seite berechnen ────────────────────────────────────────
    function kartenProSeite() {
        const headerH  = document.getElementById('boerse-header').offsetHeight;
        const tickerH  = document.getElementById('ticker-container').offsetHeight;
        const footerH  = document.getElementById('boerse-footer').offsetHeight;
        const padding  = 32; // 1rem oben + unten
        const gap      = 16;
        const verfH    = window.innerHeight - headerH - tickerH - footerH - padding;
        const verfB    = window.innerWidth  - 48; // 1.5rem links+rechts

        // Spalten nach Breite
        let cols = 1;
        if (verfB >= 1400) cols = 4;
        else if (verfB >= 1000) cols = 3;
        else if (verfB >= 640)  cols = 2;

        // Zeilen nach Höhe (Kachel-Mindesthöhe ~220px)
        const minKartenH = 220;
        const rows = Math.max(1, Math.floor((verfH + gap) / (minKartenH + gap)));

        return { cols, rows, pro: cols * rows };
    }

    // ── Grid-Spalten und Zeilenhöhe setzen ───────────────────────────────
    function setzeGridLayout(cols, rows) {
        const grid = document.getElementById('kurse-grid');
        grid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        grid.style.gridTemplateRows    = `repeat(${rows}, 1fr)`;
    }

    // ── Seite rendern ─────────────────────────────────────────────────────
    function zeigeSeite(nr, animation = true) {
        if (seitenWechselLaeuft) return;

        const { cols, rows, pro } = kartenProSeite();
        anzahlSeiten = Math.max(1, Math.ceil(daten.length / pro));
        aktuelleSeite = ((nr % anzahlSeiten) + anzahlSeiten) % anzahlSeiten;

        const start  = aktuelleSeite * pro;
        const slice  = daten.slice(start, start + pro);

        const grid = document.getElementById('kurse-grid');
        setzeGridLayout(cols, rows);

        if (animation && grid.children.length > 0) {
            seitenWechselLaeuft = true;
            grid.classList.add('fade-raus');
            setTimeout(() => {
                grid.classList.remove('fade-raus');
                grid.innerHTML = slice.map(renderKachel).join('');
                zeichneSparklines(slice);
                grid.classList.add('fade-rein');
                setTimeout(() => { grid.classList.remove('fade-rein'); seitenWechselLaeuft = false; }, 520);
            }, 420);
        } else {
            grid.innerHTML = slice.map(renderKachel).join('');
            zeichneSparklines(slice);
        }

        aktualisiereSeitenAnzeige(aktuelleSeite, anzahlSeiten);
    }

    // ── Seiten-Punkte im Footer ───────────────────────────────────────────
    function aktualisiereSeitenAnzeige(aktiv, gesamt) {
        const el = document.getElementById('seiten-anzeige');
        if (gesamt <= 1) { el.innerHTML = ''; return; }
        el.innerHTML = Array.from({length: gesamt}, (_, i) =>
            `<span style="font-size:1.1rem; color:${i === aktiv ? '#ffd700' : '#374151'};">●</span>`
        ).join('');
    }

    // ── Automatisches Umblättern ──────────────────────────────────────────
    function starteUmblätterTimer() {
        if (seitenTimer) clearInterval(seitenTimer);
        seitenTimer = setInterval(() => {
            if (anzahlSeiten > 1) zeigeSeite(aktuelleSeite + 1);
        }, SEITEN_INTERVALL * 1000);
    }

    // ── Daten vom Server holen ────────────────────────────────────────────
    async function holeNeueDaten() {
        zeigeLadebalken();
        try {
            const res = await fetch('/boerse/anzeige/daten', { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const neueDaten = await res.json();
            animiereAenderungen(daten, neueDaten);
            daten = neueDaten;
            // Seite neu rendern (ohne Seitensprung)
            zeigeSeite(aktuelleSeite, false);
            aktualisiereTickerBand(daten);
            document.getElementById('letzte-aktualisierung').textContent =
                new Date().toLocaleTimeString('de-DE');
        } catch (err) { console.warn('Aktualisierung fehlgeschlagen:', err); }
        versteckeLadebalken();
    }

    // ── Sparkline (SVG) ───────────────────────────────────────────────────
    function sparkline(preise, w = 260, h = 44) {
        if (!preise || preise.length < 2) {
            return `<svg viewBox="0 0 ${w} ${h}" style="width:100%;height:${h}px">
                        <line x1="0" y1="${h/2}" x2="${w}" y2="${h/2}"
                              stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.3"/>
                    </svg>`;
        }
        const pad=6, min=Math.min(...preise), max=Math.max(...preise), span=max-min||1, n=preise.length;
        const pts = preise.map((p,i) => ({
            x: +(pad+(i/(n-1))*(w-2*pad)).toFixed(2),
            y: +(h-pad-((p-min)/span)*(h-2*pad)).toFixed(2)
        }));
        const fp = [`${pts[0].x},${h}`, ...pts.map(p=>`${p.x},${p.y}`), `${pts[n-1].x},${h}`].join(' ');
        const lp = pts.map(p=>`${p.x},${p.y}`).join(' ');
        const last = pts[n-1];
        const gid = 'g'+Math.random().toString(36).slice(2,8);
        return `<svg viewBox="0 0 ${w} ${h}" style="width:100%;height:${h}px" overflow="visible">
            <defs><linearGradient id="${gid}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="currentColor" stop-opacity="0.25"/>
                <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
            </linearGradient></defs>
            <polygon points="${fp}" fill="url(#${gid})"/>
            <polyline points="${lp}" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="${last.x}" cy="${last.y}" r="4" fill="#0d1117" stroke="currentColor" stroke-width="2.5"/>
        </svg>`;
    }

    function zeichneSparklines(liste) {
        liste.forEach(b => {
            const el = document.querySelector(`[data-id="${b.id}"] .sparkline-wrap`);
            if (el) el.innerHTML = sparkline(b.verlauf_preise);
        });
    }

    // ── Kachel als HTML ───────────────────────────────────────────────────
    function renderKachel(b) {
        const rc  = b.richtung==='hoch' ? 'kachel-hoch'   : b.richtung==='runter' ? 'kachel-runter'  : 'kachel-gleich';
        const pc  = b.richtung==='hoch' ? 'preis-hoch'    : b.richtung==='runter' ? 'preis-runter'   : 'preis-gleich';
        const sc  = b.richtung==='hoch' ? '#3fb950'       : b.richtung==='runter' ? '#f85149'        : '#fbbf24';
        const pf  = b.richtung==='hoch' ? '▲'             : b.richtung==='runter' ? '▼'              : '▬';
        const sg  = b.aenderung >= 0 ? '+' : '';
        const pct = b.anteile_gesamt > 0 ? Math.round(b.anteile_verkauft/b.anteile_gesamt*100) : 0;
        const fc  = b.anteile_frei > 0 ? 'color:#fbbf24' : 'color:#374151';

        return `<div class="kachel ${rc}" data-id="${b.id}" style="padding:0.9rem 1.1rem; overflow:hidden; display:flex; flex-direction:column; gap:0.3rem;">
            <div style="color:#8b949e;font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                ${escHtml(b.name)}
            </div>
            <div style="font-size:clamp(1.8rem,3.5vw,3rem);font-weight:900;line-height:1;color:#ffd700;">
                <span class="kurs-wert">${b.kurs}</span>
                <span style="font-size:0.9rem;font-weight:600;color:#4b5563;"> Radi</span>
            </div>
            <div class="${pc}" style="font-size:1.1rem;font-weight:700;">
                ${pf} ${sg}${b.aenderung} Radi
                <span style="font-size:0.85rem;opacity:0.8;">(${sg}${b.aenderung_pct}%)</span>
            </div>
            <div class="sparkline-wrap" style="color:${sc};flex:1;min-height:36px;max-height:56px;">
                ${sparkline(b.verlauf_preise)}
            </div>
            <div style="border-top:1px solid #21262d;margin:0.2rem 0;"></div>
            <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:#6b7280;">
                <span>${b.anteile_verkauft} vergeben</span>
                <span style="color:#8b949e;">von ${b.anteile_gesamt}</span>
                <span style="${fc}">${b.anteile_frei} frei</span>
            </div>
            <div class="bar-bg" style="margin-top:2px;">
                <div class="bar-fill" style="width:${pct}%;background:${sc};"></div>
            </div>
        </div>`;
    }

    // ── Flash bei Kursänderung ────────────────────────────────────────────
    function animiereAenderungen(alt, neu) {
        neu.forEach(b => {
            const a = alt.find(x => x.id === b.id);
            if (!a || a.kurs === b.kurs) return;
            const el = document.querySelector(`[data-id="${b.id}"]`);
            if (!el) return;
            const cls = b.kurs > a.kurs ? 'flash-hoch' : 'flash-runter';
            el.classList.remove('flash-hoch','flash-runter');
            void el.offsetWidth;
            el.classList.add(cls);
            const pw = el.querySelector('.kurs-wert');
            if (pw) { pw.classList.remove('puls'); void pw.offsetWidth; pw.classList.add('puls'); }
            setTimeout(() => el.classList.remove(cls), 1300);
        });
    }

    // ── Ticker-Band aktualisieren ─────────────────────────────────────────
    function aktualisiereTickerBand(betriebe) {
        const eintrag = b => {
            const clr  = b.richtung==='hoch' ? '#3fb950' : b.richtung==='runter' ? '#f85149' : '#ffd700';
            const pf   = b.richtung==='hoch' ? '▲' : b.richtung==='runter' ? '▼' : '▬';
            const sg   = b.aenderung >= 0 ? '+' : '';
            return `<span style="color:${clr};margin:0 1.5rem;">
                        ${escHtml(b.name)} &nbsp;<span style="color:#ffd700;">${b.kurs} Radi</span>
                        &nbsp;${pf} ${sg}${b.aenderung} (${sg}${b.aenderung_pct}%)
                    </span><span style="color:#30363d;margin:0 0.5rem;">│</span>`;
        };
        document.getElementById('ticker-band').innerHTML =
            betriebe.map(eintrag).join('') + betriebe.map(eintrag).join('');
    }

    // ── Ladebalken ────────────────────────────────────────────────────────
    function zeigeLadebalken() {
        const lb = document.getElementById('ladebalken-inner');
        lb.style.transition='none'; lb.style.width='0%';
        setTimeout(()=>{ lb.style.transition='width 1.5s ease'; lb.style.width='85%'; },10);
    }
    function versteckeLadebalken() {
        const lb = document.getElementById('ladebalken-inner');
        lb.style.transition='width 0.3s ease'; lb.style.width='100%';
        setTimeout(()=>{ lb.style.width='0%'; lb.style.transition='none'; },400);
    }

    function escHtml(str) {
        const d = document.createElement('div'); d.textContent = str; return d.innerHTML;
    }

    // ── Start ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        zeigeSeite(0, false);
        starteUmblätterTimer();
    });

    // Bei Größenänderung neu berechnen
    window.addEventListener('resize', () => {
        zeigeSeite(aktuelleSeite, false);
    });
</script>
</body>
</html>

