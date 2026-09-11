@php
    $user = auth()->user();
    $isGS         = $user->isGS();
    $isCatering   = $user->isCatering();
    $isPEP        = $user->isPEP();
    $isNonPEP     = $user->isNonPEP();
    $canRoster    = $user->canHaveRoster(); // User PEP, User non PEP, GS
@endphp

{{-- ── 0. Switcher Portal Layanan GS ── --}}
<a href="{{ route('portal') }}" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-weight:700;margin-bottom:8px">
    <svg width="16" height="16" fill="none" stroke="#166534" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
    Portal Layanan GS
</a>

{{-- ── 1. Navigasi Beranda / Dashboard ── --}}
@if($isGS)
    @php $bActive = request()->routeIs('beranda'); @endphp
    <a href="{{ route('beranda') }}" class="{{ $bActive ? 'active' : '' }}">
        <svg width="16" height="16" fill="{{ $bActive ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Beranda Pribadi
    </a>

    @php $dActive = request()->routeIs('dashboard'); @endphp
    <a href="{{ route('dashboard') }}" class="{{ $dActive ? 'active' : '' }}">
        <svg width="16" height="16" fill="{{ $dActive ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Dashboard GS (Kendali)
    </a>
@else
    @php $active = request()->routeIs('dashboard') || request()->routeIs('beranda'); @endphp
    <a href="{{ route('dashboard') }}" class="{{ $active ? 'active' : '' }}">
        <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        {{ $isCatering ? 'Dashboard Catering' : 'Beranda' }}
    </a>
@endif

{{-- ── 2. Perencanaan Makan Pribadi (User PEP, User non PEP, GS) ── --}}
@if($canRoster)

<div class="sidebar-section">Perencanaan Makan</div>

@php $active = request()->routeIs('roster.*'); @endphp
<a href="{{ route('roster.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Jadwal Roster
</a>

@php $active = request()->routeIs('meal-plan.*'); @endphp
<a href="{{ route('meal-plan.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Rencana Makan
</a>

<div class="sidebar-section">Permohonan Izin</div>

@php $active = request()->routeIs('movement.*'); @endphp
<a href="{{ route('movement.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
    Perpindahan Lokasi
</a>

@php $active = request()->routeIs('outside-meal.*'); @endphp
<a href="{{ route('outside-meal.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
    Outside Meal (Makan Luar)
</a>

<div class="sidebar-section">Layanan & Menu</div>

@php $active = request()->routeIs('menu.*'); @endphp
<a href="{{ route('menu.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v10M8 8v4a2 2 0 002 2m4-6v4a2 2 0 002 2"/></svg>
    Menu Harian
</a>

@php $active = request()->routeIs('suggestion.*'); @endphp
<a href="{{ route('suggestion.create') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
    Saran & Masukan
</a>

@endif

{{-- ── 3. Dapur & Manifest (Catering & GS) ── --}}
@if($isCatering || $isGS)

<div class="sidebar-section">Dapur & Operasional</div>

@php $active = request()->routeIs('manifest.*'); @endphp
<a href="{{ route('manifest.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Manifest Makan
</a>

@if($isCatering)
@php $active = request()->routeIs('menu.*'); @endphp
<a href="{{ route('menu.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
    Kelola Menu Harian
</a>
@endif

@php $active = request()->routeIs('feedback.*'); @endphp
<a href="{{ route('feedback.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    Summary Rating
</a>

@if($isCatering)
@php $active = request()->routeIs('admin.export.*') || request()->routeIs('catering.export.*'); @endphp
<a href="{{ route('admin.export.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Ekspor Data Excel
</a>
@endif

@endif

{{-- ── 4. Administrasi & Pengaturan (GS) ── --}}
@if($isGS)

<div class="sidebar-section">Administrasi Sistem</div>

@php $active = request()->routeIs('admin.users.*'); @endphp
<a href="{{ route('admin.users.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    Kelola Pengguna
</a>

@php $active = request()->routeIs('admin.master.*'); @endphp
<a href="{{ route('admin.master.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    Master Data Sistem
</a>

@php $active = request()->routeIs('admin.mess.*'); @endphp
<a href="{{ route('admin.mess.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    Dashboard Mess
</a>

@php $active = request()->routeIs('admin.export.*'); @endphp
<a href="{{ route('admin.export.index') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Ekspor Data Excel
</a>

@php $active = request()->routeIs('admin.activity'); @endphp
<a href="{{ route('admin.activity') }}" class="{{ $active ? 'active' : '' }}">
    <svg width="16" height="16" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
    Log Aktivitas
</a>

@endif
