<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — Ramba Meal System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts._styles')
</head>
<body style="font-family:'Inter',sans-serif;background:#f4f6f8;color:#1a2332;margin:0">

@php
    $u    = auth()->user();
    $role = $u->role?->slug ?? '';
    $initials = strtoupper(substr($u->name, 0, 2));
    $isBeranda = request()->routeIs('beranda');

    // Build bottom navigation items per role (3 Roles: User, GS, Catering)
    $navItems = [];
    if ($u->isGS()) {
        $navItems = [
            ['route'=>'beranda',            'label'=>'Beranda',    'icon'=>'home'],
            ['route'=>'dashboard',          'label'=>'Kendali',    'icon'=>'chart'],
            ['route'=>'manifest.index',     'label'=>'Manifest',   'icon'=>'doc'],
            ['route'=>'admin.mess.index',   'label'=>'Mess',       'icon'=>'bed'],
            ['route'=>'feedback.index',     'label'=>'Rating',     'icon'=>'star'],
            ['route'=>'admin.users.index',  'label'=>'Pengguna',   'icon'=>'users'],
            ['route'=>'admin.master.index', 'label'=>'Master',     'icon'=>'master'],
        ];
    } elseif ($u->isCatering()) {
        $navItems = [
            ['route'=>'dashboard',       'label'=>'Dashboard',      'icon'=>'home'],
            ['route'=>'manifest.index',  'label'=>'Manifest',       'icon'=>'doc'],
            ['route'=>'feedback.index',  'label'=>'Summary Rating', 'icon'=>'star'],
            ['route'=>'menu.index',      'label'=>'Menu',           'icon'=>'menu'],
            ['route'=>'menu.create',     'label'=>'+ Menu',         'icon'=>'plus'],
        ];
    } else {
        // Default: User
        $navItems = [
            ['route'=>'dashboard',       'label'=>'Beranda',       'icon'=>'home'],
            ['route'=>'roster.index',    'label'=>'Roster',        'icon'=>'calendar'],
            ['route'=>'meal-plan.index', 'label'=>'Rencana Makan', 'icon'=>'plan'],
            ['route'=>'movement.index',  'label'=>'Pindah Lokasi', 'icon'=>'arrow'],
            ['route'=>'menu.index',      'label'=>'Menu',          'icon'=>'menu'],
        ];
    }
@endphp

{{-- ══════════════════════════════════════════════
     DESKTOP SIDEBAR — hidden on mobile
══════════════════════════════════════════════ --}}
<aside id="sidebar" style="position:fixed;left:0;top:0;bottom:0;width:240px;background:#fff;border-right:1px solid #e8edf2;display:flex;flex-direction:column;z-index:50">
    {{-- Logo Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;display:flex;align-items:center;gap:12px">
        <div style="width:38px;height:38px;background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,103,56,.25);font-size:16px;font-weight:900;color:#ffffff;letter-spacing:1px">
            GS
        </div>
        <div>
            <p style="font-size:14.5px;font-weight:800;color:#1a2332;margin:0;line-height:1.2">Ramba Meal</p>
            <p style="font-size:10px;color:#006738;font-weight:700;margin:2px 0 0;text-transform:uppercase;letter-spacing:0.5px">Planning System</p>
        </div>
    </div>

    {{-- Nav Items --}}
    <nav style="flex:1;overflow-y:auto;padding:10px 0">
        @include('layouts._sidebar_nav')
    </nav>

    {{-- User Footer Profile --}}
    <div style="padding:14px 18px;border-top:1px solid #e8edf2;background:#fafafa">
        <div style="display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e8edf2;border-radius:10px;padding:10px 12px">
            <div style="width:34px;height:34px;border-radius:50%;background:#006738;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1;min-width:0">
                <p style="font-size:12.5px;font-weight:700;color:#1a2332;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $u->name }}</p>
                <span class="badge badge-green" style="font-size:9.5px;padding:1px 6px;margin-top:2px">{{ $u->role?->name }}</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px">
            <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;justify-content:center;gap:5px;padding:7px;background:#fff;border:1px solid #e8edf2;border-radius:8px;font-size:11.5px;font-weight:600;color:#374151;text-decoration:none">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" style="width:100%;display:flex;align-items:center;justify-content:center;gap:5px;padding:7px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:11.5px;font-weight:700;color:#E32529;cursor:pointer">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg> Keluar
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ══════════════════════════════════════════════
     MOBILE TOP BAR — hidden on desktop
══════════════════════════════════════════════ --}}
<header id="topbar" style="position:fixed;top:0;left:0;right:0;height:54px;background:#fff;border-bottom:1px solid #e8edf2;display:flex;align-items:center;justify-content:space-between;padding:0 12px;z-index:45">
    <div style="display:flex;align-items:center;gap:8px">
        <a href="{{ route('portal') }}" title="Kembali ke Portal GS" style="width:32px;height:32px;background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,103,56,.25);text-decoration:none;font-size:13.5px;font-weight:900;color:#ffffff;letter-spacing:0.5px">
            GS
        </a>
        <div>
            <span style="font-size:13.5px;font-weight:800;color:#1a2332">@yield('page-title','Ramba Meal')</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
        <a href="{{ route('portal') }}" style="display:flex;align-items:center;gap:4px;text-decoration:none;padding:4px 8px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:11px;font-weight:700;color:#166534">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Portal
        </a>
        <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;padding:3px 8px 3px 4px;background:#f4f6f8;border:1px solid #e8edf2;border-radius:99px">
            <div style="width:24px;height:24px;border-radius:50%;background:#006738;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff">{{ $initials }}</div>
            <span style="font-size:11px;font-weight:700;color:#374151;max-width:70px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ explode(' ', $u->name)[0] }}</span>
        </a>
    </div>
