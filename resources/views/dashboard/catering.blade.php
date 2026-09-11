@extends('layouts.app')
@section('title', 'Dashboard Catering')
@section('page-title', 'Dashboard Catering')

@section('content')
<div style="max-width:1100px;margin:0 auto">

    {{-- ── 1. Top Control Bar (Pilih Tanggal & Akses Manifest) ── --}}
    <div class="card" style="margin-bottom:18px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-body" style="padding:16px 20px">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                <form method="GET" id="dateFilterForm" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:8px">
                        <label style="font-size:13px;font-weight:700;color:#166534">📅 Tanggal:</label>
                        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" class="form-input" style="padding:6px 12px;font-size:13px;background:#fff;border:1px solid #cbd5e1;border-radius:8px;font-weight:700;color:#1e293b">
                    </div>
                    @if(!$date->isToday())
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#fff;font-weight:600">Hari Ini</a>
                    @endif
                </form>

                <a href="{{ route('manifest.index', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-primary btn-sm" style="background:#006738;border-color:#006738;display:inline-flex;align-items:center;gap:6px;font-weight:700">
                    📄 Buka Manifest & Daftar Hadir →
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Ringkasan Total Porsi 5 Mess Hall (Tanggal: {{ $date->translatedFormat('d M Y') }}) ── --}}
    <div class="card" style="margin-bottom:18px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#166534">
                🍽️ Total Porsi Seluruh Mess Hall ({{ $date->translatedFormat('l, d F Y') }})
            </h3>
            <span style="font-size:13px;font-weight:800;color:#16a34a">
                Total: {{ $overallTotals['total'] }} Pax ({{ $overallTotals['workers'] }} Karyawan)
            </span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:0;background:#fff">
            <div style="padding:16px 20px;border-right:1px solid #f1f5f9">
                <p style="font-size:11px;font-weight:700;color:#c2410c;text-transform:uppercase;margin:0 0 4px">B'fast</p>
                <p style="font-size:22px;font-weight:900;color:#c2410c;margin:0">{{ $overallTotals['breakfast'] }} <span style="font-size:13px;font-weight:600">pax</span></p>
            </div>
            <div style="padding:16px 20px;border-right:1px solid #f1f5f9">
                <p style="font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;margin:0 0 4px">Lunch</p>
                <p style="font-size:22px;font-weight:900;color:#1d4ed8;margin:0">{{ $overallTotals['lunch'] }} <span style="font-size:13px;font-weight:600">pax</span></p>
            </div>
            <div style="padding:16px 20px;border-right:1px solid #f1f5f9">
                <p style="font-size:11px;font-weight:700;color:#6d28d9;text-transform:uppercase;margin:0 0 4px">Dinner</p>
                <p style="font-size:22px;font-weight:900;color:#6d28d9;margin:0">{{ $overallTotals['dinner'] }} <span style="font-size:13px;font-weight:600">pax</span></p>
            </div>
            <div style="padding:16px 20px">
                <p style="font-size:11px;font-weight:700;color:#9d174d;text-transform:uppercase;margin:0 0 4px">Supper</p>
                <p style="font-size:22px;font-weight:900;color:#9d174d;margin:0">{{ $overallTotals['supper'] }} <span style="font-size:13px;font-weight:600">pax</span></p>
            </div>
        </div>
    </div>

    {{-- ── 3. Pesanan 5 Mess Hall (B'fast, Lunch, Dinner, Supper) ── --}}
    <div class="card" style="margin-bottom:18px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bae6fd;padding:14px 20px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0369a1">
                🏢 Rincian Pesanan 5 Mess Hall ({{ $date->translatedFormat('l, d F Y') }})
            </h3>
        </div>
        <div style="overflow-x:auto;background:#fff">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th style="min-width:200px">Nama Mess Hall</th>
                        <th style="min-width:110px">Wilayah</th>
                        <th style="text-align:center;min-width:85px">Karyawan</th>
                        <th style="text-align:center;min-width:75px">B'fast</th>
                        <th style="text-align:center;min-width:75px">Lunch</th>
                        <th style="text-align:center;min-width:75px">Dinner</th>
                        <th style="text-align:center;min-width:75px">Supper</th>
                        <th style="text-align:center;min-width:90px">Total Pax</th>
                        <th style="text-align:center;min-width:110px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNo = 1; @endphp
                    @foreach($messHallData as $key => $mh)
                    <tr>
                        <td style="text-align:center;color:#64748b;font-weight:700">{{ $rowNo++ }}</td>
                        <td>
                            <p style="font-weight:800;color:#1e293b;margin:0;font-size:13.5px">{{ $mh['name'] }}</p>
                        </td>
                        <td>
                            <span class="badge badge-blue" style="font-size:11px">{{ $mh['region_name'] }}</span>
                        </td>
                        <td style="text-align:center;font-weight:700;color:#475569">
                            {{ $mh['workers'] }} org
                        </td>
                        <td style="text-align:center;{{ $mh['totals']['breakfast'] > 0 ? 'background:#fff7ed;color:#c2410c;font-weight:700' : 'color:#94a3b8' }}">
                            {{ $mh['totals']['breakfast'] }}
                        </td>
                        <td style="text-align:center;{{ $mh['totals']['lunch'] > 0 ? 'background:#eff6ff;color:#1d4ed8;font-weight:700' : 'color:#94a3b8' }}">
                            {{ $mh['totals']['lunch'] }}
                        </td>
                        <td style="text-align:center;{{ $mh['totals']['dinner'] > 0 ? 'background:#f5f3ff;color:#6d28d9;font-weight:700' : 'color:#94a3b8' }}">
                            {{ $mh['totals']['dinner'] }}
                        </td>
                        <td style="text-align:center;{{ $mh['totals']['supper'] > 0 ? 'background:#fdf2f8;color:#9d174d;font-weight:700' : 'color:#94a3b8' }}">
                            {{ $mh['totals']['supper'] }}
                        </td>
                        <td style="text-align:center;font-weight:900;color:#0284c7;background:#f0f9ff;font-size:13.5px">
                            {{ $mh['grand_total'] }} pax
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('manifest.messhall.show', ['date' => $date->format('Y-m-d'), 'key' => $key]) }}"
                               class="btn btn-secondary btn-sm" style="font-size:11px;padding:4px 8px;font-weight:600;background:#fff">
                                👁️ Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 4. Trend Porsi per Hari (7 Hari berdasarkan 5 Mess Hall) ── --}}
    <div class="card" style="margin-bottom:18px;background:#fdf4ff;border:1px solid #f5d0fe;border-left:4px solid #a855f7;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #f5d0fe;padding:14px 20px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#7e22ce">
                📊 Trend Porsi 7 Hari Berdasarkan 5 Mess Hall (Mulai {{ $date->translatedFormat('d M Y') }})
            </h3>
        </div>
        <div style="overflow-x:auto;background:#fff">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="min-width:130px">Tanggal</th>
                        <th style="min-width:90px">Hari</th>
                        <th style="text-align:center;min-width:100px">MH Staff Ramba</th>
                        <th style="text-align:center;min-width:110px">MH Non Staff Ramba</th>
                        <th style="text-align:center;min-width:90px">MH Bentayan</th>
                        <th style="text-align:center;min-width:95px">MH Mangunjaya</th>
                        <th style="text-align:center;min-width:85px">MH Kluang</th>
                        <th style="text-align:center;min-width:90px">Total Pax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyTrend as $d)
                    <tr @if($d['is_today']) style="background:#fdf4ff;font-weight:700" @endif>
                        <td style="font-weight:700;color:#1e293b">
                            {{ $d['date']->translatedFormat('d M Y') }}
                            @if($d['is_today'])
                                <span class="badge" style="background:#a855f7;color:#fff;font-size:9.5px;padding:2px 5px;margin-left:4px">Hari Ini</span>
                            @endif
                        </td>
                        <td style="color:#475569">{{ $d['date']->translatedFormat('l') }}</td>
                        <td style="text-align:center;{{ ($d['messhalls']['ramba-staff'] ?? 0) > 0 ? 'font-weight:700;color:#006738' : 'color:#94a3b8' }}">
                            {{ $d['messhalls']['ramba-staff'] ?? 0 }}
                        </td>
                        <td style="text-align:center;{{ ($d['messhalls']['ramba-nonstaff'] ?? 0) > 0 ? 'font-weight:700;color:#1d4ed8' : 'color:#94a3b8' }}">
                            {{ $d['messhalls']['ramba-nonstaff'] ?? 0 }}
                        </td>
                        <td style="text-align:center;{{ ($d['messhalls']['bentayan'] ?? 0) > 0 ? 'font-weight:700;color:#c2410c' : 'color:#94a3b8' }}">
                            {{ $d['messhalls']['bentayan'] ?? 0 }}
                        </td>
                        <td style="text-align:center;{{ ($d['messhalls']['mangunjaya'] ?? 0) > 0 ? 'font-weight:700;color:#6d28d9' : 'color:#94a3b8' }}">
                            {{ $d['messhalls']['mangunjaya'] ?? 0 }}
                        </td>
                        <td style="text-align:center;{{ ($d['messhalls']['kluang'] ?? 0) > 0 ? 'font-weight:700;color:#0891b2' : 'color:#94a3b8' }}">
                            {{ $d['messhalls']['kluang'] ?? 0 }}
                        </td>
                        <td style="text-align:center;font-weight:900;color:#7e22ce;background:#faf5ff">
                            {{ $d['total_pax'] }} pax
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
