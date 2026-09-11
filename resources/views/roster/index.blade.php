@extends('layouts.app')
@section('title', 'Roster')
@section('page-title', 'Kelola Roster')

@section('content')
@php
    $today = \Carbon\Carbon::today();

    // Build full calendar for the month (Sun-Sat grid)
    $start = $month->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $end   = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);

    $statusColors = [
        'Kerja' => ['bg'=>'#e8f5ee', 'dot'=>'#006738', 'text'=>'#006738', 'label'=>'Kerja (Makan)'],
        'Libur' => ['bg'=>'#f3f4f6', 'dot'=>'#6b7280', 'text'=>'#374151', 'label'=>'Libur / Tidak Makan (Dinas)'],
    ];

    $isManager = auth()->user()->isGS() || auth()->user()->isAdminDept() || auth()->user()->isSysAdmin();
@endphp

<div style="max-width:580px">

{{-- Searchable Karyawan Selector for GS / Admin Dept --}}
@if($isManager && $users->count() > 1)
<div class="card" style="margin-bottom:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:visible!important">
    <div class="card-body" style="padding:14px 16px;overflow:visible!important">
        <label class="form-label" style="margin-bottom:6px;font-size:12px;font-weight:700;color:#15803d">Cari & Pilih Karyawan</label>
        
        <div style="position:relative">
            <div style="display:flex;align-items:center;position:relative">
                <svg width="15" height="15" fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="position:absolute;left:12px;pointer-events:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       id="userSearchInput"
                       class="form-input"
                       style="padding-left:36px;padding-right:32px;font-size:13px;border-radius:10px;border-color:#bbf7d0;background:#fff"
                       placeholder="Ketik nama karyawan, departemen, atau status pegawai..."
                       autocomplete="off"
                       onfocus="showUserDropdown()"
                       oninput="filterUsers(this.value)">
                <button type="button"
                        id="userSearchClearBtn"
                        onclick="clearUserSearch()"
                        style="display:none;position:absolute;right:10px;border:none;background:transparent;color:#9ca3af;font-size:14px;cursor:pointer;padding:4px">✕</button>
            </div>

            {{-- Live Search Dropdown Popup --}}
            <div id="userSearchDropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;max-height:340px;overflow-y:auto;background:#fff;border:1.5px solid #cbd5e1;border-radius:12px;box-shadow:0 16px 36px rgba(0,0,0,.16);z-index:999;padding:6px">
                @foreach($users as $u)
                @php
                    $deptName = $u->department?->name ?? 'General';
                    $statusName = $u->workerStatus?->name ?? $u->role?->name ?? 'Pekerja';
                    $isSelected = $targetUser->id == $u->id;
                    $searchData = strtolower($u->name . ' ' . $deptName . ' ' . $statusName);
                @endphp
                <div class="user-search-item"
                     data-search="{{ $searchData }}"
                     onclick="selectUserRoster('{{ $u->id }}')"
                     style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;cursor:pointer;transition:background .12s;border-bottom:1px solid #f8fafc;{{ $isSelected ? 'background:#f0fdf4;' : '' }}"
                     onmouseover="this.style.background='{{ $isSelected ? '#e8f5ee' : '#f8fafc' }}'"
                     onmouseout="this.style.background='{{ $isSelected ? '#f0fdf4' : 'transparent' }}'">
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $isSelected ? '#006738' : '#e5e7eb' }};color:{{ $isSelected ? '#fff' : '#4b5563' }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($u->name, 0, 2)) }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <p style="font-size:12.5px;font-weight:700;color:#1a2332;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $u->name }}
                        </p>
                        <p style="font-size:11px;color:#6b7280;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $u->name }} — {{ $deptName }} — {{ $statusName }}
                        </p>
                    </div>
                    @if($isSelected)
                    <span style="font-size:11px;font-weight:700;color:#006738;background:#ecfdf5;padding:2px 7px;border-radius:6px;flex-shrink:0">Aktif</span>
                    @endif
                </div>
                @endforeach
                <div id="userSearchEmpty" style="display:none;padding:16px;text-align:center;color:#9ca3af;font-size:12px">
                    Tidak ditemukan karyawan yang cocok.
                </div>
            </div>
        </div>

        {{-- Current selected user bar --}}
        <div style="margin-top:8px;font-size:11.5px;color:#166534;display:flex;align-items:center;gap:6px">
            <span style="color:#16a34a">●</span>
            <span>Karyawan aktif: <strong>{{ $targetUser->name }}</strong> — {{ $targetUser->department?->name ?? 'General' }} — {{ $targetUser->workerStatus?->name ?? $targetUser->role?->name ?? 'Pekerja' }}</span>
        </div>
    </div>
