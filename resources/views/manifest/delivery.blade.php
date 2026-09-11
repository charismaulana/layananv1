@extends('layouts.app')
@section('title', 'Manifest Pengantaran — ' . $date->format('d/m/Y'))
@section('page-title', 'Manifest Pengantaran')

@section('content')
<div style="max-width:1100px;margin:0 auto">

    {{-- Breadcrumb & Back --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:8px">
            <a href="{{ route('manifest.index', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-secondary btn-sm" style="background:#fff;font-weight:600">
                ← Kembali ke Ringkasan Manifest
            </a>
            <span style="color:#9ca3af">/</span>
            <span style="font-size:13px;font-weight:700;color:#0284c7">Pengantaran & Drop-point</span>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <a href="{{ route('manifest.delivery.pdf', ['date' => $date->format('Y-m-d')]) }}"
               target="_blank"
               class="btn btn-primary"
               style="background:#0284c7;border-color:#0284c7;display:inline-flex;align-items:center;gap:6px;font-weight:700">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                🖨️ Cetak Lembar Pengantaran (PDF)
            </a>
        </div>
    </div>

    {{-- Header Card --}}
    <div class="card" style="margin-bottom:18px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-body" style="padding:18px 20px">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span style="font-size:20px">🚚</span>
                        <h2 style="font-size:18px;font-weight:800;color:#1a2332;margin:0">Manifest Pengantaran</h2>
                    </div>
                    <div style="font-size:12.5px;color:#334155;font-weight:600">
                        📅 Tanggal: <strong style="color:#0284c7">{{ $date->translatedFormat('l, d F Y') }}</strong>
                    </div>
                </div>

                {{-- Pax Breakdown Pills --}}
                <div style="background:#fff;border:1px solid #bae6fd;border-radius:12px;padding:12px 18px;text-align:right">
                    <p style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase;margin:0 0 4px">Total Porsi Diantar</p>
                    <p style="font-size:22px;font-weight:900;color:#0369a1;margin:0;line-height:1">{{ $grand_total }} <span style="font-size:13px;font-weight:600">pax</span></p>
                    <div style="display:flex;gap:10px;margin-top:8px;font-size:11.5px;color:#0369a1;font-weight:600">
                        <span>B: <strong>{{ $totals['breakfast'] }}</strong></span>
                        <span>L: <strong>{{ $totals['lunch'] }}</strong></span>
                        <span>D: <strong>{{ $totals['dinner'] }}</strong></span>
                        <span>S: <strong>{{ $totals['supper'] }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- Breakdown Titik Lokasi --}}
            @if(count($location_breakdown) > 0)
            <div style="margin-top:14px;padding-top:12px;border-top:1px solid #e0f2fe;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="font-size:11.5px;font-weight:700;color:#0369a1">Titik Antar:</span>
                @foreach($location_breakdown as $locName => $pax)
                    <span class="badge badge-blue" style="font-size:11.5px;padding:4px 10px;background:#fff;border:1px solid #bfdbfe;color:#0284c7">
                        📍 {{ $locName }}: <strong>{{ $pax }} pax</strong>
                    </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card" style="background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bae6fd;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#1a2332">Daftar Karyawan yang Diantar ({{ count($rows) }} orang)</h3>
            <input type="text" id="deliveryFilter" placeholder="🔍 Cari nama karyawan..." onkeyup="filterDeliveryTable()" style="padding:6px 12px;font-size:12px;border:1px solid #cbd5e1;border-radius:6px;width:220px;background:#fff">
        </div>

        <div style="overflow-x:auto;background:#fff">
            <table class="tbl" id="deliveryTable">
                <thead>
                    <tr>
                        <th style="width:45px;text-align:center">No</th>
                        <th style="min-width:180px">Nama Karyawan</th>
                        <th style="min-width:130px">Departemen</th>
                        <th style="min-width:180px">Tujuan Pengantaran</th>
                        <th style="text-align:center;min-width:80px">B'fast</th>
                        <th style="text-align:center;min-width:80px">Lunch</th>
                        <th style="text-align:center;min-width:80px">Dinner</th>
                        <th style="text-align:center;min-width:80px">Supper</th>
                        <th style="text-align:center;min-width:80px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasPrintedAny = false; @endphp
                    @foreach($regions_data as $regName => $regData)
                        @if(count($regData['rows']) > 0)
                        @php $hasPrintedAny = true; @endphp
                        <tr>
                            <td colspan="9" style="background:#e0f2fe;color:#0369a1;font-weight:800;font-size:13px;padding:10px 16px;border-top:2px solid #bae6fd;border-bottom:1px solid #bae6fd">
                                📍 WILAYAH {{ strtoupper($regName) }} — {{ count($regData['rows']) }} Pekerja · {{ $regData['grand_total'] }} Pax
                            </td>
                        </tr>
                        @foreach($regData['rows'] as $r)
                        <tr>
                            <td style="text-align:center;color:#6b7280;font-weight:700">{{ $r['number'] }}</td>
                            <td>
                                <p style="font-weight:700;color:#1a2332;margin:0;font-size:13.5px">{{ $r['name'] }}</p>
                                @if($r['nomor_pegawai'] !== '-')
                                <p style="font-size:11px;color:#6b7280;margin:1px 0 0">NIP: {{ $r['nomor_pegawai'] }}</p>
                                @endif
                            </td>
                            <td style="font-size:12.5px;color:#374151">{{ $r['department'] }}</td>
                            <td>
                                <span class="badge badge-blue" style="font-size:11.5px;font-weight:700">📍 {{ $r['destination'] }}</span>
                            </td>
                            <td style="text-align:center;{{ $r['breakfast'] !== '-' ? 'background:#eff6ff;font-weight:700;color:#1d4ed8' : 'color:#9ca3af' }}">
                                {{ $r['breakfast'] }}
                            </td>
                            <td style="text-align:center;{{ $r['lunch'] !== '-' ? 'background:#eff6ff;font-weight:700;color:#1d4ed8' : 'color:#9ca3af' }}">
                                {{ $r['lunch'] }}
                            </td>
                            <td style="text-align:center;{{ $r['dinner'] !== '-' ? 'background:#eff6ff;font-weight:700;color:#1d4ed8' : 'color:#9ca3af' }}">
                                {{ $r['dinner'] }}
                            </td>
                            <td style="text-align:center;{{ $r['supper'] !== '-' ? 'background:#eff6ff;font-weight:700;color:#1d4ed8' : 'color:#9ca3af' }}">
                                {{ $r['supper'] }}
                            </td>
                            <td style="text-align:center;font-weight:800;color:#1e3a8a;background:#f8fafc">
                                {{ $r['total_pax'] }} pax
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    @endforeach

                    @if(!$hasPrintedAny)
                    <tr>
                        <td colspan="9" style="text-align:center;padding:36px;color:#9ca3af">
                            Tidak ada pesanan makanan yang diantar ke kantor / SP / luar untuk tanggal ini.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filterDeliveryTable() {
    var input = document.getElementById('deliveryFilter');
    var filter = input.value.toLowerCase();
    var table = document.getElementById('deliveryTable');
    var trs = table.getElementsByTagName('tr');

    for (var i = 1; i < trs.length; i++) {
        var td = trs[i].getElementsByTagName('td')[1]; // Nama Karyawan column
        if (td) {
            var txt = td.textContent || td.innerText;
            trs[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
        }
    }
}
</script>
@endpush
@endsection
