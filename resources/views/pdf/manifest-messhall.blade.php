<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manifest & Daftar Hadir — {{ $config['name'] }} ({{ $date->format('d/m/Y') }})</title>
<style>
    @page {
        margin: 30px 36px 20px 36px;
    }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 7pt;
        color: #0f172a;
        line-height: 1.15;
        margin: 0;
        padding: 0;
    }

    /* Header */
    .header {
        border-bottom: 2px solid #000000;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-table td {
        vertical-align: middle;
        padding: 0;
    }

    .company-title {
        font-size: 11pt;
        font-weight: 900;
        color: #000000;
        letter-spacing: .4px;
    }
    .doc-title {
        font-size: 9pt;
        font-weight: 800;
        color: #1e293b;
        margin-top: 1px;
    }
    .catering-vendor {
        font-size: 6.5pt;
        color: #475569;
        margin-top: 1px;
    }

    .location-badge {
        font-size: 10pt;
        font-weight: 900;
        color: #000000;
    }
    .date-text {
        font-size: 7.5pt;
        color: #334155;
        margin-top: 1px;
    }

    /* Summary Meta Cards */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }
    .meta-table td {
        background: #f8fafc;
        border: 1px solid #94a3b8;
        padding: 2.5px 5px;
    }
    .meta-label {
        font-size: 5.5pt;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .meta-val {
        font-size: 7.5pt;
        font-weight: 800;
        color: #000000;
        margin-top: 1px;
    }

    /* Color & B&W Legend Table */
    .legend-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
        font-size: 6pt;
    }
    .legend-table td {
        padding: 2px 3px;
        text-align: center;
        font-weight: 800;
        border: 1px solid #64748b;
    }

    /* Main Table (Optimized Columns Width) */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 6.8pt;
        table-layout: fixed;
    }
    .main-table th {
        background: #0f172a;
        color: #ffffff;
        font-weight: 800;
        font-size: 6.8pt;
        text-transform: uppercase;
        letter-spacing: .2px;
        padding: 4px 2px;
        border: 1px solid #000000;
        text-align: center;
        vertical-align: middle;
    }
    .main-table th.th-left {
        text-align: left;
        padding-left: 4px;
    }

    .main-table td {
        padding: 2.5px 3px;
        border: 1px solid #94a3b8;
        vertical-align: middle;
        height: 22.5px;
        word-wrap: break-word;
    }
    .main-table tr:nth-child(even) td:not(.cell-sign-active):not(.cell-delivery):not(.cell-req-other):not(.cell-outside):not(.cell-cancelled) {
        background: #f8fafc;
    }

    /* ── Cell Styling ── */
    .cell-sign-active {
        background: #ffffff !important;
        border: 1.5px solid #0f172a !important;
        text-align: center;
        vertical-align: middle;
    }
    .cell-delivery {
        background: #e2e8f0 !important;
        border: 1.5px solid #0284c7 !important;
        text-align: center;
        padding: 2px 1px !important;
    }
    .cell-req-other {
        background: #f1f5f9 !important;
        border: 1.5px dashed #64748b !important;
        text-align: center;
        padding: 2px 1px !important;
    }
    .cell-outside {
        background: #fef3c7 !important;
        border: 1.5px dotted #b45309 !important;
        text-align: center;
        padding: 2px 1px !important;
    }
    .cell-cancelled {
        background: #fee2e2 !important;
        border: 1.5px solid #b91c1c !important;
        text-align: center;
        padding: 2px 1px !important;
    }
    .cell-disabled {
        background: #ffffff !important;
        color: #94a3b8;
        text-align: center;
        font-weight: bold;
        font-size: 7.5pt;
    }

    .badge-delivery {
        font-size: 5.5pt;
        font-weight: 900;
        color: #0369a1;
        display: block;
        line-height: 1.1;
    }
    .badge-req {
        font-size: 5.5pt;
        font-weight: 800;
        color: #475569;
        display: block;
        line-height: 1.1;
        font-style: italic;
    }
    .badge-outside {
        font-size: 5.5pt;
        font-weight: 900;
        color: #92400e;
        display: block;
        line-height: 1.1;
    }
    .badge-cancelled {
        font-size: 5.5pt;
        font-weight: 900;
        color: #991b1b;
        display: block;
        line-height: 1.1;
        text-decoration: line-through;
    }

    /* Signatures Footer (2 Columns) */
    .signature-area {
        margin-top: 16px;
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .signature-area td {
        width: 50%;
        text-align: center;
        vertical-align: top;
    }
    .sign-title {
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 32px;
        font-size: 7.5pt;
    }
    .sign-name {
        font-weight: bold;
        color: #000000;
        border-top: 1px solid #000000;
        display: inline-block;
        width: 160px;
        padding-top: 3px;
        font-size: 7.5pt;
    }
</style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 58%;">
                    <div class="company-title">PT PERTAMINA EP — FIELD RAMBA</div>
                    <div class="doc-title">DAFTAR HADIR CATERING</div>
                    <div class="catering-vendor">Penyedia Katering: <strong>{{ $catering_vendor ?? \App\Services\ManifestService::getCateringVendorName() }}</strong></div>
                </td>
                <td style="width: 42%; text-align: right;">
                    <div class="location-badge">{{ $config['name'] }}</div>
                    <div class="date-text">Tanggal: <strong>{{ $date->translatedFormat('l, d F Y') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Summary Meta Table --}}
    <table class="meta-table">
        <tr>
            <td style="width: 33.33%;">
                <div class="meta-label">Wilayah Operasional</div>
                <div class="meta-val">{{ $config['region_name'] }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="meta-label">Lokasi Dapur / Ruang Makan</div>
                <div class="meta-val">{{ $config['location_name'] }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="meta-label">Total Karyawan Terdaftar (A-Z)</div>
                <div class="meta-val">{{ count($rows) }} orang</div>
            </td>
        </tr>
    </table>


    {{-- Main Table Sorted A-Z (Optimized Column Proportions: 100% Total) --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th class="th-left" style="width: 28%; white-space: nowrap;">Nama Karyawan</th>
                <th class="th-left" style="width: 10%;">Status</th>
                <th class="th-left" style="width: 14%;">Fungsi</th>
                <th style="width: 11%;">B'fast</th>
                <th style="width: 11%;">Lunch</th>
                <th style="width: 11%;">Dinner</th>
                <th style="width: 11%;">Supper</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            <tr>
                <td style="text-align: center; color: #000000; font-weight: bold;">{{ $r['number'] }}</td>
                <td>
                    <div style="font-weight: bold; color: #000000; font-size: 7.5pt;">{{ $r['name'] }}</div>
                    @if(!empty($r['jabatan']))
                    <div style="font-size: 6pt; color: #475569;">{{ $r['jabatan'] }}</div>
                    @endif
                </td>
                <td style="font-size: 6.5pt; color: #1e293b;">{{ $r['worker_status'] }}</td>
                <td style="font-size: 6.5pt; color: #1e293b;">{{ $r['department'] }}</td>

                {{-- 1. B'fast --}}
                @if($r['breakfast']['active'])
                    @if($r['breakfast']['type'] === 'messhall')
                        <td class="cell-sign-active"></td>
                    @elseif($r['breakfast']['type'] === 'outside_meal')
                        <td class="cell-outside">
                            <span class="badge-outside">[OUTSIDE]</span>
                        </td>
                    @elseif($r['breakfast']['type'] === 'other_messhall')
                        <td class="cell-req-other">
                            <span class="badge-req">[REQ] {{ str_replace('Req di ', '', $r['breakfast']['label']) }}</span>
                        </td>
                    @else
                        <td class="cell-delivery">
                            <span class="badge-delivery">[ANTAR]<br>{{ str_replace('Diantar: ', '', $r['breakfast']['label']) }}</span>
                        </td>
                    @endif
                @elseif($r['breakfast']['type'] === 'cancelled')
                    <td class="cell-cancelled">
                        <span class="badge-cancelled">[BATAL]</span>
                    </td>
                @else
                    <td class="cell-disabled">—</td>
                @endif

                {{-- 2. Lunch --}}
                @if($r['lunch']['active'])
                    @if($r['lunch']['type'] === 'messhall')
                        <td class="cell-sign-active"></td>
                    @elseif($r['lunch']['type'] === 'outside_meal')
                        <td class="cell-outside">
                            <span class="badge-outside">[OUTSIDE]</span>
                        </td>
                    @elseif($r['lunch']['type'] === 'other_messhall')
                        <td class="cell-req-other">
                            <span class="badge-req">[REQ] {{ str_replace('Req di ', '', $r['lunch']['label']) }}</span>
                        </td>
                    @else
                        <td class="cell-delivery">
                            <span class="badge-delivery">[ANTAR]<br>{{ str_replace('Diantar: ', '', $r['lunch']['label']) }}</span>
                        </td>
                    @endif
                @elseif($r['lunch']['type'] === 'cancelled')
                    <td class="cell-cancelled">
                        <span class="badge-cancelled">[BATAL]</span>
                    </td>
                @else
                    <td class="cell-disabled">—</td>
                @endif

                {{-- 3. Dinner --}}
                @if($r['dinner']['active'])
                    @if($r['dinner']['type'] === 'messhall')
                        <td class="cell-sign-active"></td>
                    @elseif($r['dinner']['type'] === 'outside_meal')
                        <td class="cell-outside">
                            <span class="badge-outside">[OUTSIDE]</span>
                        </td>
                    @elseif($r['dinner']['type'] === 'other_messhall')
                        <td class="cell-req-other">
                            <span class="badge-req">[REQ] {{ str_replace('Req di ', '', $r['dinner']['label']) }}</span>
                        </td>
                    @else
                        <td class="cell-delivery">
                            <span class="badge-delivery">[ANTAR]<br>{{ str_replace('Diantar: ', '', $r['dinner']['label']) }}</span>
                        </td>
                    @endif
                @elseif($r['dinner']['type'] === 'cancelled')
                    <td class="cell-cancelled">
                        <span class="badge-cancelled">[BATAL]</span>
                    </td>
                @else
                    <td class="cell-disabled">—</td>
                @endif

                {{-- 4. Supper --}}
                @if($r['supper']['active'])
                    @if($r['supper']['type'] === 'messhall')
                        <td class="cell-sign-active"></td>
                    @elseif($r['supper']['type'] === 'outside_meal')
                        <td class="cell-outside">
                            <span class="badge-outside">[OUTSIDE]</span>
                        </td>
                    @elseif($r['supper']['type'] === 'other_messhall')
                        <td class="cell-req-other">
                            <span class="badge-req">[REQ] {{ str_replace('Req di ', '', $r['supper']['label']) }}</span>
                        </td>
                    @else
                        <td class="cell-delivery">
                            <span class="badge-delivery">[ANTAR]<br>{{ str_replace('Diantar: ', '', $r['supper']['label']) }}</span>
                        </td>
                    @endif
                @elseif($r['supper']['type'] === 'cancelled')
                    <td class="cell-cancelled">
                        <span class="badge-cancelled">[BATAL]</span>
                    </td>
                @else
                    <td class="cell-disabled">—</td>
                @endif

            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Tidak ada data jatah makan terdaftar untuk mess hall ini pada tanggal tersebut.
                </td>
            </tr>
            @endforelse

            {{-- Kolom Kosong Tambahan (Spare Manual Write-In) --}}
            @php
                $totalRowsCount = count($rows);
                $spareRowsCount = ($totalRowsCount < 27) ? max(5, 27 - $totalRowsCount) : 5;
            @endphp
            @for($i = 1; $i <= $spareRowsCount; $i++)
            <tr style="height: 22px;">
                <td style="text-align: center; color: #000000; font-weight: bold; font-size: 7pt; border-bottom: 1px dotted #cbd5e1;">{{ $totalRowsCount + $i }}</td>
                <td style="border-bottom: 1px dotted #cbd5e1; font-size: 7pt; color: #cbd5e1;">&nbsp;</td>
                <td style="border-bottom: 1px dotted #cbd5e1;">&nbsp;</td>
                <td style="border-bottom: 1px dotted #cbd5e1;">&nbsp;</td>
                <td class="cell-sign-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1;"></td>
                <td class="cell-sign-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1;"></td>
                <td class="cell-sign-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1;"></td>
                <td class="cell-sign-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1;"></td>
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- Signatures Footer (2 Columns) --}}
    <table class="signature-area">
        <tr>
            <td>
                <div class="sign-title">Disiapkan Oleh (Penyedia Katering)</div>
                <div class="sign-name">{{ $catering_vendor ?? \App\Services\ManifestService::getCateringVendorName() }}</div>
                <div style="font-size:6.5pt;color:#64748b;margin-top:2px">Head Chef / Koordinator / Campboss</div>
            </td>
            <td>
                <div class="sign-title">Mengetahui</div>
                <div class="sign-name">{{ $gs_officer_name ?? \App\Services\ManifestService::getGsOfficerName() }}</div>
                <div style="font-size:6.5pt;color:#64748b;margin-top:2px">{{ $gs_officer_title ?? \App\Services\ManifestService::getGsOfficerTitle() }}</div>
            </td>
        </tr>
    </table>

    {{-- Document Timestamp Footer --}}
    <div style="margin-top: 16px; padding-top: 5px; border-top: 1px dashed #94a3b8; font-size: 6.5pt; color: #475569; text-align: right;">
        Dicetak pada tanggal: {{ now()->translatedFormat('d F Y') }} pukul {{ now()->format('H:i') }} WIB oleh {{ auth()->user()?->name ?? 'General Services' }}
    </div>

</body>
</html>