</div>
@endif

{{-- Target User info banner if viewing someone else --}}
@if($targetUser->id !== auth()->id())
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #0284c7;border-radius:12px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
    <svg width="16" height="16" fill="#1d4ed8" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
    <p style="font-size:12px;color:#1e40af;margin:0">
        Mengelola roster untuk: <strong>{{ $targetUser->name }}</strong> ({{ $targetUser->department?->name ?? $targetUser->role?->name }})
    </p>
</div>
@endif

{{-- Persuasive Notice Banner --}}
<div style="background:#fefce8;border:1px solid #fef08a;border-left:4px solid #d97706;border-radius:12px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;box-shadow:0 1px 3px rgba(0,0,0,.02)">
    <span style="font-size:18px;line-height:1">📢</span>
    <div style="font-size:12.5px;color:#854d0e;line-height:1.45">
        <strong>Penting — Mohon Selalu Isi Roster Anda:</strong><br>
        Porsi makan hanya akan disiapkan oleh katering bagi karyawan yang telah mengisi jadwal roster kerja. Jika tidak mengisi roster, pihak katering tidak akan menyiapkan porsi makan Anda.
    </div>
</div>

{{-- Quick Action Card: Atur Rentang Tanggal (Komprehensif & Informatif) --}}
<div class="card" style="margin-bottom:18px;background:linear-gradient(to right, #f0fdf4, #ffffff);border:1.5px solid #86efac;border-left:5px solid #006738;border-radius:14px;box-shadow:0 4px 12px rgba(0,103,56,0.06);overflow:hidden">
    <div style="padding:16px 18px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px">
            <div style="display:inline-flex;align-items:center;gap:6px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.3px;text-transform:uppercase">
                <span>⚡</span> Fitur Cepat & Praktis
            </div>
            <span style="font-size:11.5px;color:#64748b;font-weight:600">Hemat Waktu</span>
        </div>

        <h3 style="font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 6px;line-height:1.3">
            Atur Jadwal Sekaligus (Rentang Tanggal)
        </h3>

        <p style="font-size:12.5px;color:#334155;line-height:1.55;margin:0 0 12px">
            Tidak perlu klik kalender satu per satu! Gunakan tombol di bawah ini jika Anda ingin menetapkan jadwal untuk <strong>beberapa hari berturut-turut</strong> (misalnya 1 siklus shift, dinas luar, cuti, atau periode kerja).
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:11.5px;color:#475569">
            <div style="display:flex;align-items:flex-start;gap:6px">
                <span style="color:#006738;font-size:14px;line-height:1">🍽️</span>
                <span><strong>Kerja / Shift:</strong> Otomatis buat jatah makan seluruh hari.</span>
            </div>
            <div style="display:flex;align-items:flex-start;gap:6px">
                <span style="color:#64748b;font-size:14px;line-height:1">✈️</span>
                <span><strong>Dinas / Libur:</strong> Otomatis batalkan jatah makan agar pas.</span>
            </div>
        </div>

        <button type="button" onclick="openBulkModal()"
                class="btn-primary"
                style="width:100%;padding:12px 18px;font-size:13.5px;font-weight:800;border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 10px rgba(0,103,56,0.25);cursor:pointer">
            Buka Form Atur Rentang Tanggal
        </button>
    </div>
</div>

