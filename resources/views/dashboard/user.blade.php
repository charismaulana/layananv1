@extends('layouts.app')
@section('title', 'Beranda')
@section('page-title', 'Beranda')

@section('content')
@php
    $today    = \Carbon\Carbon::today();
    $tomorrow = \Carbon\Carbon::tomorrow();
    $u        = $user;

    $mealColors = [
        'breakfast' => ['bg'=>'#fff7ed', 'dot'=>'#c2410c', 'border'=>'#fed7aa'],
        'lunch'     => ['bg'=>'#eff6ff', 'dot'=>'#1d4ed8', 'border'=>'#bfdbfe'],
        'dinner'    => ['bg'=>'#f5f3ff', 'dot'=>'#6d28d9', 'border'=>'#ddd6fe'],
        'supper'    => ['bg'=>'#fdf2f8', 'dot'=>'#9d174d', 'border'=>'#fbcfe8'],
    ];

    $statusMap = [
        'active'       => ['label'=>'Aktif (Makan)',  'class'=>'badge-green'],
        'cancelled'    => ['label'=>'Tidak Makan',    'class'=>'badge-red'],
        'outside_meal' => ['label'=>'Outside Box',    'class'=>'badge-gold'],
        'moved'        => ['label'=>'Dipindah',       'class'=>'badge-blue'],
    ];
@endphp

{{-- ── 1. Greeting Banner ── --}}
<div style="background:#006738;border-radius:14px;padding:18px 20px;margin-bottom:20px;position:relative;overflow:hidden;box-shadow:0 4px 12px rgba(0,103,56,.12)">
    <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
    <div style="position:relative">
        <p style="font-size:11px;color:rgba(255,255,255,.7);margin:0 0 2px">{{ $today->translatedFormat('l, d F Y') }}</p>
        <h2 style="font-size:18px;font-weight:800;color:white;margin:0 0 4px">Halo, {{ $u->name }} 👋</h2>
        <p style="font-size:11.5px;color:rgba(255,255,255,.75);margin:0">
            {{ $u->role?->name }} @if($u->homebaseRegion) · Homebase: <strong>{{ $u->homebaseRegion->name }}</strong> @endif
            @if($u->department) · {{ $u->department->name }} @endif
        </p>
    </div>
</div>

{{-- ── 2. Action Center / Planning Focus Grid ── --}}
<div class="dash-grid">

