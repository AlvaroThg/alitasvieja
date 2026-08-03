@php
    $r = config('restaurante');
    $logo = public_path($r['logo'] ?? '') && file_exists(public_path($r['logo'])) ? asset($r['logo']) : null;
    $wa = $r['whatsapp'] ? 'https://wa.me/' . preg_replace('/\D/', '', $r['whatsapp']) : null;

    // Un enlace sin esquema (ej. "www.linkedin.com/in/...") el navegador lo trata
    // como ruta interna: se le antepone https:// para que salga del sitio.
    $link = fn (?string $u) => $u
        ? (preg_match('#^https?://#i', $u) ? $u : 'https://' . ltrim($u, '/'))
        : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $r['nombre'] }} — Alitas en Cochabamba y Tarija</title>
    <meta name="description" content="{{ $r['descripcion'] }}">
    <meta property="og:title" content="{{ $r['nombre'] }}">
    <meta property="og:description" content="{{ $r['descripcion'] }}">
    <meta property="og:type" content="restaurant">
    @if($logo)<meta property="og:image" content="{{ $logo }}">@endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-base); color: var(--text-strong); }
        a { color: inherit; }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 0 1.5rem; }

        /* ── Barra superior ── */
        .nav { position: sticky; top: 0; z-index: 40; background: color-mix(in srgb, var(--bg-surface) 88%, transparent); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); }
        .nav-in { display: flex; align-items: center; gap: 1rem; height: 68px; }
        .brand { display: flex; align-items: center; gap: 0.7rem; font-weight: 900; letter-spacing: -0.02em; }
        .brand-logo { height: 44px; width: auto; background: #fff; border-radius: 10px; padding: 3px; }
        .brand-fallback { width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg, #dc2626, #b91c1c); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
        .nav-links { margin-left: auto; display: flex; align-items: center; gap: 0.35rem; }
        .nav-link { padding: 0.45rem 0.85rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); text-decoration: none; }
        .nav-link:hover { background: var(--bg-elevated); color: var(--text-strong); }
        .btn-wa { display: inline-flex; align-items: center; gap: 0.45rem; background: #16a34a; color: #fff; padding: 0.5rem 1rem; border-radius: 11px; font-size: 0.85rem; font-weight: 800; text-decoration: none; }
        .btn-wa:hover { background: #15803d; }
        @media (max-width: 820px) { .nav-hide { display: none; } }

        /* ── Hero ── */
        .hero { position: relative; overflow: hidden; padding: 4rem 0 3.5rem; }
        .hero::before { content: ''; position: absolute; inset: 0; background:
            radial-gradient(60% 55% at 82% 12%, rgba(220,38,38,0.10), transparent 70%),
            radial-gradient(45% 45% at 8% 88%, rgba(249,115,22,0.10), transparent 70%); pointer-events: none; }
        .hero-in { position: relative; display: grid; gap: 2.5rem; align-items: center; }
        @media (min-width: 900px) { .hero-in { grid-template-columns: 1.05fr 0.95fr; } }
        .eyebrow { display: inline-block; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #f97316; background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.25); padding: 0.3rem 0.8rem; border-radius: 50px; }
        h1 { font-size: clamp(2.1rem, 6vw, 3.4rem); font-weight: 900; line-height: 1.05; letter-spacing: -0.03em; margin: 1rem 0 0.75rem; }
        .lead { font-size: 1.05rem; line-height: 1.65; color: var(--text-secondary); max-width: 34rem; }
        .cta-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.75rem; }
        .btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #f97316, #dc2626); color: #fff; padding: 0.85rem 1.75rem; border-radius: 13px; font-weight: 800; text-decoration: none; box-shadow: 0 8px 20px rgba(220,38,38,0.22); }
        .btn-primary:hover { transform: translateY(-1px); }
        .btn-ghost { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--bg-surface); border: 1px solid var(--border-strong); color: var(--text); padding: 0.85rem 1.5rem; border-radius: 13px; font-weight: 700; text-decoration: none; }
        .facts { display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: 2rem; font-size: 0.85rem; color: var(--text-muted); }
        .fact { display: inline-flex; align-items: center; gap: 0.45rem; }
        .hero-art { display: flex; align-items: center; justify-content: center; }
        .hero-art img { width: min(100%, 380px); background: #fff; border-radius: 28px; padding: 1.5rem; box-shadow: 0 24px 60px rgba(0,0,0,0.16); }

        /* ── Secciones ── */
        section { padding: 3.5rem 0; }
        .sec-head { margin-bottom: 2rem; }
        .sec-head h2 { font-size: clamp(1.5rem, 3.5vw, 2.1rem); font-weight: 900; letter-spacing: -0.02em; }
        .sec-head p { color: var(--text-muted); margin-top: 0.4rem; }

        /* ── Menú ── */
        .branch-tabs { display: inline-flex; gap: 0.25rem; background: var(--bg-elevated); border: 1px solid var(--border); padding: 0.25rem; border-radius: 12px; margin-bottom: 1.75rem; }
        .branch-tab { padding: 0.5rem 1.1rem; border-radius: 9px; border: none; background: transparent; color: var(--text-muted); font-weight: 700; font-size: 0.85rem; cursor: pointer; font-family: inherit; }
        .branch-tab.on { background: var(--bg-surface); color: var(--text-strong); box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
        .cat { margin-bottom: 2.5rem; }
        .cat h3 { font-size: 1.05rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.06em; color: #f97316; margin-bottom: 0.9rem; }
        .items { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 18px; overflow: hidden; }
        .item { display: flex; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
        .item:last-child { border-bottom: none; }
        .item-main { flex: 1; min-width: 0; }
        .item-name { font-weight: 800; }
        .item-desc { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.15rem; line-height: 1.45; }
        .variants { display: flex; flex-direction: column; gap: 0.3rem; text-align: right; font-size: 0.88rem; }
        .variant { display: flex; gap: 0.9rem; justify-content: flex-end; white-space: nowrap; }
        .variant-name { color: var(--text-muted); }
        .variant-price { font-weight: 800; color: var(--text-strong); font-variant-numeric: tabular-nums; }

        /* ── Salsas ── */
        .sauces { display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); }
        .sauce { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 14px; padding: 0.9rem 1.1rem; }
        .sauce-name { font-weight: 800; font-size: 0.92rem; }
        .heat { display: flex; gap: 2px; margin-top: 0.3rem; }

        /* ── Sucursales ── */
        .branches { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .branch { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 18px; padding: 1.4rem; }
        .branch h3 { font-size: 1.1rem; font-weight: 900; margin-bottom: 0.5rem; }
        .branch p { color: var(--text-secondary); font-size: 0.9rem; line-height: 1.55; }
        .branch a { color: #f97316; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-top: 0.6rem; }

        /* ── Equipo / créditos ── */
        .team { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 22px; padding: 2.25rem; }
        .team-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-top: 1.5rem; }
        .dev { background: var(--bg-base); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; }
        .dev-name { font-weight: 900; }
        .dev-role { font-size: 0.83rem; color: var(--text-muted); margin-top: 0.2rem; line-height: 1.45; }
        .dev-links { display: flex; gap: 0.6rem; margin-top: 0.7rem; }
        .dev-links a { font-size: 0.78rem; font-weight: 700; color: #f97316; text-decoration: none; }
        .stack { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 1.5rem; }
        .chip { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); background: var(--bg-elevated); border: 1px solid var(--border); padding: 0.25rem 0.7rem; border-radius: 50px; }

        /* ── Pie ── */
        footer { border-top: 1px solid var(--border); padding: 1.75rem 0; margin-top: 1rem; }
        .foot { display: flex; flex-wrap: wrap; gap: 0.75rem 1.5rem; justify-content: space-between; align-items: center; font-size: 0.82rem; color: var(--text-muted); }
        .foot a { text-decoration: none; }
        .foot a:hover { color: #f97316; }
    </style>
</head>
<body x-data="{ sucursal: {{ $sucursales->first()->id ?? 'null' }} }">

    <nav class="nav">
        <div class="wrap nav-in">
            <a href="#" class="brand">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $r['nombre'] }}" class="brand-logo">
                @else
                    <span class="brand-fallback">🍗</span>
                @endif
                <span class="nav-hide">{{ $r['nombre'] }}</span>
            </a>
            <div class="nav-links">
                <a href="#menu" class="nav-link nav-hide">Menú</a>
                <a href="#salsas" class="nav-link nav-hide">Salsas</a>
                <a href="#sucursales" class="nav-link nav-hide">Sucursales</a>
                @if($wa)
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-wa">
                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18a8 8 0 01-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1112 20z"/></svg>
                        Pedir
                    </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- ═══ HERO ═══ --}}
    <header class="hero">
        <div class="wrap hero-in">
            <div>
                <span class="eyebrow">{{ $r['lema'] }}</span>
                <h1>Las alitas que se comen<br>con las manos</h1>
                <p class="lead">{{ $r['descripcion'] }}</p>

                <div class="cta-row">
                    <a href="#menu" class="btn-primary">
                        Ver el menú
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </a>
                    @if($wa)
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-ghost">Pedir por WhatsApp</a>
                    @endif
                </div>

                <div class="facts">
                    <span class="fact">
                        <svg width="15" height="15" fill="none" stroke="#f97316" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ $sucursales->count() }} {{ $sucursales->count() === 1 ? 'sucursal' : 'sucursales' }}
                    </span>
                    <span class="fact">
                        <svg width="15" height="15" fill="none" stroke="#f97316" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $r['horarios'] }}
                    </span>
                    <span class="fact">
                        <svg width="15" height="15" fill="none" stroke="#f97316" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"></path></svg>
                        Para llevar y delivery
                    </span>
                </div>
            </div>

            @if($logo)
                <div class="hero-art"><img src="{{ $logo }}" alt="{{ $r['nombre'] }}"></div>
            @endif
        </div>
    </header>

    {{-- ═══ MENÚ (desde la base de datos) ═══ --}}
    <section id="menu">
        <div class="wrap">
            <div class="sec-head">
                <h2>Nuestro menú</h2>
                <p>Precios en bolivianos. Elige tu sucursal: algunos productos y precios cambian según la ciudad.</p>
            </div>

            @if($sucursales->count() > 1)
                <div class="branch-tabs">
                    @foreach($sucursales as $s)
                        <button class="branch-tab" :class="{ 'on': sucursal === {{ $s->id }} }" @click="sucursal = {{ $s->id }}">
                            {{ $s->city ?: $s->name }}
                        </button>
                    @endforeach
                </div>
            @endif

            @forelse($categorias as $cat)
                <div class="cat">
                    <h3>{{ $cat['nombre'] }}</h3>
                    <div class="items">
                        @foreach($cat['productos'] as $p)
                            @php
                                // El producto se oculta en la sucursal donde ninguna variante se vende.
                                $disponibleEn = [];
                                foreach ($sucursales as $s) {
                                    $disponibleEn[$s->id] = collect($p['variantes'])
                                        ->contains(fn ($v) => ($v['precios'][$s->id] ?? 0) > 0);
                                }
                                $cond = collect($disponibleEn)->filter()->keys()
                                    ->map(fn ($id) => "sucursal === $id")->implode(' || ');
                            @endphp
                            <div class="item" @if($cond) x-show="{{ $cond }}" x-cloak @endif>
                                <div class="item-main">
                                    <div class="item-name">{{ $p['nombre'] }}</div>
                                    @if($p['descripcion'])
                                        <div class="item-desc">{{ $p['descripcion'] }}</div>
                                    @endif
                                </div>
                                <div class="variants">
                                    @foreach($p['variantes'] as $v)
                                        @php
                                            $condV = collect($v['precios'])->filter(fn ($pr) => $pr > 0)->keys()
                                                ->map(fn ($id) => "sucursal === $id")->implode(' || ');
                                        @endphp
                                        <div class="variant" @if($condV) x-show="{{ $condV }}" x-cloak @endif>
                                            @if(count($p['variantes']) > 1 || $v['nombre'] !== 'Único')
                                                <span class="variant-name">{{ $v['nombre'] }}</span>
                                            @endif
                                            <span class="variant-price">
                                                @foreach($v['precios'] as $bid => $precio)
                                                    <span x-show="sucursal === {{ $bid }}" x-cloak>Bs {{ number_format($precio, 0) }}</span>
                                                @endforeach
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p style="color: var(--text-muted);">El menú se está actualizando.</p>
            @endforelse
        </div>
    </section>

    {{-- ═══ SALSAS ═══ --}}
    @if($salsas->isNotEmpty())
    <section id="salsas" style="background: var(--bg-elevated);">
        <div class="wrap">
            <div class="sec-head">
                <h2>Nuestras salsas</h2>
                <p>Preparadas en casa. Eliges cuántas alitas van con cada una — o te las llevamos aparte.</p>
            </div>
            <div class="sauces">
                @foreach($salsas as $salsa)
                    <div class="sauce">
                        <div class="sauce-name">{{ $salsa->name }}</div>
                        @if($salsa->spice_level > 0)
                            <div class="heat">
                                @for($i = 0; $i < min($salsa->spice_level, 5); $i++)
                                    <svg width="13" height="13" fill="#dc2626" viewBox="0 0 24 24"><path d="M12 2C9 6 7 9 7 13a5 5 0 0010 0c0-1.5-.5-3-1.5-4.5C15 11 13.5 12 12 12c1-2 1-5 0-10z"></path></svg>
                                @endfor
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══ SUCURSALES ═══ --}}
    <section id="sucursales">
        <div class="wrap">
            <div class="sec-head">
                <h2>Dónde encontrarnos</h2>
                <p>{{ $r['horarios'] }}</p>
            </div>
            <div class="branches">
                @foreach($r['sucursales'] as $suc)
                    @if($suc['ciudad'])
                        <div class="branch">
                            <h3>{{ $suc['ciudad'] }}</h3>
                            <p>{{ $suc['direccion'] ?: 'Dirección próximamente' }}</p>
                            @if($suc['telefono'])<p>{{ $suc['telefono'] }}</p>@endif
                            @if($suc['maps'])
                                <a href="{{ $link($suc['maps']) }}" target="_blank" rel="noopener">Cómo llegar →</a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ EQUIPO / SISTEMA ═══ --}}
    <section id="sistema">
        <div class="wrap">
            <div class="team">
                <span class="eyebrow">{{ $r['equipo']['titulo'] }}</span>
                <h2 style="font-size: clamp(1.4rem, 3vw, 1.9rem); font-weight: 900; margin: 0.9rem 0 0.6rem; letter-spacing: -0.02em;">
                    Un punto de venta hecho a medida
                </h2>
                <p style="color: var(--text-secondary); max-width: 46rem; line-height: 1.65;">
                    {{ $r['equipo']['resumen'] }}
                </p>

                <div class="team-grid">
                    @foreach($r['equipo']['personas'] as $dev)
                        <div class="dev">
                            <div class="dev-name">{{ $dev['nombre'] }}</div>
                            <div class="dev-role">{{ $dev['rol'] }}</div>
                            @if($dev['github'] || $dev['linkedin'])
                                <div class="dev-links">
                                    @if($dev['github'])<a href="{{ $link($dev['github']) }}" target="_blank" rel="noopener">GitHub</a>@endif
                                    @if($dev['linkedin'])<a href="{{ $link($dev['linkedin']) }}" target="_blank" rel="noopener">LinkedIn</a>@endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="stack">
                    @foreach($r['equipo']['stack'] as $tec)
                        <span class="chip">{{ $tec }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap foot">
            <span>© {{ date('Y') }} {{ $r['nombre'] }} · Cochabamba y Tarija</span>
            <span style="display: flex; gap: 1.25rem;">
                @if($r['instagram'])<a href="{{ $link($r['instagram']) }}" target="_blank" rel="noopener">Instagram</a>@endif
                @if($r['facebook'])<a href="{{ $link($r['facebook']) }}" target="_blank" rel="noopener">Facebook</a>@endif
                <a href="{{ url('/acceso') }}">Acceso al sistema</a>
            </span>
        </div>
    </footer>

</body>
</html>