{{-- Month Navigator --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;background:#fafafa;border:1px solid #e8edf2;border-left:4px solid #006738;border-radius:12px;padding:10px 16px">
    <a href="{{ route('roster.index', array_merge(request()->all(), ['month'=>$month->copy()->subMonth()->format('Y-m-d'), 'user_id'=>$targetUser->id])) }}"
       style="width:34px;height:34px;background:#fff;border:1px solid #e8edf2;border-radius:8px;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div style="text-align:center">
        <h2 style="font-size:15px;font-weight:700;color:#1a2332;margin:0">{{ $month->translatedFormat('F Y') }}</h2>
        <p style="font-size:11px;color:#6b7280;margin:2px 0 0">Klik tanggal untuk mengatur Kerja / Libur</p>
    </div>
    <a href="{{ route('roster.index', array_merge(request()->all(), ['month'=>$month->copy()->addMonth()->format('Y-m-d'), 'user_id'=>$targetUser->id])) }}"
       style="width:34px;height:34px;background:#fff;border:1px solid #e8edf2;border-radius:8px;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>
@php
    $isShiftWorker = (bool)$targetUser->is_shift;
    $shiftColors = [
        'shift_pagi'  => ['bg' => '#f0fdf4', 'dot' => '#16a34a', 'text' => '#15803d', 'icon' => '☀️'],
        'shift_malam' => ['bg' => '#faf5ff', 'dot' => '#9333ea', 'text' => '#7e22ce', 'icon' => '🌙'],
        'non_shift'   => ['bg' => '#e8f5ee', 'dot' => '#006738', 'text' => '#006738', 'icon' => '🍴'],
        'libur'       => ['bg' => '#f3f4f6', 'dot' => '#6b7280', 'text' => '#4b5563', 'icon' => '🚫'],
    ];
@endphp

{{-- Calendar Card --}}
<div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    {{-- Day Headers --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);background:#fafafa;border-bottom:1px solid #f3f4f6">
        @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $day)
        <div style="text-align:center;font-size:10.5px;font-weight:700;color:#6b7280;padding:10px 2px;text-transform:uppercase">{{ $day }}</div>
        @endforeach
    </div>

    {{-- Day Cells --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr)">
        @for($d = $start->copy(); $d->lte($end); $d->addDay())
        @php
            $ds = $d->format('Y-m-d');
            $inMonth  = $d->month === $month->month;
            $isToday  = $d->isToday();
            $isPast   = $d->lt($today);
            $roster   = $rosters->get($ds);
            
            $cellKey = null;
            if ($roster) {
                if ($roster->status === 'Kerja') {
                    $cellKey = $roster->shift_type ?? ($targetUser->shift_type ?? ($isShiftWorker ? 'shift_pagi' : 'non_shift'));
                } else {
                    $cellKey = 'libur';
                }
            }
            $sc = $cellKey ? ($shiftColors[$cellKey] ?? null) : null;
        @endphp
        @if($inMonth && (!$isPast || $isManager))
        {{-- Clickable cells --}}
        <button type="button"
                onclick="openRosterModal('{{ $ds }}', '{{ $roster?->status ?? 'Kerja' }}', '{{ $roster?->region_id ?? ($targetUser->homebase_region_id ?? $regions->first()?->id ?? 1) }}', '{{ $roster?->notes ?? '' }}', '{{ $roster?->shift_type ?? ($targetUser->shift_type ?? ($isShiftWorker ? 'shift_pagi' : 'non_shift')) }}')"
                style="aspect-ratio:1;padding:4px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;border:none;border-right:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6;cursor:pointer;background:{{ $sc ? $sc['bg'] : '#fff' }};{{ $isToday ? 'outline:2.5px solid #006738;outline-offset:-2.5px;z-index:2;' : '' }}transition:background .1s">
            <span style="font-size:13px;font-weight:{{ $isToday ? '800' : '600' }};color:{{ $sc ? $sc['text'] : ($isToday ? '#006738' : '#374151') }}">{{ $d->day }}</span>
            @if($sc)
            <div style="display:flex;align-items:center;gap:2px">
                <span style="width:5px;height:5px;border-radius:50%;background:{{ $sc['dot'] }}"></span>
                @if($cellKey === 'shift_malam')
                <span style="font-size:8px;line-height:1">🌙</span>
                @endif
            </div>
            @else
            <span style="width:5px;height:5px;border-radius:50%;background:#e5e7eb"></span>
            @endif
        </button>
        @else
        {{-- Non-clickable past / out-of-month cells --}}
        <div style="aspect-ratio:1;padding:4px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;border-right:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6;background:{{ $sc && $inMonth ? $sc['bg'] : '#fafafa' }};opacity:{{ $inMonth ? '0.7' : '0.2' }}">
            <span style="font-size:13px;font-weight:400;color:{{ $sc && $inMonth ? $sc['text'] : '#c4c9d4' }}">{{ $d->day }}</span>
            @if($sc && $inMonth)
            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sc['dot'] }}"></span>
            @endif
        </div>
        @endif
        @endfor
    </div>
</div>

{{-- Legend --}}
<div style="display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:16px;flex-wrap:wrap">
    @if($isShiftWorker)
    <div style="display:flex;align-items:center;gap:5px">
        <span style="width:9px;height:9px;border-radius:50%;background:#16a34a;display:inline-block"></span>
        <span style="font-size:11.5px;font-weight:600;color:#15803d">Shift Pagi</span>
    </div>
    <div style="display:flex;align-items:center;gap:5px">
        <span style="width:9px;height:9px;border-radius:50%;background:#9333ea;display:inline-block"></span>
        <span style="font-size:11.5px;font-weight:600;color:#7e22ce">Shift Malam</span>
    </div>
    @else
    <div style="display:flex;align-items:center;gap:5px">
        <span style="width:9px;height:9px;border-radius:50%;background:#006738;display:inline-block"></span>
        <span style="font-size:11.5px;font-weight:600;color:#006738">Kerja</span>
    </div>
    @endif
    <div style="display:flex;align-items:center;gap:5px">
        <span style="width:9px;height:9px;border-radius:50%;background:#6b7280;display:inline-block"></span>
        <span style="font-size:11.5px;font-weight:600;color:#4b5563">Libur / Dinas Luar</span>
    </div>
    <div style="display:flex;align-items:center;gap:5px">
        <span style="width:9px;height:9px;border-radius:50%;background:#e5e7eb;display:inline-block"></span>
        <span style="font-size:11.5px;font-weight:500;color:#9ca3af">Belum Diisi</span>
    </div>
</div>

{{-- Month Summary Counters --}}
@php
    $kerjaCount = $rosters->where('status', 'Kerja')->count();
    $shiftPagiCount = $rosters->where('status', 'Kerja')->where('shift_type', 'shift_pagi')->count();
    $shiftMalamCount = $rosters->where('status', 'Kerja')->where('shift_type', 'shift_malam')->count();
    $liburCount = $rosters->where('status', '!=', 'Kerja')->count();
@endphp
@if($rosters->isNotEmpty())
@if($isShiftWorker)
<div class="card" style="background:#fff;border-radius:12px;border:1px solid #e8edf2;border-left:4px solid #006738;padding:12px 14px;margin-bottom:20px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
    <div style="background:#f0fdf4;border-radius:9px;padding:10px;text-align:center">
        <p style="font-size:20px;font-weight:800;color:#15803d;margin:0;line-height:1.2">{{ $shiftPagiCount }}</p>
        <p style="font-size:10.5px;font-weight:600;color:#15803d;margin:2px 0 0">Shift Pagi</p>
    </div>
    <div style="background:#faf5ff;border-radius:9px;padding:10px;text-align:center">
        <p style="font-size:20px;font-weight:800;color:#7e22ce;margin:0;line-height:1.2">{{ $shiftMalamCount }}</p>
        <p style="font-size:10.5px;font-weight:600;color:#7e22ce;margin:2px 0 0">Shift Malam</p>
    </div>
    <div style="background:#f3f4f6;border-radius:9px;padding:10px;text-align:center">
        <p style="font-size:20px;font-weight:800;color:#374151;margin:0;line-height:1.2">{{ $liburCount }}</p>
        <p style="font-size:10.5px;font-weight:600;color:#4b5563;margin:2px 0 0">Libur / Dinas</p>
    </div>
</div>
@else
<div class="card" style="background:#fff;border-radius:12px;border:1px solid #e8edf2;border-left:4px solid #006738;padding:12px 14px;margin-bottom:20px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
    <div style="background:#e8f5ee;border-radius:9px;padding:10px;text-align:center">
        <p style="font-size:22px;font-weight:800;color:#006738;margin:0;line-height:1.2">{{ $kerjaCount }}</p>
        <p style="font-size:11px;font-weight:600;color:#006738;margin:2px 0 0">Hari Kerja (Makan)</p>
    </div>
    <div style="background:#f3f4f6;border-radius:9px;padding:10px;text-align:center">
        <p style="font-size:22px;font-weight:800;color:#374151;margin:0;line-height:1.2">{{ $liburCount }}</p>
        <p style="font-size:11px;font-weight:600;color:#4b5563;margin:2px 0 0">Hari Libur / Dinas</p>
    </div>
</div>
@endif
@endif

</div>

{{-- ── MODAL 1: EDIT ROSTER TANGGAL TUNGGAL ── --}}
<div id="rosterModal"
     style="display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.5);align-items:flex-end;justify-content:center;backdrop-filter:blur(2px)"
     onclick="if(event.target===this)closeRosterModal()">
    <div style="background:#fff;border-radius:20px 20px 0 0;width:100%;max-width:480px;padding:22px;box-shadow:0 -10px 40px rgba(0,0,0,.2);animation:slideUp .2s ease-out">
        <div style="width:38px;height:4px;background:#e5e7eb;border-radius:99px;margin:0 auto 16px"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <h3 style="font-size:15px;font-weight:700;color:#1a2332;margin:0">Atur Jadwal Roster</h3>
            <button type="button" onclick="closeRosterModal()" style="border:none;background:#f3f4f6;border-radius:50%;width:26px;height:26px;cursor:pointer;color:#6b7280;display:flex;align-items:center;justify-content:center">✕</button>
        </div>
        <p id="rosterDateLabel" style="font-size:12px;color:#006738;font-weight:600;margin:0 0 16px"></p>

        <form method="POST" action="{{ route('roster.store') }}" id="rosterForm">
            @csrf
            <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
            <input type="hidden" name="roster_date" id="rosterDateInput">
            <input type="hidden" name="status" id="rosterStatusInput" value="Kerja">
            <input type="hidden" name="shift_type" id="rosterShiftTypeInput" value="{{ $isShiftWorker ? 'shift_pagi' : 'non_shift' }}">

            {{-- Status Options --}}
            @if($isShiftWorker)
            {{-- Pilihan Pekerja Shift --}}
            <p style="font-size:12px;font-weight:600;color:#374151;margin:0 0 8px">Pola Jam Kerja Shift</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px">
                <div id="btnShiftPagi"
                     onclick="setSingleAction('shift_pagi')"
                     style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:12.5px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .12s">
                    <div style="font-size:16px;margin-bottom:2px">☀️</div>
                    <div>Shift Pagi</div>
                </div>

                <div id="btnShiftMalam"
                     onclick="setSingleAction('shift_malam')"
                     style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:12.5px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .12s">
                    <div style="font-size:16px;margin-bottom:2px">🌙</div>
                    <div>Shift Malam</div>
                </div>

                <div id="btnLibur"
                     onclick="setSingleAction('libur')"
                     style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:12.5px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .12s">
                    <div style="font-size:16px;margin-bottom:2px">🚫</div>
                    <div>Libur / Dinas</div>
                </div>
            </div>
            @else
            {{-- Pilihan Pekerja Non-Shift --}}
            <p style="font-size:12px;font-weight:600;color:#374151;margin:0 0 8px">Status Jadwal</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                <div id="btnKerjaNormal"
                     onclick="setSingleAction('non_shift')"
                     style="padding:12px 8px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:13px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .12s">
                    <div style="font-size:16px;margin-bottom:2px">🍴 Kerja</div>
                    <div style="font-size:10px;font-weight:500;opacity:.85">Bfast, Lunch, Dinner</div>
                </div>

                <div id="btnLibur"
                     onclick="setSingleAction('libur')"
                     style="padding:12px 8px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:13px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .12s">
                    <div style="font-size:16px;margin-bottom:2px">🚫 Libur / Dinas</div>
                    <div style="font-size:10px;font-weight:500;opacity:.85">Tidak Makan / Dinas</div>
                </div>
            </div>
            @endif

            {{-- Wilayah --}}
            <div class="form-group" id="regionFormGroup">
                <label class="form-label">Wilayah Tugas & Dapur</label>
                <select name="region_id" id="rosterRegionId" class="form-select">
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}" {{ ($targetUser->homebase_region_id == $r->id) ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Meal plan otomatis dibuat sesuai dapur wilayah ini.</p>
            </div>

            {{-- Catatan --}}
            <div class="form-group">
                <label class="form-label">Keterangan / Alasan (opsional)</label>
                <input type="text" name="notes" id="rosterNotesInput" class="form-input" placeholder="Contoh: Dinas Luar / CTO / Offduty">
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="padding:11px">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Roster
            </button>
        </form>
    </div>