{{-- ── LEFT / PRIMARY PLANNING COLUMN ── --}}
<div>

    {{-- ══ FOCUS 1: STATUS & JADWAL ROSTER MINGGU INI ══ --}}
    <div class="card" style="margin-bottom:18px;border-left:4px solid #006738">
        <div class="card-header" style="background:#fafafa">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">📅</span>
                <h3 style="font-size:14px;font-weight:700;color:#1a2332;margin:0">Jadwal Roster & Kehadiran Kerja</h3>
            </div>
        </div>
        <div class="card-body" style="padding:16px">
            {{-- Jadwal 7 Hari ke Depan (Minggu Ini) --}}
            <div style="background:#fcfdfd;border:1px solid #e8edf2;border-radius:10px;padding:8px 10px;margin-bottom:14px">
                <p style="font-size:11px;font-weight:700;color:#475569;margin:2px 4px 6px">Jadwal 7 Hari ke Depan:</p>
                <div style="display:flex;flex-direction:column;gap:4px">
                    @foreach($upcomingWeek as $day)
                    @php
                        $isTdy  = $day['date']->isToday();
                        $isTmrw = $day['date']->isTomorrow();
                        $r      = $day['roster'];
                        $isKrj  = $r && $r->status === 'Kerja';
                        $dotClr = $isKrj ? '#006738' : ($r ? '#9ca3af' : '#fbbf24');
                        $txt    = $isKrj ? 'Kerja ('.$r->region?->name.')' : ($r ? 'Libur / Tidak Makan' : 'Belum diisi');
                    @endphp
                    <div style="display:flex;align-items:center;gap:10px;padding:5px 8px;border-radius:6px;{{ $isTdy ? 'background:#e8f5ee;' : ($isTmrw ? 'background:#eff6ff;' : '') }}">
                        <div style="width:36px;text-align:center;flex-shrink:0">
                            <p style="font-size:9px;font-weight:700;color:{{ $isTdy ? '#006738' : '#9ca3af' }};margin:0;text-transform:uppercase">{{ $day['date']->translatedFormat('D') }}</p>
                            <p style="font-size:13px;font-weight:{{ $isTdy ? '800' : '600' }};color:{{ $isTdy ? '#006738' : '#1a2332' }};margin:0;line-height:1.1">{{ $day['date']->format('d') }}</p>
                        </div>
                        <div style="width:6px;height:6px;border-radius:50%;background:{{ $dotClr }};flex-shrink:0"></div>
                        <p style="font-size:11.5px;color:{{ $r ? '#374151' : '#d97706' }};font-weight:{{ $isTdy||$isTmrw ? '600' : '400' }};margin:0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $txt }}
                        </p>
                        @if($isTdy)<span style="font-size:9px;font-weight:700;background:#006738;color:white;padding:1px 6px;border-radius:99px;flex-shrink:0">Hari Ini</span>@endif
                        @if($isTmrw)<span style="font-size:9px;font-weight:700;background:#1d4ed8;color:white;padding:1px 6px;border-radius:99px;flex-shrink:0">Besok</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Persuasive Notice --}}
            <div style="background:#fefce8;border:1px solid #fef08a;border-radius:10px;padding:10px 12px;margin-bottom:14px;display:flex;align-items:flex-start;gap:8px">
                <span style="font-size:16px;line-height:1">📢</span>
                <p style="font-size:11.5px;color:#854d0e;margin:0;line-height:1.4">
                    <strong>Mohon selalu isi roster Anda:</strong> Porsi makan hanya akan disiapkan oleh katering bagi karyawan yang telah mengisi jadwal roster kerja. Jika tidak mengisi, katering tidak akan menyiapkan porsi makan Anda.
                </p>
            </div>

            {{-- Quick action button for Roster --}}
            <div>
                <a href="{{ route('roster.index') }}" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;padding:9px 16px;font-weight:700">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Atur Roster
                </a>
            </div>
        </div>
    </div>

    {{-- ══ FOCUS 2: RENCANA MAKAN BESOK ══ --}}
    <div class="card" style="margin-bottom:18px;border-left:4px solid #1d4ed8">
        <div class="card-header" style="background:#fafafa">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">🍽️</span>
                <div>
                    <h3 style="font-size:14px;font-weight:700;color:#1a2332">Rencana Makan Besok</h3>
                    <p style="font-size:11px;color:#6b7280;margin:0">{{ $tomorrow->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
            <a href="{{ route('meal-plan.index') }}" style="font-size:12px;color:#1d4ed8;font-weight:700;text-decoration:none">Semua Jadwal →</a>
        </div>

        @if($tomorrowPlans->isNotEmpty())
        <div style="padding:0">
            @foreach($tomorrowPlans as $plan)
            @php
                $isCancelled = $plan->status === 'cancelled';
                $isActive = $plan->status === 'active';
                $isMess = $plan->mealLocation ? $plan->mealLocation->isMessHall() : true;
                $locName = $plan->mealLocation?->name ?? $plan->region?->name ?? 'Mess Hall';
            @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f1f5f9;gap:12px;
                 {{ $isCancelled ? 'background:#f8fafc;' : 'background:#ffffff;' }}">
                {{-- Left: Meal Name & Location --}}
                <div style="min-width:0;flex:1">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-size:13.5px;
                              {{ $isCancelled ? 'font-weight:700;color:#94a3b8;text-decoration:line-through;' : 'font-weight:800;color:#0f172a;' }}">
                            {{ $plan->mealType->name }}
                        </span>
                        @if($isCancelled)
                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10.5px;font-weight:800;color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;padding:1px 7px;border-radius:20px">
                                🚫 Dibatalkan
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10.5px;font-weight:800;color:#166534;background:#dcfce7;border:1px solid #86efac;padding:1px 7px;border-radius:20px">
                                🟢 Aktif
                            </span>
                        @endif
                    </div>
                    <p style="font-size:12px;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                              {{ $isCancelled ? 'color:#94a3b8;' : 'color:#4b5563;font-weight:600;' }}">
                        {{ $isMess ? '🍽️' : '🚚' }} {{ $locName }}
                    </p>
                </div>

                {{-- Right: Clean Actions --}}
                <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
                    @if($isActive)
                    <button type="button"
                            onclick="openLocationModal({{ $plan->id }}, '{{ $plan->mealType->name }}', '{{ $tomorrow->translatedFormat('d M Y') }}', {{ $plan->meal_location_id ?? 'null' }}, {{ $plan->region_id ?? auth()->user()->homebase_region_id ?? 1 }})"
                            style="font-size:11.5px;color:#334155;background:#f8fafc;border:1px solid #cbd5e1;border-radius:6px;padding:5px 10px;cursor:pointer;font-weight:600">
                        📍 Lokasi
                    </button>
                    <a href="{{ route('cancellation.create', $plan) }}"
                       style="font-size:11.5px;color:#dc2626;background:#ffffff;border:1px solid #fca5a5;border-radius:6px;padding:5px 10px;text-decoration:none;font-weight:600">
                        ✕ Batal
                    </a>
                    @elseif($isCancelled)
                    <form method="POST" action="{{ route('cancellation.reactivate', $plan) }}" style="margin:0">
                        @csrf
                        <button type="submit"
                                style="font-size:11.5px;color:#006738;background:#ffffff;border:1.5px solid #006738;border-radius:6px;padding:5px 12px;cursor:pointer;font-weight:800;box-shadow:0 1px 3px rgba(0,103,56,0.08)">
                            ↺ Aktifkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Eco-Friendly Educational Notice --}}
        <div style="background:#f0fdf4;border-top:1px solid #bbf7d0;padding:11px 16px;display:flex;align-items:flex-start;gap:9px">
            <span style="font-size:16px;line-height:1">🌱</span>
            <p style="font-size:11.5px;color:#166534;margin:0;line-height:1.45">
                <strong>Cegah Food Waste & Sayangi Bumi:</strong> Apabila besok Anda berencana membeli makan di luar atau tidak makan di katering, mohon berikan konfirmasi <strong>Batal</strong> sebelum cut-off agar katering tidak menyiapkan makanan berlebih dan mencegah terbuangnya makanan secara sia-sia.
            </p>
        </div>

        <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:11px;color:#475569">⏱ Batas cut-off: <strong>Hari ini 19:00 WIB</strong></span>
            <a href="{{ route('menu.index', ['date' => $tomorrow->format('Y-m-d')]) }}" style="font-size:11.5px;color:#006738;font-weight:700;text-decoration:none">Lihat Menu Besok →</a>
        </div>
        @else
        <div class="empty-state" style="padding:22px">
            <p style="font-size:13px;font-weight:600;color:#374151;margin:0">Belum ada jatah makan untuk besok</p>
            <p style="font-size:11.5px;color:#9ca3af;margin:4px 0 12px">Pastikan status roster besok diisi sebagai "Kerja" jika Anda bertugas di Ramba.</p>
            <a href="{{ route('roster.index') }}" class="btn btn-primary btn-sm">Atur Roster Besok →</a>
        </div>
        @endif
    </div>

    {{-- ══ FOCUS 3 & 4: MOVEMENT & OUTSIDE MEAL PLANNING ══ --}}
    <div style="display:grid;grid-template-columns:1fr;gap:16px;margin-bottom:18px">

        {{-- FOCUS 3: MOVEMENT --}}
        <div class="card" style="border-left:4px solid #065f46">
            <div style="padding:16px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px">
                    <div>
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                            <span style="font-size:16px">↔️</span>
                            <h3 style="font-size:13.5px;font-weight:800;color:#1a2332;margin:0">Ada Rencana Pindah Lokasi Kerja?</h3>
                        </div>
                        <p style="font-size:11.5px;color:#4b5563;margin:0;line-height:1.4">
                            Jika besok/hari mendatang Anda pindah wilayah (contoh: Ramba ➔ Bentayan/Mangunjaya), ajukan perpindahan lokasi agar dapur katering menyiapkan porsi makan di lokasi baru.
                        </p>
                    </div>
                </div>

                {{-- Status Permohonan Perpindahan Lokasi Aktif --}}
                @if($myRecentMovements->isNotEmpty())
                <div style="margin:10px 0;border-top:1px solid #f3f4f6;padding-top:8px">
                    <p style="font-size:10.5px;font-weight:700;color:#6b7280;margin:0 0 6px">Permohonan Perpindahan Terkini:</p>
                    @foreach($myRecentMovements as $mv)
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;padding:4px 0">
                        <span>{{ $mv->movement_date->translatedFormat('d M') }}: {{ $mv->fromRegion?->name }} ➔ {{ $mv->toRegion?->name }}</span>
                        @if($mv->status === 'approved')
                            <span class="badge badge-green" style="font-size:10px">✓ Disetujui</span>
                        @elseif($mv->status === 'pending')
                            <span class="badge badge-gold" style="font-size:10px">⏳ Menunggu GS</span>
                        @else
                            <span class="badge badge-red" style="font-size:10px">✕ Ditolak</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div style="margin-top:10px;display:flex;gap:8px">
                    <a href="{{ route('movement.create') }}" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                        + Ajukan Pindah Lokasi
                    </a>
                    <a href="{{ route('movement.index') }}" class="btn btn-secondary btn-sm">
                        Riwayat
                    </a>
                </div>
            </div>
        </div>

        {{-- FOCUS 4: OUTSIDE MEAL --}}
        <div class="card" style="border-left:4px solid #c2410c">
            <div style="padding:16px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px">
                    <div>
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                            <span style="font-size:16px">🍽️</span>
                            <h3 style="font-size:13.5px;font-weight:800;color:#1a2332;margin:0">Ada Rencana Event Meeting (Outside Meals)?</h3>
                        </div>
                        <p style="font-size:11.5px;color:#4b5563;margin:0;line-height:1.45">
                            Apabila Bapak/Ibu telah memesan konsumsi kegiatan operasional atau acara melalui tim GS sehingga tidak makan di katering, mohon kesediaannya mengajukan Outside Meal. Konfirmasi Anda sangat membantu agar dapur katering tidak memasak porsi berlebih dan bersama kita cegah <em>food waste</em>.
                        </p>
                    </div>
                </div>

                {{-- Status Permohonan Outside Meal Aktif --}}
                @if($myRecentOutsideMeals->isNotEmpty())
                <div style="margin:10px 0;border-top:1px solid #f3f4f6;padding-top:8px">
                    <p style="font-size:10.5px;font-weight:700;color:#6b7280;margin:0 0 6px">Permohonan Outside Meal Terkini:</p>
                    @foreach($myRecentOutsideMeals as $om)
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;padding:4px 0">
                        <span>{{ $om->meal_date->translatedFormat('d M') }} ({{ $om->mealType?->name }})</span>
                        @if($om->status === 'approved')
                            <span class="badge badge-green" style="font-size:10px">✓ Disetujui</span>
                        @elseif($om->status === 'pending')
                            <span class="badge badge-gold" style="font-size:10px">⏳ Menunggu GS</span>
                        @else
                            <span class="badge badge-red" style="font-size:10px">✕ Ditolak</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div style="margin-top:10px;display:flex;gap:8px">
                    <a href="{{ route('outside-meal.create') }}" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;background:#c2410c">
                        + Ajukan Outside Meal
                    </a>
                    <a href="{{ route('outside-meal.index') }}" class="btn btn-secondary btn-sm">
                        Riwayat
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ── RIGHT / SECONDARY INFO COLUMN ── --}}
<div>

    {{-- MAKAN HARI INI --}}
    <div class="card" style="margin-bottom:16px;border-left:4px solid #16a34a">
        <div class="card-header" style="background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">🍽️</span>
                <h3 style="font-size:14px;font-weight:700;color:#1a2332;margin:0">Jadwal Makan Hari Ini</h3>
            </div>
            <a href="{{ route('meal-plan.index') }}" style="font-size:12px;color:#16a34a;font-weight:600;text-decoration:none">Detail →</a>
        </div>
        @if($todayPlans->isNotEmpty())
        @foreach($todayPlans as $plan)
        @php
            $st = $statusMap[$plan->status] ?? ['label'=>$plan->status,'class'=>'badge-gray'];
        @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f9fafb">
            <div>
                <p style="font-size:12.5px;font-weight:700;color:#1a2332;margin:0">{{ $plan->mealType->name }}</p>
                <p style="font-size:11px;color:#6b7280;margin:2px 0 0">{{ $plan->mealLocation?->name ?? $plan->region?->name ?? '-' }}</p>
            </div>
            <span class="badge {{ $st['class'] }}" style="font-size:10.5px">{{ $st['label'] }}</span>
        </div>
        @endforeach
        @else
        <div style="padding:16px;text-align:center">
            <p style="font-size:12px;color:#9ca3af;margin:0">Tidak ada jadwal makan hari ini</p>
        </div>
        @endif
    </div>

    {{-- MENU HARI INI PREVIEW --}}
    @if($todayMenu->isNotEmpty())
    <div class="card" style="margin-bottom:16px;border-left:4px solid #f59e0b">
        <div class="card-header" style="background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">🍲</span>
                <h3 style="font-size:14px;font-weight:700;color:#1a2332;margin:0">Menu Hari Ini</h3>
            </div>
            <a href="{{ route('menu.index') }}" style="font-size:12px;color:#d97706;font-weight:600;text-decoration:none">Semua Menu →</a>
        </div>
        @foreach($todayMenu as $menu)
        <div style="padding:10px 16px;border-bottom:1px solid #f9fafb">
            <p style="font-size:11.5px;font-weight:700;color:#374151;margin:0 0 4px">{{ $menu->mealType->name }} · {{ $menu->region->name }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:4px">
                @foreach($menu->items->take(4) as $item)
                <span style="font-size:10.5px;background:#f4f6f8;border-radius:4px;padding:2px 6px;color:#4b5563">{{ $item->name }}</span>
                @endforeach
                @if($menu->items->count() > 4)
                <span style="font-size:10.5px;color:#9ca3af">+{{ $menu->items->count()-4 }} lagi</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- KIRIM SARAN SHORTCUT --}}
    <a href="{{ route('suggestion.create') }}" class="card" style="display:flex;align-items:center;gap:12px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px;padding:12px 16px;text-decoration:none;margin-bottom:16px">
        <div style="width:36px;height:36px;background:#e0f2fe;border:1px solid #93c5fd;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <span style="font-size:18px">💬</span>
        </div>
        <div style="flex:1">
            <p style="font-size:12.5px;font-weight:700;color:#0369a1;margin:0">Kirim Saran & Masukan</p>
            <p style="font-size:11px;color:#475569;margin:2px 0 0">Untuk perbaikan menu & layanan katering</p>
        </div>
        <svg width="15" height="15" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </a>

