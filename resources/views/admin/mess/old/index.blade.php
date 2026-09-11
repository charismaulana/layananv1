@extends('layouts.app')
@section('title', 'Dashboard Mess – ' . $date->translatedFormat('d F Y'))
@section('page-title', 'Dashboard Mess')

@section('content')
@php
    $isToday = $date->isToday();
@endphp

{{-- ── 1. HEADER BANNER ── --}}
<div style="background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:14px;padding:14px 18px;margin-bottom:16px;position:relative;overflow:hidden;box-shadow:0 4px 14px rgba(0,103,56,.14);color:#ffffff">
    <div style="position:absolute;top:-25px;right:-25px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
    <div style="position:absolute;bottom:-35px;right:40px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.04)"></div>

    <div style="position:relative;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:22px">🛏️</span>
            <p style="font-size:14px;font-weight:800;color:#ffffff;margin:0">Mess — {{ $date->translatedFormat('d M Y') }}</p>
        </div>

        {{-- Pemilih Tanggal --}}
        <form method="GET" action="{{ route('admin.mess.index') }}" id="dateForm" style="display:flex;align-items:center;gap:6px;margin:0">
            @if($regionId)
                <input type="hidden" name="region" value="{{ $regionId }}">
            @endif
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
                   class="form-input"
                   style="padding:6px 10px;font-size:12px;font-weight:700;border-radius:8px;background:rgba(255,255,255,0.95);color:#1a2332;border:none;box-shadow:0 2px 6px rgba(0,0,0,0.1);max-width:145px">
        </form>
    </div>
</div>

{{-- ── 2. METRIC / KPI CARDS (Konsep Card Standar Aplikasi) ── --}}
<div class="grid-4" style="margin-bottom:16px;gap:10px">
    {{-- Total Kamar Aktif --}}
    <div class="card" style="padding:14px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <p style="font-size:10.5px;font-weight:700;color:#0369a1;text-transform:uppercase;margin:0">Total Kamar</p>
            <span style="font-size:16px">🏠</span>
        </div>
        <p style="font-size:24px;font-weight:900;color:#075985;margin:0">
            {{ $totalRooms }} <span style="font-size:12px;font-weight:600;color:#0369a1">unit</span>
        </p>
    </div>

    {{-- Total Penghuni Terdaftar --}}
    <div class="card" style="padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <p style="font-size:10.5px;font-weight:700;color:#15803d;text-transform:uppercase;margin:0">Penghuni Terdaftar</p>
            <span style="font-size:16px">👥</span>
        </div>
        <p style="font-size:24px;font-weight:900;color:#14532d;margin:0">
            {{ $totalOccupants }} <span style="font-size:12px;font-weight:600;color:#15803d">orang</span>
        </p>
    </div>

    {{-- On Duty --}}
    <div class="card" style="padding:14px;background:#ecfdf5;border:1px solid #a7f3d0;border-left:4px solid #10b981;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <p style="font-size:10.5px;font-weight:700;color:#065f46;text-transform:uppercase;margin:0">On Duty</p>
            <span style="font-size:16px">🟢</span>
        </div>
        <p style="font-size:24px;font-weight:900;color:#064e3b;margin:0">
            {{ $totalKerja }} <span style="font-size:12px;font-weight:600;color:#059669">orang</span>
        </p>
    </div>

    {{-- Off Duty --}}
    <div class="card" style="padding:14px;background:#fefce8;border:1px solid #fef08a;border-left:4px solid #d97706;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <p style="font-size:10.5px;font-weight:700;color:#854d0e;text-transform:uppercase;margin:0">Off Duty</p>
            <span style="font-size:16px">🟡</span>
        </div>
        <p style="font-size:24px;font-weight:900;color:#713f12;margin:0">
            {{ $totalLibur }} <span style="font-size:12px;font-weight:600;color:#854d0e">orang</span>
            @if($totalNone > 0)
                <span style="font-size:10.5px;font-weight:700;color:#b45309;display:block;margin-top:2px">
                    ({{ $totalNone }} belum isi roster)
                </span>
            @endif
        </p>
    </div>
