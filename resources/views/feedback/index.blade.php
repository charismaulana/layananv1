@extends('layouts.app')
@section('title', 'Summary Rating')
@section('page-title', 'Summary Rating')

@section('content')
<div style="margin-bottom:24px">

    {{-- ── 1. Filter Bar ── --}}
    <div class="card" style="margin-bottom:20px;padding:16px 20px;border-radius:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #006738;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <form method="GET" action="{{ route('feedback.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label class="form-label" style="font-size:11.5px;font-weight:700;color:#166534;margin-bottom:4px">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input" style="font-size:12.5px;padding:6px 10px;background:#fff">
            </div>
            <div>
                <label class="form-label" style="font-size:11.5px;font-weight:700;color:#166534;margin-bottom:4px">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input" style="font-size:12.5px;padding:6px 10px;background:#fff">
            </div>
            <div>
                <label class="form-label" style="font-size:11.5px;font-weight:700;color:#166534;margin-bottom:4px">Wilayah</label>
                <select name="region_id" class="form-input" style="font-size:12.5px;padding:6px 10px;background:#fff">
                    <option value="">Semua Wilayah</option>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}" {{ $regionId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div style="display:flex;gap:6px">
                <button type="submit" class="btn btn-primary btn-sm" style="padding:7px 16px;font-weight:700">
                    Filter
                </button>
                <a href="{{ route('feedback.index') }}" class="btn btn-secondary btn-sm" style="padding:7px 12px">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ── 2. KPI Summary Cards ── --}}
    <div class="grid-4" style="gap:14px;margin-bottom:24px">
        {{-- Rata-Rata Rating --}}
        <div class="card" style="padding:18px;background:#fefce8;border:1px solid #fef08a;border-left:4px solid #d97706;border-radius:12px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <p style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase;margin:0">Rata-Rata Rating</p>
                <span style="font-size:16px">⭐</span>
            </div>
            <div style="display:flex;align-items:baseline;gap:6px">
                <p style="font-size:28px;font-weight:800;color:#854d0e;margin:0">
                    ★ {{ $avgScore ?: '-' }}
                </p>
                <span style="font-size:13px;font-weight:600;color:#b45309">/ 5.0</span>
            </div>
            <p style="font-size:11px;color:#a16207;margin:4px 0 0">Dari total {{ $totalRatings }} ulasan katering</p>
        </div>

        {{-- Distribusi Kepuasan --}}
        <div class="card" style="padding:18px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <p style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;margin:0">Kepuasan Tertinggi</p>
                <span style="font-size:16px">👍</span>
            </div>
            <p style="font-size:28px;font-weight:800;color:#14532d;margin:0">
                {{ ($starCounts[5]['pct'] + $starCounts[4]['pct']) }}%
            </p>
            <p style="font-size:11px;color:#166534;margin:4px 0 0">Memberikan rating 4★ & 5★</p>
        </div>

        {{-- Total Saran & Masukan --}}
        <div class="card" style="padding:18px;background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #2563eb;border-radius:12px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <p style="font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;margin:0">Saran & Masukan Masuk</p>
                <span style="font-size:16px">💬</span>
            </div>
            <p style="font-size:28px;font-weight:800;color:#1e40af;margin:0">
                {{ $totalSuggestions }} <span style="font-size:13px;font-weight:500;color:#1d4ed8">masukan</span>
            </p>
            <p style="font-size:11px;color:#2563eb;margin:4px 0 0">
                @if($unreadSuggestions > 0)
                <span style="color:#b91c1c;font-weight:700">● {{ $unreadSuggestions }} belum ditinjau</span>
                @else
                ✓ Semua telah ditinjau
                @endif
            </p>
        </div>

        {{-- Rating per Wilayah --}}
        <div class="card" style="padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #64748b;border-radius:12px">
            <p style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 6px">Rata-Rata per Wilayah</p>
            <div style="display:flex;flex-direction:column;gap:3px">
                @foreach($regionAverages as $ra)
                <div style="display:flex;justify-content:space-between;font-size:11.5px">
                    <span style="color:#334155;font-weight:600">{{ $ra['name'] }}:</span>
                    <span style="font-weight:700;color:{{ $ra['avg'] ? '#d97706' : '#94a3b8' }}">
                        {{ $ra['avg'] ? "★ {$ra['avg']}" : '-' }} <span style="font-size:10px;color:#64748b">({{ $ra['count'] }})</span>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── 3. Tab Navigasi ── --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;border-bottom:2px solid #e2e8f0;padding-bottom:2px">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'ratings']) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:700;text-decoration:none;border-bottom:3px solid {{ $activeTab === 'ratings' ? '#006738' : 'transparent' }};color:{{ $activeTab === 'ratings' ? '#006738' : '#64748b' }};display:flex;align-items:center;gap:6px">
            ⭐ Rekapitulasi Rating Katering
            <span class="badge {{ $activeTab === 'ratings' ? 'badge-green' : 'badge-gray' }}" style="font-size:10.5px">{{ $ratings->total() }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'suggestions']) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:700;text-decoration:none;border-bottom:3px solid {{ $activeTab === 'suggestions' ? '#006738' : 'transparent' }};color:{{ $activeTab === 'suggestions' ? '#006738' : '#64748b' }};display:flex;align-items:center;gap:6px">
            💬 Saran & Masukan Karyawan
            <span class="badge {{ $activeTab === 'suggestions' ? 'badge-blue' : 'badge-gray' }}" style="font-size:10.5px">{{ $suggestions->total() }}</span>
        </a>
    </div>

    {{-- ── 4. Konten Tab ── --}}
    @if($activeTab === 'ratings')
    {{-- ── TAB 1: TABEL RATING KATERING ── --}}
    <div class="card" style="padding:0;overflow:hidden;border-radius:14px;background:#fff;border:1px solid #e2e8f0;border-left:4px solid #006738;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div style="overflow-x:auto">
            <table class="tbl" style="margin:0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th style="min-width:90px">Tanggal</th>
                        <th>Menu & Jenis Makan</th>
                        <th>Wilayah</th>
                        <th style="text-align:center;min-width:110px">Skor Bintang</th>
                        <th>Komentar / Ulasan Pengguna</th>
                        <th>Pengulas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $idx => $r)
                    <tr>
                        <td style="text-align:center;font-weight:700;color:#64748b">
                            {{ $ratings->firstItem() + $idx }}
                        </td>
                        <td style="font-size:12px;font-weight:700;color:#1e293b;white-space:nowrap">
                            {{ $r->rating_date ? $r->rating_date->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:13px;color:#1a2332">
                                {{ $r->mealType?->name ?? 'Makan' }}
                            </div>
                            <div style="font-size:11.5px;color:#64748b">
                                {{ $r->menu ? $r->menu->items->pluck('name')->implode(', ') : '-' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-green" style="font-size:11px">
                                {{ $r->region?->name ?? 'Ramba' }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <div style="font-size:13px;color:#eab308;font-weight:800">
                                @for($s=1; $s<=5; $s++)
                                    {{ $s <= $r->score ? '★' : '☆' }}
                                @endfor
                            </div>
                            <span style="font-size:11px;font-weight:700;color:#475569">{{ $r->score }} / 5</span>
                        </td>
                        <td>
                            @if($r->comment)
                            <div style="font-size:12.5px;color:#1e293b;background:#f8fafc;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0">
                                "{{ $r->comment }}"
                            </div>
                            @else
                            <span style="font-size:11.5px;color:#94a3b8;font-style:italic">Tanpa komentar tertulis</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:12.5px;color:#1a2332">
                                {{ $r->user?->name ?? 'Anonim' }}
                            </div>
                            <div style="font-size:11px;color:#64748b">
                                {{ $r->user?->department?->name ?? '-' }} · {{ $r->user?->company?->name ?? 'PEP' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                            Belum ada data rating katering yang sesuai dengan filter tanggal <strong>{{ $from->translatedFormat('d M Y') }} s/d {{ $to->translatedFormat('d M Y') }}</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ratings->hasPages())
        <div style="padding:12px 18px;border-top:1px solid #e2e8f0;background:#fafafa">
            {{ $ratings->links() }}
        </div>
        @endif
    </div>

    @else
    {{-- ── TAB 2: TABEL SARAN & MASUKAN KARYAWAN ── --}}
    <div class="card" style="padding:0;overflow:hidden;border-radius:14px;background:#fff;border:1px solid #e2e8f0;border-left:4px solid #2563eb;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div style="overflow-x:auto">
            <table class="tbl" style="margin:0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th style="min-width:110px">Waktu Masuk</th>
                        <th>Pengirim & Departemen</th>
                        <th>Wilayah</th>
                        <th>Kategori</th>
                        <th>Isi Saran & Masukan</th>
                        <th style="text-align:center;width:130px">Status</th>
                        <th style="text-align:center;width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suggestions as $idx => $s)
                    <tr style="{{ !$s->is_read ? 'background:#f0f9ff' : '' }}">
                        <td style="text-align:center;font-weight:700;color:#64748b">
                            {{ $suggestions->firstItem() + $idx }}
                        </td>
                        <td style="font-size:12px;font-weight:700;color:#1e293b;white-space:nowrap">
                            <div>{{ $s->created_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px;color:#64748b;font-weight:500">{{ $s->created_at->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:13px;color:#1a2332">
                                {{ $s->user?->name ?? 'Anonim' }}
                            </div>
                            <div style="font-size:11px;color:#64748b">
                                {{ $s->user?->department?->name ?? '-' }} · {{ $s->user?->company?->name ?? 'PEP' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-green" style="font-size:11px">
                                {{ $s->region?->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-gold" style="font-size:11px">
                                {{ ucfirst($s->category ?: 'Umum') }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:12.5px;color:#1e293b;line-height:1.45;background:#fff;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0">
                                {{ $s->content }}
                            </div>
                        </td>
                        <td style="text-align:center">
                            @if($s->is_read)
                            <span class="badge badge-green" style="font-size:11px">✓ Sudah Ditinjau</span>
                            @else
                            <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px">● Belum Ditinjau</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <form method="POST" action="{{ route('feedback.toggle-read', $s) }}" style="margin:0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary btn-sm" style="font-size:11px;padding:4px 8px" title="{{ $s->is_read ? 'Tandai belum ditinjau' : 'Tandai sudah ditinjau' }}">
                                    {{ $s->is_read ? 'Batal Tinjau' : '✓ Tandai' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                            Belum ada saran & masukan yang masuk pada rentang tanggal <strong>{{ $from->translatedFormat('d M Y') }} s/d {{ $to->translatedFormat('d M Y') }}</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suggestions->hasPages())
        <div style="padding:12px 18px;border-top:1px solid #e2e8f0;background:#fafafa">
            {{ $suggestions->links() }}
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