</div>

{{-- ── MODAL 2: ATUR RENTANG TANGGAL (DINAS / TIDAK MAKAN / PERIODE SHIFT) ── --}}
<div id="bulkModal"
     style="display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.5);align-items:flex-end;justify-content:center;backdrop-filter:blur(2px)"
     onclick="if(event.target===this)closeBulkModal()">
    <div style="background:#fff;border-radius:20px 20px 0 0;width:100%;max-width:500px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 -10px 40px rgba(0,0,0,.2);animation:slideUp .2s ease-out">
        <div style="padding:22px 22px 0 22px;flex-shrink:0">
            <div style="width:38px;height:4px;background:#e5e7eb;border-radius:99px;margin:0 auto 16px"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <h3 style="font-size:16px;font-weight:800;color:#1a2332;margin:0">Atur Jadwal Sekaligus (Rentang Tanggal)</h3>
                <button type="button" onclick="closeBulkModal()" style="border:none;background:#f3f4f6;border-radius:50%;width:26px;height:26px;cursor:pointer;color:#6b7280;display:flex;align-items:center;justify-content:center">✕</button>
            </div>
            <p style="font-size:12.5px;color:#475569;margin:0 0 16px;line-height:1.45">
                Pilih status tugas, tentukan tanggal mulai & berakhir. Sistem akan otomatis menerapkan jadwal untuk semua hari tersebut.
            </p>
        </div>

        <div style="padding:0 22px 22px 22px;overflow-y:auto;flex-grow:1;padding-bottom:env(safe-area-inset-bottom, 22px);">
        <form method="POST" action="{{ route('roster.bulk') }}" id="bulkForm">
            @csrf
            <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
            <input type="hidden" name="status" id="bulkStatusInput" value="Libur">
            <input type="hidden" name="shift_type" id="bulkShiftTypeInput" value="{{ $isShiftWorker ? 'shift_pagi' : 'non_shift' }}">

            {{-- Status Options (Bulk) --}}
            <div class="form-group">
                <label class="form-label">Pilih Status untuk Rentang Tanggal</label>
                @if($isShiftWorker)
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
                    <div id="bulkBtnShiftPagi"
                         onclick="setBulkAction('shift_pagi')"
                         style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:12px;font-weight:700;color:#6b7280;cursor:pointer">
                        <div style="font-size:16px;margin-bottom:2px">☀️</div>
                        <div>Shift Pagi</div>
                    </div>
                    <div id="bulkBtnShiftMalam"
                         onclick="setBulkAction('shift_malam')"
                         style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;text-align:center;font-size:12px;font-weight:700;color:#6b7280;cursor:pointer">
                        <div style="font-size:16px;margin-bottom:2px">🌙</div>
                        <div>Shift Malam</div>
                    </div>
                    <div id="bulkBtnLibur"
                         onclick="setBulkAction('libur')"
                         style="padding:12px 6px;border-radius:10px;border:2px solid #e8edf2;background:#f3f4f6;text-align:center;font-size:12px;font-weight:700;color:#374151;cursor:pointer">
                        <div style="font-size:16px;margin-bottom:2px">🚫</div>
                        <div>Libur / Dinas</div>
                    </div>
                </div>
                @else
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div id="bulkBtnLibur"
                         onclick="setBulkAction('libur')"
                         style="padding:12px 8px;border-radius:10px;border:2px solid #e8edf2;background:#f3f4f6;color:#374151;text-align:center;font-size:13px;font-weight:700;cursor:pointer">
                        <div style="font-size:14px;margin-bottom:2px">🚫 Tidak Makan / Dinas</div>
                        <div style="font-size:10px;font-weight:500;opacity:.85">Meal plan otomatis dibatalkan</div>
                    </div>
                    <div id="bulkBtnKerjaNormal"
                         onclick="setBulkAction('non_shift')"
                         style="padding:12px 8px;border-radius:10px;border:2px solid #e8edf2;background:#fff;color:#6b7280;text-align:center;font-size:13px;font-weight:700;cursor:pointer">
                        <div style="font-size:14px;margin-bottom:2px">🍴 Kerja (Makan)</div>
                        <div style="font-size:10px;font-weight:500;opacity:.85">Meal plan otomatis dibuat</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Date Range --}}
            <div class="grid-2" style="gap:10px;margin-bottom:14px">
                <div>
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" id="bulkStartDate"
                           value="{{ now()->addDay()->format('Y-m-d') }}"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="form-input" required onchange="updateBulkDaysCount()">
                </div>
                <div>
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="bulkEndDate"
                           value="{{ now()->addDays(4)->format('Y-m-d') }}"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="form-input" required onchange="updateBulkDaysCount()">
                </div>
            </div>

            <div id="bulkSummaryNotice" style="background:#fffbeb;border:1px solid #fde68a;border-radius:9px;padding:8px 12px;margin-bottom:14px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:6px">
                <span id="bulkDaysCountText">Total: 4 hari akan diatur sebagai Tidak Makan</span>
            </div>

            {{-- Wilayah (hanya jika Kerja) --}}
            <div class="form-group" id="bulkRegionFormGroup" style="display:none">
                <label class="form-label">Wilayah Tugas</label>
                <select name="region_id" class="form-select">
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}" {{ ($targetUser->homebase_region_id == $r->id) ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Catatan --}}
            <div class="form-group">
                <label class="form-label">Keterangan / Alasan (opsional)</label>
                <input type="text" name="notes" class="form-input" placeholder="Contoh: Dinas Luar / CTO / Offduty">
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="padding:11px">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Terapkan ke Rentang Tanggal
            </button>
        </form>
        </div>
    </div>