</div>

</div>{{-- end dash-grid --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL GANTI LOKASI MAKAN / TITIK PENGANTARAN
══════════════════════════════════════════════════════════════════════════ --}}
<div id="changeLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;display:flex;align-items:center;justify-content:space-between">
            <div>
                <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">📍 Pilih Lokasi Makan / Pengantaran</h3>
                <p id="modalMealSubtitle" style="font-size:11.5px;color:#6b7280;margin:2px 0 0">Pilih tempat makan atau lokasi pengantaran</p>
            </div>
            <button type="button" onclick="closeLocModal()" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form id="changeLocForm" method="POST" action="" style="padding:20px">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label">Pilih Lokasi Makan / Titik Pengantaran <span style="color:var(--red)">*</span></label>
                <select name="meal_location_id" id="modalLocationSelect" class="form-select" required>
                    @foreach($deliveryLocations as $loc)
                    @php
                        $isMess = $loc->isMessHall();
                    @endphp
                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}">
                        {{ $isMess ? '🍽️' : '🚚' }} {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Info Ketentuan Ambil vs Diantar --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-top:14px">
                <div style="font-size:12px;color:#334155;line-height:1.45">
                    <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:6px">
                        <span>🍽️</span>
                        <span><strong>Pilih Mess Hall:</strong> Makanan <strong>diambil sendiri</strong> di Mess Hall (baik makan di tempat / dine in maupun bawa sendiri / take away). <em>Tidak diantar</em>.</span>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:6px">
                        <span>🚚</span>
                        <span><strong>Pilih Selain Mess Hall:</strong> Makanan akan <strong>diantar oleh katering</strong> ke titik lokasi kerja Anda (Kantor / SP / Pos).</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#006738">Simpan Pilihan Lokasi</button>
                <button type="button" onclick="closeLocModal()" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
.dash-grid { display:grid; grid-template-columns:1fr; gap:0; }
@media(min-width:1024px) { .dash-grid { grid-template-columns:1fr 320px; gap:20px; } }
</style>

@push('scripts')
<script>
function openLocationModal(planId, mealName, mealDate, currentLocId, regionId) {
    document.getElementById('changeLocForm').action = '/rencana-makan/' + planId + '/lokasi';
    document.getElementById('modalMealSubtitle').textContent = mealName + ' · ' + mealDate;
    
    var select = document.getElementById('modalLocationSelect');
    var options = select.options;
    var firstVisibleValue = null;
    var isCurrentLocVisible = false;

    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        var optRegion = opt.getAttribute('data-region');
        
        if (!regionId || optRegion == regionId) {
            opt.hidden = false;
            opt.disabled = false;
            if (!firstVisibleValue) firstVisibleValue = opt.value;
            if (opt.value == currentLocId) isCurrentLocVisible = true;
        } else {
            opt.hidden = true;
            opt.disabled = true;
        }
    }

    if (currentLocId && isCurrentLocVisible) {
        select.value = currentLocId;
    } else if (firstVisibleValue) {
        select.value = firstVisibleValue;
    }

    document.getElementById('changeLocModal').style.display = 'flex';
}
function closeLocModal() {
    document.getElementById('changeLocModal').style.display = 'none';
}
</script>
@endpush
@endsection
