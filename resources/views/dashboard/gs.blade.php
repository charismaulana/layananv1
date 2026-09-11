@extends('layouts.app')
@section('title', 'Dashboard GS')
@section('page-title', 'Dashboard GS')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <a href="{{ route('admin.mess.index') }}" class="btn btn-outline btn-sm" style="background:#fff;font-weight:700;display:inline-flex;align-items:center;gap:6px">
        <span>🛏️</span> Dashboard Mess
    </a>
    <form method="GET" style="display:flex;align-items:center;margin:0">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" class="form-input" style="font-weight:600">
    </form>
</div>

{{-- ── 1. KPI Metric Cards ── --}}
<div class="grid-4" style="margin-bottom:24px;gap:14px">
    {{-- Total Karyawan Aktif --}}
    <div class="card" style="padding:18px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <p style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;margin:0">Total Karyawan Aktif</p>
            <span style="font-size:16px">👥</span>
        </div>
        <p style="font-size:28px;font-weight:800;color:#14532d;margin:0">{{ $totalUsers }} <span style="font-size:13px;font-weight:500;color:#166534">karyawan</span></p>
    </div>

    {{-- Kelengkapan Roster --}}
    <div class="card" style="padding:18px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <p style="font-size:11px;font-weight:700;color:#0369a1;text-transform:uppercase;margin:0">Kelengkapan Roster</p>
                <span style="font-size:16px">📅</span>
            </div>
            <p style="font-size:28px;font-weight:800;color:#075985;margin:0">
                {{ $rosterCompletion }}% <span style="font-size:12px;font-weight:600;color:#0284c7">({{ $rosterToday }}/{{ $totalActive }})</span>
            </p>
        </div>
        <div style="margin-top:10px;padding-top:8px;border-top:1px dashed #bae6fd;display:flex;align-items:center;justify-content:space-between">
            @if($usersWithoutRoster->isNotEmpty())
            <a href="javascript:void(0)" onclick="openMissingRosterModal()" style="font-size:11.5px;font-weight:700;color:#0284c7;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
                Lihat {{ $usersWithoutRoster->count() }} Belum Isi →
            </a>
            @else
            <span style="font-size:11px;font-weight:700;color:#16a34a">✓ Semua Lengkap</span>
            @endif
        </div>
    </div>

    {{-- Menunggu Persetujuan --}}
    @php $totalPending = $pendingMovements->count() + $pendingOutside->count() + $pendingKontraktor->count(); @endphp
    <div class="card" style="padding:18px;background:#fff7ed;border:1px solid #fed7aa;border-left:4px solid #ea580c;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <p style="font-size:11px;font-weight:700;color:#c2410c;text-transform:uppercase;margin:0">Menunggu Persetujuan</p>
            <span style="font-size:16px">⏳</span>
        </div>
        <p style="font-size:28px;font-weight:800;color:#9a3412;margin:0">{{ $totalPending }} <span style="font-size:13px;font-weight:500;color:#c2410c">berkas</span></p>
    </div>

    {{-- Kepuasan Katering --}}
    <div class="card" style="padding:18px;background:#fefce8;border:1px solid #fef08a;border-left:4px solid #d97706;border-radius:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <p style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase;margin:0">Kepuasan Katering</p>
            <span style="font-size:16px">⭐</span>
        </div>
        <p style="font-size:28px;font-weight:800;color:#854d0e;margin:0">
            ★ {{ $avgRating ? number_format($avgRating, 1) : '-' }} <span style="font-size:13px;font-weight:500;color:#b45309">/ 5.0</span>
        </p>
    </div>
</div>