</header>

{{-- ══════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════ --}}
<div id="main-wrap">
    {{-- Flash Messages --}}
    <div id="flash-area" style="padding:0">
        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px">
            <svg width="18" height="18" fill="#16a34a" viewBox="0 0 20 20" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <p style="font-size:13px;color:#15803d;margin:0;line-height:1.4">{!! session('success') !!}</p>
        </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px">
            @if(session('error'))<p style="font-size:13px;color:#dc2626;margin:0;font-weight:600">{{ session('error') }}</p>@endif
            @if(isset($errors))
                @foreach($errors->all() as $e)<p style="font-size:12.5px;color:#dc2626;margin:3px 0 0">• {{ $e }}</p>@endforeach
            @endif
        </div>
        @endif

        @if(session('warning'))
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:16px">
            <p style="font-size:13px;color:#92400e;margin:0;font-weight:600">{{ session('warning') }}</p>
        </div>
        @endif
    </div>

    @yield('content')
</div>

{{-- ══════════════════════════════════════════════
     MOBILE BOTTOM NAV — hidden on desktop
══════════════════════════════════════════════ --}}
@php $navCount = count($navItems); $isCompact = $navCount >= 6; @endphp
<nav id="bottom-nav" style="position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #e8edf2;height:{{ $isCompact ? '54px' : '60px' }};display:grid;grid-template-columns:repeat({{ $navCount }}, 1fr);z-index:45;box-shadow:0 -2px 10px rgba(0,0,0,.03)">
    @foreach($navItems as $item)
    @php $active = request()->routeIs($item['route'].'*') || request()->routeIs($item['route']); @endphp
    <a href="{{ route($item['route']) }}" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.5px;text-decoration:none;color:{{ $active ? '#006738' : '#9ca3af' }};position:relative;padding:0 1px">
        @if($active)<div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:{{ $isCompact ? '22px' : '28px' }};height:2.5px;background:#006738;border-radius:0 0 4px 4px"></div>@endif
        @include('layouts._icon', ['name'=>$item['icon'], 'active'=>$active, 'size'=>$isCompact ? 18 : 20])
        <span style="font-size:{{ $isCompact ? '9px' : '10px' }};font-weight:{{ $active ? '800':'500' }};line-height:1;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%">{{ $item['label'] }}</span>
    </a>
    @endforeach
</nav>

{{-- Responsive CSS --}}
<style>
@media(min-width:1024px){
    #sidebar{display:flex!important}
    #topbar{display:none!important}
    #bottom-nav{display:none!important}
    #main-wrap{margin-left:240px;padding:28px 36px;min-height:100vh}
    #flash-area{margin-bottom:16px}
}
@media(max-width:1023px){
    #sidebar{display:none!important}
    #topbar{display:flex!important}
    #bottom-nav{display:grid!important}
    #main-wrap{padding:68px 14px 70px;min-height:100vh}
}
</style>

