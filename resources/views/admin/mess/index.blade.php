@extends('layouts.app')
@section('title', 'Dashboard Mess – ' . $date->translatedFormat('d F Y'))
@section('page-title', 'Dashboard Mess')

@section('content')
@php
    $isToday = $date->isToday();
@endphp

{{-- ── 1. HEADER DATE FILTER (Sesuai Menu Lain) ── --}}
<div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-bottom:16px">
    <form method="GET" action="{{ route('admin.mess.index') }}" id="dateForm" style="display:flex;align-items:center;margin:0">
        @if($regionId)
            <input type="hidden" name="region" value="{{ $regionId }}">
        @endif
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
               class="form-input"
               style="font-weight:600;font-size:13px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;padding:7px 12px;color:#1e293b;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
    </form>
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

{{-- ── 4. INSTANT SEARCH BAR (Lebar kolom rapi & teratur) ── --}}
<div style="position:relative;margin-bottom:16px;max-width:380px;width:100%">
    <input type="text" id="messSearchInput" onkeyup="filterMessRows()"
           placeholder="Cari blok, kamar, atau nama pekerja..."
           class="form-input"
           style="padding:10px 34px 10px 38px;font-size:13px;border-radius:10px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.03);width:100%">
    <svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"
         style="position:absolute;left:13px;top:50%;transform:translateY(-50%);pointer-events:none">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <button type="button" id="clearSearchBtn" onclick="clearMessSearch()"
            style="display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:#f3f4f6;color:#6b7280;width:20px;height:20px;border-radius:50%;font-size:11px;cursor:pointer;line-height:1">
        ✕
    </button>
</div>

