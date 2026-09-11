@extends('layouts.app')
@section('title', 'Lembar Manifest: ' . $config['name'])
@section('page-title', 'Lembar Manifest Mess Hall')

@section('content')
<div style="max-width:1100px;margin:0 auto">

    {{-- ── 1. Top Bar Navigation & KPI ── --}}
    <div class="card" style="margin-bottom:18px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:20px">📋</span>
                    <h3 style="font-size:17px;font-weight:800;color:#1a2332;margin:0">
                        {{ $config['name'] }}
                    </h3>
                </div>
                <p style="font-size:12.5px;color:#334155;margin:4px 0 0;font-weight:600">
                    Tanggal: <strong style="color:#16a34a">{{ $date->translatedFormat('l, d F Y') }}</strong>
                </p>
            </div>

            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <a href="{{ route('manifest.messhall.pdf', ['date' => $date->format('Y-m-d'), 'key' => $config['key']]) }}" target="_blank"
                   class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;padding:9px 18px;font-weight:700">
                    Cetak PDF
                </a>
                <a href="{{ route('manifest.index', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-secondary" style="padding:9px 16px;background:#fff;font-weight:600">
                    Kembali
                </a>
            </div>
        </div>

        {{-- KPI Summary Pax --}}
        <div style="background:#fff;border-top:1px solid #bbf7d0">
            {{-- Baris 1: Total Karyawan --}}
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
                <p style="font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;margin:0;letter-spacing:.3px">Total Karyawan</p>
                <p style="font-size:18px;font-weight:900;color:#1a2332;margin:0">{{ count($rows) }} orang</p>
            </div>

            {{-- Baris 2: B'fast & Lunch --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #f1f5f9">
                <div style="padding:12px 20px;border-right:1px solid #f1f5f9">
                    <p style="font-size:10px;font-weight:700;color:#c2410c;text-transform:uppercase;margin:0 0 2px">B'fast</p>
                    <p style="font-size:16px;font-weight:800;color:#c2410c;margin:0">{{ $totals['breakfast'] }} pax</p>
                </div>
                <div style="padding:12px 20px">
                    <p style="font-size:10px;font-weight:700;color:#1d4ed8;text-transform:uppercase;margin:0 0 2px">Lunch</p>
                    <p style="font-size:16px;font-weight:800;color:#1d4ed8;margin:0">{{ $totals['lunch'] }} pax</p>
                </div>
            </div>

            {{-- Baris 3: Dinner & Supper --}}
            <div style="display:grid;grid-template-columns:1fr 1fr">
                <div style="padding:12px 20px;border-right:1px solid #f1f5f9">
                    <p style="font-size:10px;font-weight:700;color:#6d28d9;text-transform:uppercase;margin:0 0 2px">Dinner</p>
                    <p style="font-size:16px;font-weight:800;color:#6d28d9;margin:0">{{ $totals['dinner'] }} pax</p>
                </div>
                <div style="padding:12px 20px">
                    <p style="font-size:10px;font-weight:700;color:#9d174d;text-transform:uppercase;margin:0 0 2px">Supper</p>
                    <p style="font-size:16px;font-weight:800;color:#9d174d;margin:0">{{ $totals['supper'] }} pax</p>
                </div>
            </div>
        </div>
    </div>



    {{-- ── 3. Table of Workers with Live Search Header ── --}}
    <div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:12px 18px">
            <input type="text" id="filterInput" oninput="filterTable(this.value)"
                   placeholder="🔍 Cari nama karyawan / fungsi..."
                   class="form-input" style="width:100%;max-width:360px;padding:8px 14px;font-size:13px;background:#fff;border:1px solid #cbd5e1">
        </div>

        <div style="overflow-x:auto;background:#fff">
            <table class="tbl" id="manifestTable">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th style="min-width:190px">Nama Karyawan (A-Z)</th>
                        <th style="min-width:90px">Status</th>
                        <th style="min-width:140px">Fungsi</th>
                        <th style="text-align:center;min-width:120px">B'fast</th>
                        <th style="text-align:center;min-width:120px">Lunch</th>
                        <th style="text-align:center;min-width:120px">Dinner</th>
                        <th style="text-align:center;min-width:120px">Supper</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                    <tr>
                        <td style="text-align:center;color:#6b7280;font-weight:700">{{ $r['number'] }}</td>
                        <td>
                            <div>
                                <p style="font-weight:700;color:#1a2332;margin:0;font-size:13.5px">{{ $r['name'] }}</p>
                                @if(!empty($r['jabatan']))
                                <p style="font-size:11px;color:#64748b;margin:1px 0 0">{{ $r['jabatan'] }}</p>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-gray" style="font-size:11px">{{ $r['worker_status'] }}</span>
                        </td>
                        <td style="font-size:12.5px;color:#374151;font-weight:600">{{ $r['department'] }}</td>

                        {{-- 1. B'fast --}}
                        <td style="text-align:center;height:36px;vertical-align:middle;padding:4px">
                            @if($r['breakfast']['active'])
                                @if($r['breakfast']['type'] === 'messhall')
                                    <div style="background:#eef9f2;border:1.5px solid #86efac;border-radius:6px;padding:5px 8px;color:#166534;font-weight:700;font-size:11px">
                                        Paraf [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
                                    </div>
                                @elseif($r['breakfast']['type'] === 'outside_meal')
                                    <div style="background:#ffedd5;border:1.5px solid #fb923c;border-radius:6px;padding:4px 6px;color:#c2410c;font-weight:700;font-size:10.5px">
                                        📦 Outside Meal
                                    </div>
                                @elseif($r['breakfast']['type'] === 'other_messhall')
                                    <div style="background:#f3e8ff;border:1.5px solid #c084fc;border-radius:6px;padding:4px 6px;color:#7e22ce;font-weight:700;font-size:10.5px">
                                        🏢 {{ $r['breakfast']['label'] }}
                                    </div>
                                @else
                                    <div style="background:#dbeafe;border:1.5px solid #3b82f6;border-radius:6px;padding:4px 6px;color:#1d4ed8;font-weight:700;font-size:10.5px">
                                        🚚 {{ $r['breakfast']['label'] }}
                                    </div>
                                @endif
                            @elseif($r['breakfast']['type'] === 'cancelled')
                                <div style="background:#ffe4e6;border:1.5px solid #fda4af;border-radius:6px;padding:4px 6px;color:#be123c;font-weight:700;font-size:10.5px">
                                    ✕ Batal
                                </div>
                            @else
                                <span style="color:#cbd5e1;font-size:13px;font-weight:700">-</span>
                            @endif
                        </td>

                        {{-- 2. Lunch --}}
                        <td style="text-align:center;height:36px;vertical-align:middle;padding:4px">
                            @if($r['lunch']['active'])
                                @if($r['lunch']['type'] === 'messhall')
                                    <div style="background:#eef9f2;border:1.5px solid #86efac;border-radius:6px;padding:5px 8px;color:#166534;font-weight:700;font-size:11px">
                                        Paraf [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
                                    </div>
                                @elseif($r['lunch']['type'] === 'outside_meal')
                                    <div style="background:#ffedd5;border:1.5px solid #fb923c;border-radius:6px;padding:4px 6px;color:#c2410c;font-weight:700;font-size:10.5px">
                                        📦 Outside Meal
                                    </div>
                                @elseif($r['lunch']['type'] === 'other_messhall')
                                    <div style="background:#f3e8ff;border:1.5px solid #c084fc;border-radius:6px;padding:4px 6px;color:#7e22ce;font-weight:700;font-size:10.5px">
                                        🏢 {{ $r['lunch']['label'] }}
                                    </div>
                                @else
                                    <div style="background:#dbeafe;border:1.5px solid #3b82f6;border-radius:6px;padding:4px 6px;color:#1d4ed8;font-weight:700;font-size:10.5px">
                                        🚚 {{ $r['lunch']['label'] }}
                                    </div>
                                @endif
                            @elseif($r['lunch']['type'] === 'cancelled')
                                <div style="background:#ffe4e6;border:1.5px solid #fda4af;border-radius:6px;padding:4px 6px;color:#be123c;font-weight:700;font-size:10.5px">
                                    ✕ Batal
                                </div>
                            @else
                                <span style="color:#cbd5e1;font-size:13px;font-weight:700">-</span>
                            @endif
                        </td>

                        {{-- 3. Dinner --}}
                        <td style="text-align:center;height:36px;vertical-align:middle;padding:4px">
                            @if($r['dinner']['active'])
                                @if($r['dinner']['type'] === 'messhall')
                                    <div style="background:#eef9f2;border:1.5px solid #86efac;border-radius:6px;padding:5px 8px;color:#166534;font-weight:700;font-size:11px">
                                        Paraf [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
                                    </div>
                                @elseif($r['dinner']['type'] === 'outside_meal')
                                    <div style="background:#ffedd5;border:1.5px solid #fb923c;border-radius:6px;padding:4px 6px;color:#c2410c;font-weight:700;font-size:10.5px">
                                        📦 Outside Meal
                                    </div>
                                @elseif($r['dinner']['type'] === 'other_messhall')
                                    <div style="background:#f3e8ff;border:1.5px solid #c084fc;border-radius:6px;padding:4px 6px;color:#7e22ce;font-weight:700;font-size:10.5px">
                                        🏢 {{ $r['dinner']['label'] }}
                                    </div>
                                @else
                                    <div style="background:#dbeafe;border:1.5px solid #3b82f6;border-radius:6px;padding:4px 6px;color:#1d4ed8;font-weight:700;font-size:10.5px">
                                        🚚 {{ $r['dinner']['label'] }}
                                    </div>
                                @endif
                            @elseif($r['dinner']['type'] === 'cancelled')
                                <div style="background:#ffe4e6;border:1.5px solid #fda4af;border-radius:6px;padding:4px 6px;color:#be123c;font-weight:700;font-size:10.5px">
                                    ✕ Batal
                                </div>
                            @else
                                <span style="color:#cbd5e1;font-size:13px;font-weight:700">-</span>
                            @endif
                        </td>

                        {{-- 4. Supper --}}
                        <td style="text-align:center;height:36px;vertical-align:middle;padding:4px">
                            @if($r['supper']['active'])
                                @if($r['supper']['type'] === 'messhall')
                                    <div style="background:#eef9f2;border:1.5px solid #86efac;border-radius:6px;padding:5px 8px;color:#166534;font-weight:700;font-size:11px">
                                        Paraf [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
                                    </div>
                                @elseif($r['supper']['type'] === 'outside_meal')
                                    <div style="background:#ffedd5;border:1.5px solid #fb923c;border-radius:6px;padding:4px 6px;color:#c2410c;font-weight:700;font-size:10.5px">
                                        📦 Outside Meal
                                    </div>
                                @elseif($r['supper']['type'] === 'other_messhall')
                                    <div style="background:#f3e8ff;border:1.5px solid #c084fc;border-radius:6px;padding:4px 6px;color:#7e22ce;font-weight:700;font-size:10.5px">
                                        🏢 {{ $r['supper']['label'] }}
                                    </div>
                                @else
                                    <div style="background:#dbeafe;border:1.5px solid #3b82f6;border-radius:6px;padding:4px 6px;color:#1d4ed8;font-weight:700;font-size:10.5px">
                                        🚚 {{ $r['supper']['label'] }}
                                    </div>
                                @endif
                            @elseif($r['supper']['type'] === 'cancelled')
                                <div style="background:#ffe4e6;border:1.5px solid #fda4af;border-radius:6px;padding:4px 6px;color:#be123c;font-weight:700;font-size:10.5px">
                                    ✕ Batal
                                </div>
                            @else
                                <span style="color:#cbd5e1;font-size:13px;font-weight:700">-</span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:36px;color:#9ca3af">
                            Tidak ada karyawan yang terdaftar makan di {{ $config['name'] }} pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filterTable(query) {
    var s = query.toLowerCase().trim();
    var rows = document.querySelectorAll('#manifestTable tbody tr');
    rows.forEach(function(r) {
        var text = r.textContent.toLowerCase();
        r.style.display = (!s || text.includes(s)) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