</div>

{{-- ── 3. FILTER WILAYAH PILL TABS (Mobile First — Scroll Horizontal) ── --}}
<div style="margin-bottom:12px">
    <div style="display:flex;align-items:center;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:6px;scrollbar-width:none">
        <a href="{{ route('admin.mess.index', array_filter(['date' => request('date')])) }}"
           style="text-decoration:none;padding:7px 15px;border-radius:99px;font-size:12px;font-weight:{{ !$regionId ? '800' : '600' }};white-space:nowrap;transition:all .15s;{{ !$regionId ? 'background:#006738;color:#ffffff;border:1px solid #006738;box-shadow:0 2px 6px rgba(0,103,56,.25)' : 'background:#ffffff;color:#374151;border:1px solid #e2e8f0' }}">
            Semua Wilayah ({{ $totalRooms }})
        </a>
        @foreach($regions as $r)
        @php
            $isSelected = $regionId == $r->id;
            $rRoomCount = $r->rooms_count ?? $r->rooms()->where('is_active', true)->count();
        @endphp
        <a href="{{ route('admin.mess.index', array_filter(['region' => $r->id, 'date' => request('date')])) }}"
           style="text-decoration:none;padding:7px 14px;border-radius:99px;font-size:12px;font-weight:{{ $isSelected ? '800' : '600' }};white-space:nowrap;transition:all .15s;{{ $isSelected ? 'background:#006738;color:#ffffff;border:1px solid #006738;box-shadow:0 2px 6px rgba(0,103,56,.25)' : 'background:#ffffff;color:#374151;border:1px solid #e2e8f0' }}">
            📍 {{ $r->name }} ({{ $rRoomCount }})
        </a>
        @endforeach
    </div>
</div>

{{-- ── 4. INSTANT SEARCH BAR (Sangat Membantu di Mobile) ── --}}
<div style="position:relative;margin-bottom:16px">
    <input type="text" id="messSearchInput" onkeyup="filterMessCards()"
           placeholder="🔍 Cari nomor kamar, blok, atau nama pekerja..."
           class="form-input"
           style="padding:10px 14px 10px 38px;font-size:13px;border-radius:10px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
    <svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"
         style="position:absolute;left:13px;top:50%;transform:translateY(-50%);pointer-events:none">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <button type="button" id="clearSearchBtn" onclick="clearMessSearch()"
            style="display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:#f3f4f6;color:#6b7280;width:20px;height:20px;border-radius:50%;font-size:11px;cursor:pointer;line-height:1">
        ✕
    </button>
</div>