</div>

<style>
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
</style>

@push('scripts')
<script>
var isShiftWorker = {{ $isShiftWorker ? 'true' : 'false' }};
var currentSingleAction = 'non_shift';
var currentBulkAction = 'libur';

function openRosterModal(dateStr, status, regionId, notes, shiftType) {
    document.getElementById('rosterModal').style.display = 'flex';
    document.getElementById('rosterDateInput').value = dateStr;
    document.getElementById('rosterNotesInput').value = notes || '';

    var d = new Date(dateStr + 'T00:00:00');
    document.getElementById('rosterDateLabel').textContent =
        '📅 ' + d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });

    var action = 'non_shift';
    if (status !== 'Kerja') {
        action = 'libur';
    } else {
        action = shiftType || (isShiftWorker ? 'shift_pagi' : 'non_shift');
    }
    setSingleAction(action);

    var sel = document.getElementById('rosterRegionId');
    if (regionId && sel) {
        for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value == regionId) { sel.selectedIndex = i; break; }
        }
    }
}

function setSingleAction(action) {
    currentSingleAction = action;
    var statusInput = document.getElementById('rosterStatusInput');
    var shiftInput  = document.getElementById('rosterShiftTypeInput');
    var regionGroup = document.getElementById('regionFormGroup');

    if (action === 'libur') {
        statusInput.value = 'Libur';
        shiftInput.value  = isShiftWorker ? 'shift_pagi' : 'non_shift';
        if (regionGroup) regionGroup.style.display = 'none';
    } else if (action === 'shift_malam') {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'shift_malam';
        if (regionGroup) regionGroup.style.display = 'block';
    } else if (action === 'shift_pagi') {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'shift_pagi';
        if (regionGroup) regionGroup.style.display = 'block';
    } else {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'non_shift';
        if (regionGroup) regionGroup.style.display = 'block';
    }

    // Update buttons UI
    var btns = {
        'shift_pagi':  { id:'btnShiftPagi', border:'#16a34a', bg:'#f0fdf4', color:'#15803d' },
        'shift_malam': { id:'btnShiftMalam', border:'#9333ea', bg:'#faf5ff', color:'#7e22ce' },
        'non_shift':   { id:'btnKerjaNormal', border:'#006738', bg:'#e8f5ee', color:'#006738' },
        'libur':       { id:'btnLibur', border:'#6b7280', bg:'#f3f4f6', color:'#374151' }
    };

    Object.keys(btns).forEach(function(k) {
        var el = document.getElementById(btns[k].id);
        if (el) {
            if (k === action) {
                el.style.borderColor = btns[k].border;
                el.style.background  = btns[k].bg;
                el.style.color       = btns[k].color;
            } else {
                el.style.borderColor = '#e8edf2';
                el.style.background  = '#fff';
                el.style.color       = '#6b7280';
            }
        }
    });
}