{{-- ── 5. DAFTAR KAMAR MESS (TABLE VIEW) ── --}}
<div id="messRoomsContainer">
@forelse($roomsByRegion as $regId => $regionRooms)
    @php
        $regionName      = $regionRooms->first()->region?->name ?? 'Lainnya';
        $regionOccupants = $regionRooms->sum(fn($r) => $r->users->count());
        $regionOnDuty    = $regionRooms->sum(fn($r) => $r->users->filter(fn($u) => $u->rosters->first()?->status === 'Kerja')->count());
    @endphp

    {{-- Section Wilayah --}}
    <div class="region-section" style="margin-bottom:24px">
        {{-- Section Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 2px;margin-bottom:10px;border-bottom:2px solid #e8edf2;flex-wrap:wrap;gap:8px">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">📍</span>
                <h3 style="font-size:14.5px;font-weight:800;color:#006738;margin:0">
                    Wilayah {{ $regionName }}
                </h3>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <span class="badge badge-gray" style="font-size:11px;font-weight:700">
                    🏠 {{ $regionRooms->count() }} Kamar
                </span>
                <span class="badge badge-green" style="font-size:11px;font-weight:700">
                    👥 {{ $regionOccupants }} Penghuni ({{ $regionOnDuty }} On Duty)
                </span>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="card" style="overflow:hidden;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.03);margin-bottom:16px">
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                <table class="tbl mess-table" style="width:100%;margin:0">
                    <thead>
                        <tr>
                            <th style="width:45px;text-align:center">No</th>
                            <th style="min-width:120px">Blok & Kamar</th>
                            <th style="min-width:200px">Penghuni Terdaftar</th>
                            <th style="min-width:140px">Dept.</th>
                            <th style="min-width:140px">Roster Hari Ini</th>
                            <th style="min-width:115px;text-align:center">Status Kamar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regionRooms as $room)
                        @php
                            $occupants = $room->users;
                            $count     = $occupants->count();
                            $inField   = $occupants->filter(fn($u) => $u->rosters->first()?->status === 'Kerja')->count();
                            $roomSearchText = strtolower(($room->block ?? '') . ' ' . $room->name . ' ' . $regionName . ' ' . $occupants->pluck('name')->implode(' ') . ' ' . $occupants->map(fn($u) => $u->department?->name ?? ($u->company_name ?: ($u->company?->name ?? '')))->implode(' '));
                        @endphp
                        <tr class="room-row" data-search="{{ $roomSearchText }}">
                            {{-- No --}}
                            <td style="text-align:center;font-weight:700;color:#64748b;font-size:12px">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Blok & Kamar --}}
                            <td>
                                <div>
                                    <p style="font-weight:800;font-size:13.5px;color:#0f172a;margin:0;line-height:1.2">
                                        {{ $room->block ?: '-' }}
                                    </p>
                                    <span style="font-size:11.5px;font-weight:600;color:#64748b;display:inline-block;margin-top:2px">
                                        {{ $room->name }}
                                    </span>
                                </div>
                            </td>

                            {{-- Penghuni Terdaftar (Tanpa logo/avatar, hanya isi nama saja) --}}
                            <td>
                                @if($count > 0)
                                    <div style="display:flex;flex-direction:column;gap:6px">
                                        @foreach($occupants as $u)
                                        <div style="height:24px;display:flex;align-items:center">
                                            <span style="font-weight:700;font-size:12.5px;color:#1e293b;white-space:nowrap">
                                                {{ $u->name }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:12px;color:#94a3b8;font-style:italic">Belum ada penghuni</span>
                                @endif
                            </td>

                            {{-- Dept. --}}
                            <td>
                                @if($count > 0)
                                    <div style="display:flex;flex-direction:column;gap:6px">
                                        @foreach($occupants as $u)
                                        <div style="height:24px;display:flex;align-items:center">
                                            <span style="font-size:12px;color:#475569;font-weight:600;white-space:nowrap">
                                                {{ $u->department?->name ?? ($u->company_name ?: ($u->company?->name ?? '-')) }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:12px;color:#cbd5e1">-</span>
                                @endif
                            </td>

                            {{-- Roster Hari Ini --}}
                            <td>
                                @if($count > 0)
                                    <div style="display:flex;flex-direction:column;gap:6px">
                                        @foreach($occupants as $u)
                                        @php $roster = $u->rosters->first(); @endphp
                                        <div style="height:24px;display:flex;align-items:center">
                                            @if($roster && $roster->status === 'Kerja')
                                                <span class="badge badge-green" style="font-size:11px;font-weight:700;padding:2px 8px">
                                                    <span class="dot dot-green"></span>Masuk (On Duty)
                                                </span>
                                            @elseif($roster && $roster->status === 'Libur')
                                                <span class="badge badge-gold" style="font-size:11px;font-weight:700;padding:2px 8px">
                                                    <span class="dot dot-gold"></span>Libur (Off Duty)
                                                </span>
                                            @elseif($roster && $roster->status === 'Cuti')
                                                <span class="badge" style="background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;font-weight:700;padding:2px 8px">
                                                    <span class="dot" style="background:#8b5cf6"></span>Cuti
                                                </span>
                                            @else
                                                <span class="badge badge-gray" style="font-size:10.5px;color:#9ca3af;padding:2px 7px" title="Belum mengisi roster pada tanggal ini">
                                                    <span class="dot dot-gray"></span>Belum Isi
                                                </span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:12px;color:#cbd5e1">-</span>
                                @endif
                            </td>

                            {{-- Kapasitas & Status Kamar --}}
                            <td style="text-align:center">
                                @if($count > 0)
                                    <span class="badge badge-green" style="font-size:11px;font-weight:700;padding:3px 9px">
                                        👥 {{ $count }} Orang
                                    </span>
                                    <span style="font-size:10.5px;font-weight:700;display:block;margin-top:3px;color:{{ $inField > 0 ? '#16a34a' : '#b45309' }};white-space:nowrap">
                                        {{ $inField }}/{{ $count }} On Duty
                                    </span>
                                @else
                                    <span class="badge badge-gray" style="font-size:10.5px;color:#94a3b8;padding:2px 8px">
                                        Kosong
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@empty
    {{-- Global Empty State (Tanpa logo kamar tidur) --}}
    <div class="card" style="padding:40px 20px;text-align:center;border-radius:14px">
        <span style="font-size:36px;display:block;margin-bottom:10px">🏠</span>
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

<div style="height:30px"></div>

{{-- Styling Khusus Tabel Mess --}}
<style>
.mess-table th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 11px 14px;
    background: #f8fafc;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.mess-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.mess-table tr:last-child td {
    border-bottom: none;
}
.mess-table tr:hover td {
    background: #f8fafc;
}
</style>

@push('scripts')
<script>
function filterMessRows() {
    var input = document.getElementById('messSearchInput');
    var query = input.value.toLowerCase().trim();
    var clearBtn = document.getElementById('clearSearchBtn');
    var rows = document.querySelectorAll('.room-row');
    var sections = document.querySelectorAll('.region-section');
    var noResult = document.getElementById('noSearchResult');

    clearBtn.style.display = query ? 'block' : 'none';

    var totalVisible = 0;

    rows.forEach(function(row) {
        var text = row.getAttribute('data-search') || '';
        if (!query || text.indexOf(query) !== -1) {
            row.style.display = '';
            totalVisible++;
        } else {
            row.style.display = 'none';
        }
    });

    // Sembunyikan section wilayah jika tidak ada kamar yang cocok
    sections.forEach(function(sec) {
        var visibleRows = sec.querySelectorAll('.room-row:not([style*="display: none"])');
        sec.style.display = (visibleRows.length > 0 || !query) ? 'block' : 'none';
    });

    if (noResult) {
        noResult.style.display = (totalVisible === 0 && query) ? 'block' : 'none';
    }
}

function clearMessSearch() {
    var input = document.getElementById('messSearchInput');
    if (input) {
        input.value = '';
        filterMessRows();
        input.focus();
    }
}
</script>
@endpush
@endsection
