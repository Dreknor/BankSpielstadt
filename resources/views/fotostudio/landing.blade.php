<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fotostudio – Bilder anschauen</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        h1 {
            color: #fff;
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .untertitel {
            color: rgba(255,255,255,0.8);
            font-size: clamp(1rem, 2.5vw, 1.4rem);
            margin-bottom: 3rem;
            text-align: center;
        }
        .studios {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: center;
            max-width: 900px;
        }
        .studio-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: #fff;
            border: none;
            border-radius: 2rem;
            padding: 2.5rem 3rem;
            min-width: 220px;
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 900;
            color: #4c1d95;
            text-decoration: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .studio-btn:hover, .studio-btn:focus {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 16px 40px rgba(0,0,0,0.3);
            outline: none;
        }
        .studio-btn:active { transform: scale(0.97); }
        .studio-icon { font-size: 3.5rem; line-height: 1; }
        .leer {
            background: rgba(255,255,255,0.15);
            border-radius: 2rem;
            padding: 3rem 4rem;
            color: #fff;
            font-size: 1.3rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>📷 Fotostudio</h1>
    <p class="untertitel">Welches Fotostudio möchtest du sehen?</p>

    @if($studios->isEmpty())
        <div class="leer">
            Noch kein Fotostudio verfügbar.<br>Bitte frag einen Erwachsenen.
        </div>
    @else
        <div class="studios">
            @foreach($studios as $studio)
            <a href="{{ route('fotostudio.slideshow', $studio->fotostudio_token) }}"
               class="studio-btn">
                <span class="studio-icon">🖼️</span>
                {{ $studio->name }}
            </a>
            @endforeach
        </div>
    @endif
</body>
</html>