{{-- ══════════════════════════════════════════════
     POPUP MOTIVASI & HIMBAUAN AWAL LOGIN GS RAMBA
══════════════════════════════════════════════ --}}
<div id="gsWelcomeModal"
     style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.72);backdrop-filter:blur(6px);z-index:99999;align-items:center;justify-content:center;padding:16px;cursor:pointer;opacity:0;transition:opacity .25s ease-out"
     onclick="closeGsWelcomeModal()">

    <div style="background:#ffffff;border-radius:20px;max-width:500px;width:100%;box-shadow:0 25px 60px -15px rgba(0,0,0,0.3);overflow:hidden;border:1px solid rgba(0,103,56,0.15);cursor:pointer;position:relative"
         onclick="closeGsWelcomeModal()">

        {{-- Top Brand Banner --}}
        <div style="background:linear-gradient(135deg, #006738 0%, #004d28 100%);padding:22px 24px 18px;color:#ffffff;position:relative">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px">
                <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.3);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase">
                    <span>🌱</span> GS Field Ramba
                </div>
                <button type="button" onclick="closeGsWelcomeModal()"
                        style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.2);border:none;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;line-height:1">
                    ✕
                </button>
            </div>
            <h2 style="font-size:22px;font-weight:900;color:#ffffff;margin:0;line-height:1.2;letter-spacing:-0.3px">
                Bantu Kito, Yuk!
            </h2>
            <p style="font-size:13.5px;color:rgba(255,255,255,0.9);margin:4px 0 0;font-weight:600">
                Apo yang kito rencanoke, itulah yang kito siapkan.
            </p>
        </div>

        {{-- Body Content --}}
        <div style="padding:22px 24px">
            <p style="font-size:13.5px;line-height:1.65;color:#334155;margin:0 0 14px">
                Yuk dulur-dulur kito sempatkan isi <strong>roster</strong> dan kabarke kalau <strong>dak makan</strong>, ado <strong>tugas/kunjungan ke struktur lain</strong>, atau ado <strong>outside meal</strong>.
            </p>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #006738;border-radius:12px;padding:12px 16px;margin-bottom:16px">
                <p style="font-size:13px;color:#166534;margin:0;line-height:1.6;font-weight:600">
                    ✨ Biar porsi makanan yang disiapke pas, <strong>dak mubazir</strong>, dan pengelolaan cost Field Ramba pacak <strong>Cost Excellence</strong>.
                </p>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding-top:10px;border-top:1px dashed #e2e8f0">
                <div>
                    <p style="font-size:12px;color:#64748b;margin:0">Salam hangat,</p>
                    <p style="font-size:13px;font-weight:800;color:#006738;margin:2px 0 0">GS Ramba</p>
                </div>
                <div style="background:#fefce8;border:1px solid #fef08a;border-radius:8px;padding:6px 12px">
                    <span style="font-size:12px;font-weight:800;color:#854d0e;font-style:italic">
                        "Kito saling bantu, kito saling jago." 🤝
                    </span>
                </div>
            </div>

            {{-- Action Button --}}
            <div style="margin-top:20px;text-align:center">
                <button type="button" onclick="closeGsWelcomeModal()"
                        class="btn-primary"
                        style="width:100%;padding:12px 20px;font-size:14px;font-weight:800;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(0,103,56,0.3);background:#006738;color:#fff;border:none;cursor:pointer">
                    Siap, Lanjut ke Aplikasi →
                </button>
                <p style="font-size:11px;color:#94a3b8;margin:8px 0 0">
                    (Klik di mana saja pada layar untuk menutup)
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function showGsWelcomeModal() {
    var modal = document.getElementById('gsWelcomeModal');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(function() {
        modal.style.opacity = '1';
    }, 20);
}

function closeGsWelcomeModal() {
    var modal = document.getElementById('gsWelcomeModal');
    if (!modal) return;
    modal.style.opacity = '0';
    setTimeout(function() {
        modal.style.display = 'none';
    }, 250);
    sessionStorage.setItem('gs_welcome_popup_shown', 'true');
}

document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('gs_welcome_popup_shown')) {
        setTimeout(function() {
            showGsWelcomeModal();
        }, 350);
    }
});
</script>

@stack('scripts')
</body>
</html>