{{-- ── 5. DAFTAR KAMAR MESS (CARD KONSEP) ── --}}
<div id="messRoomsContainer">
@forelse($roomsByRegion as $regId => $regionRooms)
    @php
        $regionName = $regionRooms->first()->region?->name ?? 'Lainnya';
        $roomsByBlock = $regionRooms->groupBy('block');
    @endphp

    {{-- Section Wilayah --}}
    <div class="region-section" style="margin-bottom:24px">
        {{-- Section Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;margin-bottom:10px;border-bottom:2px solid #e8edf2">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">📍</span>
                <h3 style="font-size:14.5px;font-weight:800;color:#006738;margin:0">
                    Wilayah {{ $regionName }}
                </h3>
            </div>
            <span class="badge badge-gray" style="font-size:11px;font-weight:700">
                {{ $regionRooms->count() }} Kamar
            </span>
        </div>

        @foreach($roomsByBlock as $block => $blockRooms)
            @if($block)
            <div style="display:flex;align-items:center;gap:6px;margin:12px 0 8px">
                <span style="font-size:11px;font-weight:700;color:#4b5563;background:#f3f4f6;padding:3px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px">
                    🏢 <span>{{ $block }}</span>
                </span>
            </div>
            @endif

            {{-- Grid Kamar: 1 Kolom di Mobile, 2-3 Kolom di Desktop --}}
            <div class="mess-grid" style="display:grid;grid-template-columns:1fr;gap:12px;margin-bottom:16px">
                @foreach($blockRooms as $room)
                @php
                    $occupants = $room->users;
                    $count     = $occupants->count();
                    $roomSearchText = strtolower($room->name . ' ' . $room->block . ' ' . $regionName . ' ' . $occupants->pluck('name')->implode(' '));
                @endphp
                <div class="card room-card" data-search="{{ $roomSearchText }}"
                     style="background:#ffffff;border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between">

                    {{-- Card Header --}}
                    <div class="card-header" style="background:#fafafa;padding:12px 14px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:8px">
                        <div style="display:flex;align-items:center;gap:8px;min-width:0">
                            <span style="font-size:16px;flex-shrink:0">🛏️</span>
                            <div style="min-width:0">
                                <h4 style="font-size:14px;font-weight:800;color:#1a2332;margin:0;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $room->name }}
                                </h4>
                                @if($room->block)
                                    <span style="font-size:10.5px;color:#6b7280;font-weight:600">
                                        {{ $room->block }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Badge Kapasitas / Status Kamar --}}
                        <div style="flex-shrink:0">
                            @if($count > 0)
                                <span class="badge badge-green" style="font-size:10.5px;font-weight:700">
                                    👥 {{ $count }} Orang
                                </span>
                            @else
                                <span class="badge badge-gray" style="font-size:10.5px;color:#9ca3af">
                                    Kosong
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body: Daftar Penghuni --}}
                    <div class="card-body" style="padding:12px 14px;flex:1">
                        @if($count > 0)
                            <div style="display:flex;flex-direction:column;gap:8px">
                                @foreach($occupants as $u)
                                @php
                                    $roster = $u->rosters->first();
                                    $initials = strtoupper(substr($u->name, 0, 2));

                                    if (!$roster) {
                                        $bClass = 'badge-gray';
                                        $bText  = 'Belum Isi';
                                        $dClass = 'dot-gray';
                                    } elseif ($roster->status === 'Kerja') {
                                        $bClass = 'badge-green';
                                        $bText  = 'Di Lapangan';
                                        $dClass = 'dot-green';
                                    } elseif ($roster->status === 'Libur') {
                                        $bClass = 'badge-gold';
                                        $bText  = 'Libur';
                                        $dClass = 'dot-gold';
                                    } else {
                                        $bClass = 'badge';
                                        $bText  = $roster->status;
                                        $dClass = 'dot';
                                    }
                                @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;background:#f8fafc;border:1px solid #eef2f6;border-radius:10px">
                                    {{-- Avatar & Identitas Karyawan --}}
                                    <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1">
                                        <div style="width:28px;height:28px;border-radius:50%;background:#006738;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:800;flex-shrink:0">
                                            {{ $initials }}
                                        </div>
                                        <div style="min-width:0;flex:1">
                                            <p style="font-size:12.5px;font-weight:700;color:#1a2332;margin:0;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                                {{ $u->name }}
                                            </p>
                                            <p style="font-size:10.5px;color:#6b7280;margin:2px 0 0;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                                {{ $u->department?->name ?? ($u->company_name ?: ($u->company?->name ?? 'Karyawan')) }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Badge Status Roster --}}
                                    <div style="flex-shrink:0">
                                        @if($roster && $roster->status === 'Kerja')
                                            <span class="badge badge-green" style="font-size:10.5px;font-weight:700;padding:3px 8px">
                                                <span class="dot dot-green"></span>Masuk
                                            </span>
                                        @elseif($roster && $roster->status === 'Libur')
                                            <span class="badge badge-gold" style="font-size:10.5px;font-weight:700;padding:3px 8px">
                                                <span class="dot dot-gold"></span>Libur
                                            </span>
                                        @elseif($roster && $roster->status === 'Cuti')
                                            <span class="badge" style="background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;font-weight:700;padding:3px 8px">
                                                <span class="dot" style="background:#8b5cf6"></span>Cuti
                                            </span>
                                        @else
                                            <span class="badge badge-gray" style="font-size:10px;color:#9ca3af;padding:3px 7px" title="Belum mengisi roster pada tanggal ini">
                                                <span class="dot dot-gray"></span>Belum Isi
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Empty State di dalam Card --}}
                            <div style="padding:14px 10px;text-align:center;background:#fafafa;border:1px dashed #e2e8f0;border-radius:8px">
                                <p style="font-size:11.5px;color:#9ca3af;margin:0">
                                    Belum ada penghuni terdaftar
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer Mini: Indikator cepat --}}
                    @if($count > 0)
                    @php
                        $inField = $occupants->filter(fn($u) => $u->rosters->first()?->status === 'Kerja')->count();
                    @endphp
                    <div style="padding:7px 14px;background:#fafafa;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#6b7280">
                        <span>Status Hari Ini:</span>
                        <span style="font-weight:700;color:{{ $inField > 0 ? '#16a34a' : '#b45309' }}">
                            {{ $inField }}/{{ $count }} Sedang di Mess/Lapangan
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endforeach
    </div>