function closeRosterModal() {
    document.getElementById('rosterModal').style.display = 'none';
}

// Bulk Modal Functions
function openBulkModal() {
    document.getElementById('bulkModal').style.display = 'flex';
    setBulkAction('libur');
    updateBulkDaysCount();
}

function closeBulkModal() {
    document.getElementById('bulkModal').style.display = 'none';
}

function setBulkAction(action) {
    currentBulkAction = action;
    var statusInput = document.getElementById('bulkStatusInput');
    var shiftInput  = document.getElementById('bulkShiftTypeInput');
    var regionGroup = document.getElementById('bulkRegionFormGroup');

    if (action === 'libur') {
        statusInput.value = 'Libur';
        shiftInput.value  = isShiftWorker ? 'shift_pagi' : 'non_shift';
        if (regionGroup) regionGroup.style.display = 'none';
    } else if (action === 'shift_malam') {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'shift_malam';
        if (regionGroup) regionGroup.style.display = 'block';
    } else if (action === 'shift_pagi') {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'shift_pagi';
        if (regionGroup) regionGroup.style.display = 'block';
    } else {
        statusInput.value = 'Kerja';
        shiftInput.value  = 'non_shift';
        if (regionGroup) regionGroup.style.display = 'block';
    }

    // Update bulk buttons UI
    var btns = {
        'shift_pagi':  { id:'bulkBtnShiftPagi', border:'#16a34a', bg:'#f0fdf4', color:'#15803d' },
        'shift_malam': { id:'bulkBtnShiftMalam', border:'#9333ea', bg:'#faf5ff', color:'#7e22ce' },
        'non_shift':   { id:'bulkBtnKerjaNormal', border:'#006738', bg:'#e8f5ee', color:'#006738' },
        'libur':       { id:'bulkBtnLibur', border:'#6b7280', bg:'#f3f4f6', color:'#374151' }
    };

    Object.keys(btns).forEach(function(k) {
        var el = document.getElementById(btns[k].id);
        if (el) {
            if (k === action) {
                el.style.borderColor = btns[k].border;
                el.style.background  = btns[k].bg;
                el.style.color       = btns[k].color;
            } else {
                el.style.borderColor = '#e8edf2';
                el.style.background  = '#fff';
                el.style.color       = '#6b7280';
            }
        }
    });

    updateBulkDaysCount();
}

