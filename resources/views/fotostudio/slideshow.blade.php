<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $betrieb->name }} – Fotostudio Slideshow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
        }

        #slideshow {
            position: relative;
            width: 100vw;
            height: 100vh;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .slide.aktiv { opacity: 1; z-index: 2; }
        .slide.vorherige { opacity: 0; z-index: 1; }

        .slide img {
            width: 100vw;
            height: 100vh;
            object-fit: contain;
            object-position: center;
        }

        /* Titel-Overlay */
        .slide-titel {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 24px 40px;
            background: linear-gradient(transparent, rgba(0,0,0,0.75));
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0,0,0,0.8);
            z-index: 5;
        }

        /* Info-Leiste oben */
        #info-leiste {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(rgba(0,0,0,0.6), transparent);
            opacity: 0;
            transition: opacity 0.5s;
        }
        #info-leiste.sichtbar { opacity: 1; }

        .betrieb-name {
            font-size: clamp(1.2rem, 2.5vw, 2rem);
            font-weight: 900;
            letter-spacing: 0.05em;
        }

        .uhr {
            font-size: clamp(1.2rem, 2.5vw, 2rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        /* Leer-Zustand */
        #leer {
            display: none;
            position: fixed;
            inset: 0;
            background: #111;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1rem;
            z-index: 20;
        }
        #leer.aktiv { display: flex; }
        #leer svg { width: 80px; height: 80px; opacity: 0.3; }
        #leer p { font-size: 1.5rem; opacity: 0.4; }

        /* Fortschrittsbalken */
        #fortschritt {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 4px;
            background: rgba(255,255,255,0.6);
            z-index: 10;
            transition: width linear;
        }
    </style>
</head>
<body>

<div id="info-leiste" class="sichtbar">
    <span class="betrieb-name">📷 {{ $betrieb->name }}</span>
    <span class="uhr" id="uhr">--:--</span>
</div>

<div id="slideshow"></div>
<div id="fortschritt" style="width:0"></div>

<div id="leer">
    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 3.75h18A2.25 2.25 0 0123.25 6v12A2.25 2.25 0 0121 20.25H3A2.25 2.25 0 01.75 18V6A2.25 2.25 0 013 3.75z"/>
    </svg>
    <p>Noch keine Bilder vorhanden</p>
</div>

<script>
const DATEN_URL     = '{{ url("/fotostudio/{$token}/daten") }}';
const DAUER_DEFAULT = 5000;   // Fallback-Anzeigedauer in Millisekunden
const REFRESH_MS    = 60000;  // Bildliste alle 60 Sek. neu laden

let bilder      = [];
let aktuellerIdx = 0;
let slides      = [];
let timer       = null;
let fortTimer   = null;

// ── Uhr ──────────────────────────────────────────────────────────────────────
function aktualisierUhr() {
    const jetzt = new Date();
    document.getElementById('uhr').textContent =
        jetzt.getHours().toString().padStart(2,'0') + ':' +
        jetzt.getMinutes().toString().padStart(2,'0');
}
setInterval(aktualisierUhr, 1000);
aktualisierUhr();

// ── Bildliste laden ───────────────────────────────────────────────────────────
async function ladeBilder() {
    try {
        const res  = await fetch(DATEN_URL);
        const data = await res.json();
        const neueBilder = data.bilder ?? [];

        // Slides neu aufbauen, wenn sich Anzahl / URLs / Dauern geändert haben
        const alteUrls = bilder.map(b => b.url + ':' + b.dauer).join('|');
        const neueUrls = neueBilder.map(b => b.url + ':' + b.dauer).join('|');
        if (alteUrls !== neueUrls) {
            bilder = neueBilder;
            bautSlides();
        }
    } catch (e) {
        console.error('Fehler beim Laden der Bildliste', e);
    }
}

function bautSlides() {
    const container = document.getElementById('slideshow');
    container.innerHTML = '';
    slides = [];

    if (bilder.length === 0) {
        document.getElementById('leer').classList.add('aktiv');
        clearTimeout(timer);
        document.getElementById('fortschritt').style.width = '0';
        return;
    }
    document.getElementById('leer').classList.remove('aktiv');

    bilder.forEach((bild, i) => {
        const div = document.createElement('div');
        div.className = 'slide' + (i === 0 ? ' aktiv' : '');
        const img = document.createElement('img');
        img.src = bild.url;
        img.alt = bild.titel ?? '';
        div.appendChild(img);
        if (bild.titel) {
            const tDiv = document.createElement('div');
            tDiv.className = 'slide-titel';
            tDiv.textContent = bild.titel;
            div.appendChild(tDiv);
        }
        container.appendChild(div);
        slides.push(div);
    });

    aktuellerIdx = 0;
    starteTimer();
}

function naechstesFolie() {
    if (slides.length === 0) return;

    const alt = slides[aktuellerIdx];
    alt.classList.remove('aktiv');
    alt.classList.add('vorherige');
    setTimeout(() => alt.classList.remove('vorherige'), 1400);

    aktuellerIdx = (aktuellerIdx + 1) % slides.length;
    slides[aktuellerIdx].classList.add('aktiv');

    starteTimer();
}

function dauerFuerAktuellesFolie() {
    if (bilder.length === 0) return DAUER_DEFAULT;
    const sek = bilder[aktuellerIdx]?.dauer;
    return (sek && sek > 0) ? sek * 1000 : DAUER_DEFAULT;
}

function starteTimer() {
    clearTimeout(timer);
    clearInterval(fortTimer);

    const dauerMs = dauerFuerAktuellesFolie();
    const balken  = document.getElementById('fortschritt');
    balken.style.transition = 'none';
    balken.style.width = '0';
    // Trigger reflow
    balken.offsetWidth;
    balken.style.transition = `width ${dauerMs}ms linear`;
    balken.style.width = '100%';

    timer = setTimeout(naechstesFolie, dauerMs);
}

// ── Tipp für Infoleiste ausblenden (nach 3 Sek.) ─────────────────────────────
setTimeout(() => {
    document.getElementById('info-leiste').classList.remove('sichtbar');
}, 3000);
document.addEventListener('mousemove', () => {
    const leiste = document.getElementById('info-leiste');
    leiste.classList.add('sichtbar');
    clearTimeout(leiste._hideTimer);
    leiste._hideTimer = setTimeout(() => leiste.classList.remove('sichtbar'), 3000);
});

// ── Init ─────────────────────────────────────────────────────────────────────
ladeBilder();
setInterval(ladeBilder, REFRESH_MS);
</script>
</body>
</html>