{{-- ── 2. Predicted POB Table ── --}}
<div class="card" style="margin-bottom:24px;border-left:4px solid #006738">
    <div class="card-header" style="background:#fafafa;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">📊</span>
            <h3 style="font-size:14px;font-weight:700;color:#1a2332;margin:0">Predicted POB (Termasuk Karyawan & Visitor)</h3>
        </div>
        <span style="font-size:12px;font-weight:600;color:#6b7280">{{ $date->translatedFormat('l, d F Y') }}</span>
    </div>
    <div style="overflow-x:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th style="min-width:140px">Wilayah Dapur</th>
                    @foreach($pobSummary['meal_types'] as $mt)
                    <th style="text-align:center;min-width:100px">{{ $mt->name }}</th>
                    @endforeach
                    <th style="text-align:center;min-width:110px;background:#f3f4f6">Total Porsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pobSummary['rows'] as $row)
                <tr>
                    <td style="font-weight:700;color:#1a2332">📍 {{ $row['region']->name }}</td>
                    @foreach($pobSummary['meal_types'] as $mt)
                    <td style="text-align:center;font-weight:600;color:#374151">
                        {{ $row['totals'][$mt->slug] ?? 0 }}
                    </td>
                    @endforeach
                    <td style="text-align:center;font-weight:800;color:var(--g);background:#f8fafc">
                        {{ $row['grand_total'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="border-top:2px solid #e5e7eb;background:#f9fafb">
                <tr>
                    <td style="font-weight:800;color:#1a2332">TOTAL SEMUA WILAYAH</td>
                    @foreach($pobSummary['meal_types'] as $mt)
                    <td style="text-align:center;font-weight:800;color:#1a2332">
                        {{ collect($pobSummary['rows'])->sum(fn($r) => $r['totals'][$mt->slug] ?? 0) }}
                    </td>
                    @endforeach
                    <td style="text-align:center;font-weight:900;color:var(--g);font-size:15px;background:#e8f5ee">
                        {{ collect($pobSummary['rows'])->sum('grand_total') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ── 3. Pending Approvals Section ── --}}
<div class="grid-2" style="gap:20px;margin-bottom:24px">

    {{-- Perpindahan Lokasi Pending --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <h3 style="font-size:14.5px;font-weight:800;color:#1a2332;margin:0">Permohonan Movement</h3>
                <span class="badge {{ $pendingMovements->count() ? 'badge-gold' : 'badge-gray' }}" style="font-weight:700;font-size:11px">{{ $pendingMovements->count() }}</span>
            </div>
            <a href="{{ route('movement.index') }}" style="font-size:12px;font-weight:700;color:#006738;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
                Riwayat →
            </a>
        </div>
        <div style="padding:0">
            @forelse($pendingMovements as $mv)
            <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
                <div>
                    <p style="font-size:13.5px;font-weight:800;color:#0f172a;margin:0">
                        {{ $mv->fromRegion->name }} <span style="color:#006738">➔</span> {{ $mv->toRegion->name }}
                    </p>
                    <p style="font-size:11.5px;color:#64748b;font-weight:600;margin:3px 0 0">
                        {{ $mv->movement_date->translatedFormat('d M Y') }} · {{ $mv->people->count() }} orang · {{ $mv->requester->name }}
                    </p>
                </div>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('movement.show', $mv) }}" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:12px;font-weight:600">Tinjau</a>
                    <form method="POST" action="{{ route('movement.approve', $mv) }}" style="margin:0">
                        @csrf
                        <button class="btn btn-primary btn-sm" style="background:#006738;padding:6px 12px;font-size:12px;font-weight:700" onclick="return confirm('Setujui permohonan movement?')">✓ Setujui</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#9ca3af;font-size:12px">
                Tidak ada permohonan movement yang menunggu persetujuan.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Outside Meal Pending --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <h3 style="font-size:14.5px;font-weight:800;color:#1a2332;margin:0">Permohonan Outside Meal</h3>
                <span class="badge {{ $pendingOutside->count() ? 'badge-gold' : 'badge-gray' }}" style="font-weight:700;font-size:11px">{{ $pendingOutside->count() }}</span>
            </div>
            <a href="{{ route('outside-meal.index') }}" style="font-size:12px;font-weight:700;color:#006738;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
                Riwayat →
            </a>
        </div>
        <div style="padding:0">
            @forelse($pendingOutside as $om)
            <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
                <div>
                    <p style="font-size:13.5px;font-weight:800;color:#0f172a;margin:0">
                        Outside Meal: {{ $om->mealType->name }}
                    </p>
                    <p style="font-size:11.5px;color:#64748b;font-weight:600;margin:3px 0 0">
                        {{ $om->meal_date->translatedFormat('d M Y') }} · {{ $om->people->count() }} orang · {{ $om->requester->name }}
                    </p>
                </div>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('outside-meal.show', $om) }}" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:12px;font-weight:600">Tinjau</a>
                    <form method="POST" action="{{ route('outside-meal.approve', $om) }}" style="margin:0">
                        @csrf
                        <button class="btn btn-primary btn-sm" style="background:#006738;padding:6px 12px;font-size:12px;font-weight:700" onclick="return confirm('Setujui outside meal?')">✓ Setujui</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#9ca3af;font-size:12px">
                Tidak ada permohonan outside meal yang menunggu persetujuan.
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ── 4. Kontraktor Pending Approval ── --}}
@if($pendingKontraktor->isNotEmpty())
<div class="card" style="margin-bottom:24px;border-left:4px solid #d97706">
    <div class="card-header" style="background:#fffbeb;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">👷</span>
            <h3 style="color:#92400e;margin:0;font-size:14px;font-weight:700">Pendaftaran Akun Kontraktor Menunggu Approval ({{ $pendingKontraktor->count() }})</h3>
        </div>
        <a href="{{ route('admin.users.index', ['registration_status' => 'pending_approval']) }}" class="btn btn-secondary btn-sm">Lihat Semua di Kelola Pengguna</a>
    </div>
    <div style="padding:0">
        @foreach($pendingKontraktor as $k)
        <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:34px;height:34px;border-radius:50%;background:#e8f5ee;color:#006738;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700">
                    {{ strtoupper(substr($k->name, 0, 2)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#1a2332;margin:0">{{ $k->name }}</p>
                    <p style="font-size:11px;color:#6b7280;margin:1px 0 0">
                        {{ $k->email }} · Perusahaan: {{ $k->company?->name ?? 'Mitra Kerja' }} · Homebase: {{ $k->homebaseRegion?->name ?? '-' }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.approve-contractor', $k) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">✓ Setujui Akun</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── 5. CARD PALING BAWAH: Pendaftaran Visitor / Tamu Mess Hall (Khusus Role GS) ── --}}
<div class="card" style="margin-bottom:24px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:20px">👥</span>
            <div>
                <h3 style="font-size:14.5px;font-weight:800;color:#1a2332;margin:0">
                    Pendaftaran Visitor / Tamu Mess Hall
                </h3>
                <p style="font-size:11.5px;color:#64748b;margin:2px 0 0">
                    Untuk mendaftarkan porsi makan visitor ad-hoc.
                </p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span style="font-size:11.5px;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:4px 8px;border-radius:6px;font-weight:700">
                Cut-Off : H-1 19.00 WIB
            </span>
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateVisitorModal()" style="background:#16a34a;border-color:#16a34a;font-weight:700;padding:7px 16px">
                + Tambah
            </button>
        </div>
    </div>

    {{-- Tabel: No | Tanggal (Start - End) | Nama/Rombongan | Instansi | Lokasi (Wilayah) | Waktu Makan | Catatan | Aksi --}}
    <div style="overflow-x:auto;background:#fff">
        <table class="tbl" style="margin:0">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center">No</th>
                    <th style="min-width:110px">Tanggal</th>
                    <th>Nama/Rombongan</th>
                    <th>Instansi</th>
                    <th>Lokasi</th>
                    <th style="text-align:center">Waktu Makan</th>
                    <th>Catatan</th>
                    <th style="text-align:center;width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitorMeals as $idx => $v)
                <tr>
                    <td style="text-align:center;font-weight:700;color:#64748b">{{ $idx + 1 }}</td>
                    <td style="font-size:12px;font-weight:700;color:#1e293b;line-height:1.35">
                        @if($v->group_start_date && $v->group_end_date && $v->group_start_date !== $v->group_end_date)
                            <div>{{ \Carbon\Carbon::parse($v->group_start_date)->format('d/m/Y') }}</div>
                            <div>s/d {{ \Carbon\Carbon::parse($v->group_end_date)->format('d/m/Y') }}</div>
                        @else
                            <div>{{ $v->meal_date->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;color:#1a2332;font-size:13px">
                            {{ $v->visitor_name }}
                            @if($v->pax_count > 1)
                            <span style="color:#166534;font-size:11.5px">({{ $v->pax_count }} Orang)</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size:12px;font-weight:600;color:#334155">{{ $v->institution ?: '-' }}</td>
                    <td>
                        <span class="badge badge-green" style="font-size:11.5px">
                            {{ $v->region?->name ?? $v->mealLocation?->region?->name ?? 'Ramba' }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap">
                            @if($v->has_breakfast)<span class="badge badge-gold" style="font-size:10px">B'fast</span>@endif
                            @if($v->has_lunch)<span class="badge badge-blue" style="font-size:10px">Lunch</span>@endif
                            @if($v->has_dinner)<span class="badge" style="background:#ede9fe;color:#6d28d9;font-size:10px">Dinner</span>@endif
                            @if($v->has_supper)<span class="badge" style="background:#fce7f3;color:#be185d;font-size:10px">Supper</span>@endif
                        </div>
                        <div style="font-size:10.5px;color:#166534;font-weight:700;margin-top:2px">
                            {{ $v->pax_count }} pax
                        </div>
                    </td>
                    <td style="font-size:11.5px;color:#64748b">{{ $v->notes ?: '-' }}</td>
                    <td style="text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center;align-items:center">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="openEditVisitorModal({{ json_encode($v) }})"
                                    style="padding:4px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd" title="Edit Visitor">
                                ✏️ Edit
                            </button>
                            <form method="POST" action="{{ route('visitor-meals.destroy', $v) }}" onsubmit="return confirm('Hapus data visitor {{ $v->visitor_name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color:#ef4444;padding:4px 8px;font-size:11px;border-color:#fca5a5" title="Hapus Visitor">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:24px;color:#94a3b8;font-size:12.5px">
                        Belum ada visitor/tamu yang didaftarkan untuk tanggal <strong>{{ $date->translatedFormat('d F Y') }}</strong>. Klik tombol <strong>+ Tambah</strong> di atas untuk mendaftarkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Modal Form Tambah Visitor (Support Date Range & Per-Day Meal Detail) ── --}}
<div id="createVisitorModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:16px;overflow-y:auto">
    <div style="background:#fff;border-radius:14px;max-width:580px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 25px -5px rgba(0,0,0,.2),0 10px 10px -5px rgba(0,0,0,.04);overflow:hidden;border:1px solid #e2e8f0;margin:auto">
        <div style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:20px">👥</span>
                <h3 style="font-size:15px;font-weight:800;color:#14532d;margin:0">Tambah Porsi Visitor / Tamu</h3>
            </div>
            <button type="button" onclick="closeCreateVisitorModal()" style="background:transparent;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1">&times;</button>
        </div>

        <form method="POST" action="{{ route('visitor-meals.store') }}" style="padding:20px;overflow-y:auto;flex:1;min-height:0">
            @csrf

            <div style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Nama Tamu / Rombongan <span style="color:#ef4444">*</span></label>
                <input type="text" name="visitor_name" required placeholder="Contoh: Bpk. Rahmat (Auditor SKK Migas)" class="form-input" style="width:100%">
            </div>

            <div class="grid-2" style="gap:12px;margin-bottom:14px">
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Instansi / Perusahaan</label>
                    <input type="text" name="institution" placeholder="Contoh: SKK Migas / Vendor" class="form-input" style="width:100%">
                </div>
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Jumlah Porsi (Orang) <span style="color:#ef4444">*</span></label>
                    <input type="number" name="pax_count" id="createPaxCount" value="1" min="1" max="100" required class="form-input" style="width:100%">
                </div>
            </div>

            {{-- Range Tanggal --}}
            <div class="grid-2" style="gap:12px;margin-bottom:14px">
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Tanggal Mulai <span style="color:#ef4444">*</span></label>
                    <input type="date" name="start_date" id="createStartDate" value="{{ $date->format('Y-m-d') }}" required class="form-input" style="width:100%" onchange="updateDateRangeTable()">
                </div>
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Tanggal Selesai <span style="color:#ef4444">*</span></label>
                    <input type="date" name="end_date" id="createEndDate" value="{{ $date->format('Y-m-d') }}" required class="form-input" style="width:100%" onchange="updateDateRangeTable()">
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Lokasi Mess Hall Makan <span style="color:#ef4444">*</span></label>
                <select name="meal_location_id" required class="form-input" style="width:100%">
                    @foreach($messHallLocations as $loc)
                    <option value="{{ $loc->id }}">
                        {{ $loc->name }} (Wilayah {{ $loc->region?->name }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Pilihan Waktu Makan Default / Global --}}
            <div id="defaultMealsSection" style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:6px">Waktu Makan Tamu <span style="color:#ef4444">*</span></label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0">
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_breakfast" value="1" onchange="syncDayMealChecks()"> B'fast
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_lunch" value="1" checked onchange="syncDayMealChecks()"> Lunch
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_dinner" value="1" onchange="syncDayMealChecks()"> Dinner
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_supper" value="1" onchange="syncDayMealChecks()"> Supper
                    </label>
                </div>
            </div>

            {{-- Detail Pilihan Makan Per Hari (Muncul Jika Range > 1 Hari) --}}
            <div id="multiDayDetailsContainer" style="display:none;margin-bottom:14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#166534;margin:0">
                        📅 Detail Waktu Makan Per Hari:
                    </label>
                    <span style="font-size:11px;color:#64748b">Sesuaikan waktu makan untuk tiap tanggal</span>
                </div>
                <div id="daysListTable" style="max-height:180px;overflow-y:auto;border:1px solid #cbd5e1;border-radius:8px;background:#fff">
                    {{-- Generated dynamically via JS --}}
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Catatan / Keperluan (Opsional)</label>
                <input type="text" name="notes" placeholder="Contoh: Tamu VIP Manajemen / Kunjungan Lapangan" class="form-input" style="width:100%">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:14px;position:sticky;bottom:0;background:#fff;z-index:5">
                <button type="button" onclick="closeCreateVisitorModal()" class="btn btn-secondary" style="font-weight:600">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;font-weight:700;padding:8px 20px">
                    Simpan & Daftarkan Visitor
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Form Edit Visitor (Support Date Range & Per-Day Detail) ── --}}
<div id="editVisitorModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:16px;overflow-y:auto">
    <div style="background:#fff;border-radius:14px;max-width:580px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 25px -5px rgba(0,0,0,.2),0 10px 10px -5px rgba(0,0,0,.04);overflow:hidden;border:1px solid #e2e8f0;margin:auto">
        <div style="background:#eff6ff;border-bottom:1px solid #bfdbfe;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:20px">✏️</span>
                <h3 style="font-size:15px;font-weight:800;color:#1e3a8a;margin:0">Edit Porsi Visitor / Tamu</h3>
            </div>
            <button type="button" onclick="closeEditVisitorModal()" style="background:transparent;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1">&times;</button>
        </div>

        <form id="editVisitorForm" method="POST" action="" style="padding:20px;overflow-y:auto;flex:1;min-height:0">
            @csrf
            @method('PUT')

            <div style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Nama Tamu / Rombongan <span style="color:#ef4444">*</span></label>
                <input type="text" name="visitor_name" id="editVisitorName" required class="form-input" style="width:100%">
            </div>

            <div class="grid-2" style="gap:12px;margin-bottom:14px">
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Instansi / Perusahaan</label>
                    <input type="text" name="institution" id="editInstitution" class="form-input" style="width:100%">
                </div>
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Jumlah Porsi (Orang) <span style="color:#ef4444">*</span></label>
                    <input type="number" name="pax_count" id="editPaxCount" min="1" max="100" required class="form-input" style="width:100%">
                </div>
            </div>

            {{-- Range Tanggal Edit --}}
            <div class="grid-2" style="gap:12px;margin-bottom:14px">
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Tanggal Mulai <span style="color:#ef4444">*</span></label>
                    <input type="date" name="start_date" id="editStartDate" required class="form-input" style="width:100%" onchange="updateEditDateRangeTable()">
                </div>
                <div>
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Tanggal Selesai <span style="color:#ef4444">*</span></label>
                    <input type="date" name="end_date" id="editEndDate" required class="form-input" style="width:100%" onchange="updateEditDateRangeTable()">
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Lokasi Mess Hall <span style="color:#ef4444">*</span></label>
                <select name="meal_location_id" id="editMealLocationId" required class="form-input" style="width:100%">
                    @foreach($messHallLocations as $loc)
                    <option value="{{ $loc->id }}">
                        {{ $loc->name }} (Wilayah {{ $loc->region?->name }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div id="editDefaultMealsSection" style="margin-bottom:14px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:6px">Waktu Makan Tamu <span style="color:#ef4444">*</span></label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0">
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_breakfast" id="editHasBf" value="1" onchange="syncEditDayMealChecks()"> B'fast
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_lunch" id="editHasLu" value="1" onchange="syncEditDayMealChecks()"> Lunch
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_dinner" id="editHasDi" value="1" onchange="syncEditDayMealChecks()"> Dinner
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer">
                        <input type="checkbox" name="has_supper" id="editHasSu" value="1" onchange="syncEditDayMealChecks()"> Supper
                    </label>
                </div>
            </div>

            {{-- Detail Pilihan Makan Per Hari untuk Edit --}}
            <div id="editMultiDayDetailsContainer" style="display:none;margin-bottom:14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e3a8a;margin:0">
                        📅 Detail Waktu Makan Per Hari:
                    </label>
                    <span style="font-size:11px;color:#64748b">Sesuaikan waktu makan untuk tiap tanggal</span>
                </div>
                <div id="editDaysListTable" style="max-height:180px;overflow-y:auto;border:1px solid #cbd5e1;border-radius:8px;background:#fff">
                    {{-- Generated dynamically via JS --}}
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label" style="font-weight:700;font-size:12.5px;color:#1e293b">Catatan / Keperluan (Opsional)</label>
                <input type="text" name="notes" id="editNotes" class="form-input" style="width:100%">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:14px;position:sticky;bottom:0;background:#fff;z-index:5">
                <button type="button" onclick="closeEditVisitorModal()" class="btn btn-secondary" style="font-weight:600">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background:#0284c7;border-color:#0284c7;font-weight:700;padding:8px 20px">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: Daftar Pekerja yang Belum Mengisi Roster ── --}}
<div id="missingRosterModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:16px" onclick="if(event.target===this) closeMissingRosterModal()">
    <div style="background:#fff;border-radius:16px;max-width:560px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 40px rgba(0,0,0,.2);overflow:hidden;animation:fadeIn .2s ease-out">
        <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
            <div>
                <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Pekerja Belum Mengisi Roster</h3>
                <p style="font-size:11.5px;color:#64748b;margin:2px 0 0">
                    Tanggal: <strong>{{ $date->translatedFormat('l, d F Y') }}</strong> · Total: <strong>{{ $usersWithoutRoster->count() }} orang</strong>
                </p>
            </div>
            <button type="button" onclick="closeMissingRosterModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1;padding:4px">✕</button>
        </div>

        {{-- Search filter in modal --}}
        <div style="padding:12px 18px;border-bottom:1px solid #f1f5f9;background:#fff">
            <input type="text" id="searchMissingRoster" placeholder="🔍 Cari nama, NIP, atau departemen..."
                   class="form-input" style="font-size:12.5px;padding:8px 12px" oninput="filterMissingRosterList()">
        </div>

        {{-- User List --}}
        <div style="padding:0;overflow-y:auto;flex:1" id="missingRosterList">
            @forelse($usersWithoutRoster as $u)
            <div class="missing-user-row"
                 data-name="{{ strtolower($u->name) }}"
                 data-nip="{{ strtolower($u->nomor_pegawai ?? '') }}"
                 data-dept="{{ strtolower($u->department?->name ?? '') }}"
                 style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f1f5f9">
                <div style="width:34px;height:34px;border-radius:50%;background:#e0f2fe;color:#0369a1;display:flex;align-items:center;justify-content:center;font-size:11.5px;font-weight:800;flex-shrink:0">
                    {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0">{{ $u->name }}</p>
                    <p style="font-size:11px;color:#64748b;margin:2px 0 0">
                        {{ $u->nomor_pegawai ? 'NIP: '.$u->nomor_pegawai.' · ' : '' }}
                        {{ $u->department?->name ?? $u->role?->name }}
                        @if($u->homebaseRegion) · Homebase: {{ $u->homebaseRegion->name }} @endif
                    </p>
                </div>
                <span class="badge badge-gold" style="font-size:10px;font-weight:700;flex-shrink:0">Belum Ada Roster</span>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#16a34a;font-weight:700;font-size:13px">
                🎉 Luar biasa! Seluruh karyawan aktif telah mengisi roster untuk tanggal ini.
            </div>
            @endforelse
        </div>

        <div style="padding:12px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap">
            {{-- Download Excel --}}
            <a href="{{ route('admin.export.download', ['type' => 'missing-roster']) }}?date={{ $date->format('Y-m-d') }}"
               class="btn btn-sm"
               style="background:#006738;color:#fff;border:none;padding:7px 14px;font-weight:700;font-size:12px;display:inline-flex;align-items:center;gap:5px;border-radius:8px;text-decoration:none"
               title="Download daftar sebagai file Excel">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Excel
            </a>
            <button type="button" onclick="closeMissingRosterModal()" class="btn btn-secondary btn-sm" style="padding:7px 18px;font-weight:700">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openMissingRosterModal() {
    document.getElementById('missingRosterModal').style.display = 'flex';
}
function closeMissingRosterModal() {
    document.getElementById('missingRosterModal').style.display = 'none';
}
function filterMissingRosterList() {
    var query = document.getElementById('searchMissingRoster').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.missing-user-row');
    rows.forEach(function(row) {
        var name = row.getAttribute('data-name') || '';
        var nip = row.getAttribute('data-nip') || '';
        var dept = row.getAttribute('data-dept') || '';
        if (!query || name.includes(query) || nip.includes(query) || dept.includes(query)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}

function openCreateVisitorModal() {
    document.getElementById('createVisitorModal').style.display = 'flex';
    updateDateRangeTable();
}

function closeCreateVisitorModal() {
    document.getElementById('createVisitorModal').style.display = 'none';
}

function updateDateRangeTable() {
    var startInput = document.getElementById('createStartDate');
    var endInput = document.getElementById('createEndDate');
    var container = document.getElementById('multiDayDetailsContainer');
    var daysListTable = document.getElementById('daysListTable');

    if (!startInput || !endInput || !startInput.value || !endInput.value) return;

    var s = new Date(startInput.value);
    var e = new Date(endInput.value);

    if (e < s) {
        endInput.value = startInput.value;
        e = new Date(startInput.value);
    }

    var diffDays = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;

    if (diffDays <= 1) {
        container.style.display = 'none';
        daysListTable.innerHTML = '';
        return;
    }

    container.style.display = 'block';

    var defaultBf = document.querySelector('#defaultMealsSection input[name="has_breakfast"]').checked;
    var defaultLu = document.querySelector('#defaultMealsSection input[name="has_lunch"]').checked;
    var defaultDi = document.querySelector('#defaultMealsSection input[name="has_dinner"]').checked;
    var defaultSu = document.querySelector('#defaultMealsSection input[name="has_supper"]').checked;
    var defaultPax = document.getElementById('createPaxCount').value || 1;

    var html = '<table style="width:100%;border-collapse:collapse;font-size:12px">';
    html += '<thead style="background:#f8fafc;border-bottom:1px solid #e2e8f0"><tr style="text-align:left"><th style="padding:6px 10px">Tanggal</th><th style="padding:6px 4px;text-align:center">B\'fast</th><th style="padding:6px 4px;text-align:center">Lunch</th><th style="padding:6px 4px;text-align:center">Dinner</th><th style="padding:6px 4px;text-align:center">Supper</th><th style="padding:6px 8px;text-align:center">Pax</th></tr></thead><tbody>';

    var cur = new Date(s);
    for (var i = 0; i < diffDays; i++) {
        var iso = cur.toISOString().split('T')[0];
        var formatted = cur.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });

        html += '<tr style="border-bottom:1px solid #f1f5f9">';
        html += '<td style="padding:6px 10px;font-weight:700;color:#1e293b">' + formatted + '</td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_breakfast]" value="1" ' + (defaultBf ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_lunch]" value="1" ' + (defaultLu ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_dinner]" value="1" ' + (defaultDi ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_supper]" value="1" ' + (defaultSu ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 8px;text-align:center"><input type="number" name="days[' + iso + '][pax_count]" value="' + defaultPax + '" min="1" max="100" style="width:48px;padding:2px 4px;font-size:11.5px;border:1px solid #cbd5e1;border-radius:4px;text-align:center"></td>';
        html += '</tr>';

        cur.setDate(cur.getDate() + 1);
    }
    html += '</tbody></table>';

    daysListTable.innerHTML = html;
}

function syncDayMealChecks() {
    updateDateRangeTable();
}

function updateEditDateRangeTable() {
    var startInput = document.getElementById('editStartDate');
    var endInput = document.getElementById('editEndDate');
    var container = document.getElementById('editMultiDayDetailsContainer');
    var daysListTable = document.getElementById('editDaysListTable');

    if (!startInput || !endInput || !startInput.value || !endInput.value) return;

    var s = new Date(startInput.value);
    var e = new Date(endInput.value);

    if (e < s) {
        endInput.value = startInput.value;
        e = new Date(startInput.value);
    }

    var diffDays = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;

    if (diffDays <= 1) {
        container.style.display = 'none';
        daysListTable.innerHTML = '';
        return;
    }

    container.style.display = 'block';

    var defaultBf = document.getElementById('editHasBf').checked;
    var defaultLu = document.getElementById('editHasLu').checked;
    var defaultDi = document.getElementById('editHasDi').checked;
    var defaultSu = document.getElementById('editHasSu').checked;
    var defaultPax = document.getElementById('editPaxCount').value || 1;

    var html = '<table style="width:100%;border-collapse:collapse;font-size:12px">';
    html += '<thead style="background:#f8fafc;border-bottom:1px solid #e2e8f0"><tr style="text-align:left"><th style="padding:6px 10px">Tanggal</th><th style="padding:6px 4px;text-align:center">B\'fast</th><th style="padding:6px 4px;text-align:center">Lunch</th><th style="padding:6px 4px;text-align:center">Dinner</th><th style="padding:6px 4px;text-align:center">Supper</th><th style="padding:6px 8px;text-align:center">Pax</th></tr></thead><tbody>';

    var cur = new Date(s);
    for (var i = 0; i < diffDays; i++) {
        var iso = cur.toISOString().split('T')[0];
        var formatted = cur.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });

        html += '<tr style="border-bottom:1px solid #f1f5f9">';
        html += '<td style="padding:6px 10px;font-weight:700;color:#1e293b">' + formatted + '</td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_breakfast]" value="1" ' + (defaultBf ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_lunch]" value="1" ' + (defaultLu ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_dinner]" value="1" ' + (defaultDi ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 4px;text-align:center"><input type="checkbox" name="days[' + iso + '][has_supper]" value="1" ' + (defaultSu ? 'checked' : '') + '></td>';
        html += '<td style="padding:6px 8px;text-align:center"><input type="number" name="days[' + iso + '][pax_count]" value="' + defaultPax + '" min="1" max="100" style="width:48px;padding:2px 4px;font-size:11.5px;border:1px solid #cbd5e1;border-radius:4px;text-align:center"></td>';
        html += '</tr>';

        cur.setDate(cur.getDate() + 1);
    }
    html += '</tbody></table>';

    daysListTable.innerHTML = html;
}

function syncEditDayMealChecks() {
    updateEditDateRangeTable();
}

function openEditVisitorModal(visitor) {
    var modal = document.getElementById('editVisitorModal');
    var form = document.getElementById('editVisitorForm');

    form.action = '/visitor-meals/' + visitor.id;
    document.getElementById('editVisitorName').value = visitor.visitor_name || '';
    document.getElementById('editInstitution').value = visitor.institution || '';
    document.getElementById('editPaxCount').value = visitor.pax_count || 1;
    document.getElementById('editMealLocationId').value = visitor.meal_location_id || '';
    document.getElementById('editNotes').value = visitor.notes || '';

    var startDateStr = visitor.group_start_date || (visitor.meal_date ? new Date(visitor.meal_date).toISOString().split('T')[0] : '');
    var endDateStr = visitor.group_end_date || (visitor.meal_date ? new Date(visitor.meal_date).toISOString().split('T')[0] : '');

    document.getElementById('editStartDate').value = startDateStr;
    document.getElementById('editEndDate').value = endDateStr;

    document.getElementById('editHasBf').checked = !!visitor.has_breakfast;
    document.getElementById('editHasLu').checked = !!visitor.has_lunch;
    document.getElementById('editHasDi').checked = !!visitor.has_dinner;
    document.getElementById('editHasSu').checked = !!visitor.has_supper;

    updateEditDateRangeTable();

    modal.style.display = 'flex';
}

function closeEditVisitorModal() {
    document.getElementById('editVisitorModal').style.display = 'none';
}
</script>
@endpush
@endsection