function updateBulkDaysCount() {
    var startVal = document.getElementById('bulkStartDate').value;
    var endVal = document.getElementById('bulkEndDate').value;
    if (startVal && endVal) {
        var d1 = new Date(startVal + 'T00:00:00');
        var d2 = new Date(endVal + 'T00:00:00');
        var diffTime = d2.getTime() - d1.getTime();
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        if (diffDays > 0) {
            var actionNames = {
                'shift_pagi': 'Kerja [Shift Pagi]',
                'shift_malam': 'Kerja [Shift Malam]',
                'non_shift': 'Kerja',
                'libur': 'Tidak Makan / Dinas Luar'
            };
            var statusName = actionNames[currentBulkAction] || 'Tidak Makan';
            document.getElementById('bulkDaysCountText').textContent =
                'Total: ' + diffDays + ' hari akan diatur sebagai [' + statusName + ']';
        } else {
            document.getElementById('bulkDaysCountText').textContent = 'Tanggal akhir harus sama atau setelah tanggal mulai.';
        }
    }
}

// User Live Search functions
function showUserDropdown() {
    var dropdown = document.getElementById('userSearchDropdown');
    if (dropdown) dropdown.style.display = 'block';
}

function filterUsers(query) {
    query = (query || '').toLowerCase().trim();
    var items = document.querySelectorAll('.user-search-item');
    var clearBtn = document.getElementById('userSearchClearBtn');
    var emptyMsg = document.getElementById('userSearchEmpty');
    var dropdown = document.getElementById('userSearchDropdown');
    var visibleCount = 0;

    if (dropdown) dropdown.style.display = 'block';

    if (clearBtn) {
        clearBtn.style.display = query.length > 0 ? 'block' : 'none';
    }

    items.forEach(function(item) {
        var searchData = item.getAttribute('data-search') || '';
        if (!query || searchData.includes(query)) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    if (emptyMsg) {
        emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function clearUserSearch() {
    var input = document.getElementById('userSearchInput');
    if (input) {
        input.value = '';
        filterUsers('');
        input.focus();
    }
}

function selectUserRoster(userId) {
    var month = '{{ $month->format('Y-m-d') }}';
    window.location.href = '{{ route('roster.index') }}?month=' + month + '&user_id=' + userId;
}

// Close search dropdown on click outside
document.addEventListener('click', function(e) {
    var searchBox = document.getElementById('userSearchInput');
    var dropdown = document.getElementById('userSearchDropdown');
    if (searchBox && dropdown && !searchBox.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
</script>
@endpush
@endsection