@empty
    {{-- Global Empty State --}}
    <div class="card" style="padding:40px 20px;text-align:center;border-radius:14px">
        <span style="font-size:36px;display:block;margin-bottom:10px">🛏️</span>
        <h4 style="font-size:15px;font-weight:700;color:#1a2332;margin:0 0 6px">Belum Ada Data Kamar</h4>
        <p style="font-size:12.5px;color:#6b7280;margin:0 0 16px">Kamar mess belum ditambahkan untuk wilayah ini.</p>
        <a href="{{ route('admin.master.index', ['tab' => 'rooms']) }}" class="btn btn-primary btn-sm">
            + Tambah Kamar di Master Data
        </a>
    </div>
@endforelse
</div>

{{-- Empty Search Result --}}
<div id="noSearchResult" style="display:none" class="card">
    <div class="empty-state" style="padding:32px 16px">
        <span style="font-size:32px;display:block;margin-bottom:8px">🔍</span>
        <p style="font-size:13px;font-weight:600;color:#374151;margin:0">Tidak ditemukan kamar atau penghuni yang cocok.</p>
        <p style="font-size:11.5px;color:#9ca3af;margin:4px 0 12px">Coba gunakan kata kunci pencarian yang lain.</p>
        <button type="button" onclick="clearMessSearch()" class="btn btn-secondary btn-sm">
            Reset Pencarian
        </button>
    </div>
</div>

{{-- Responsive CSS untuk Grid Desktop --}}
<style>
@media(min-width:640px) {
    .mess-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 14px !important;
    }
}
@media(min-width:1024px) {
    .mess-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 16px !important;
    }
}
@media(min-width:1440px) {
    .mess-grid {
        grid-template-columns: repeat(4, 1fr) !important;
    }
}
</style>

@push('scripts')
<script>
function filterMessCards() {
    var input = document.getElementById('messSearchInput');
    var query = input.value.toLowerCase().trim();
    var clearBtn = document.getElementById('clearSearchBtn');
    var cards = document.querySelectorAll('.room-card');
    var sections = document.querySelectorAll('.region-section');
    var noResult = document.getElementById('noSearchResult');

    clearBtn.style.display = query ? 'block' : 'none';

    var totalVisible = 0;

    cards.forEach(function(card) {
        var text = card.getAttribute('data-search') || '';
        if (!query || text.indexOf(query) !== -1) {
            card.style.display = 'flex';
            totalVisible++;
        } else {
            card.style.display = 'none';
        }
    });

    // Sembunyikan section wilayah jika tidak ada kamar yang cocok
    sections.forEach(function(sec) {
        var visibleInSec = sec.querySelectorAll('.room-card[style*="display: flex"]').length;
        sec.style.display = (visibleInSec > 0 || !query) ? 'block' : 'none';
    });

    if (noResult) {
        noResult.style.display = (totalVisible === 0 && query) ? 'block' : 'none';
    }
}

function clearMessSearch() {
    var input = document.getElementById('messSearchInput');
    if (input) {
        input.value = '';
        filterMessCards();
        input.focus();
    }
}
</script>
@endpush
@endsection
