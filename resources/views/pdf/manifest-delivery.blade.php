<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manifest Pengantaran - {{ $date->format('Y-m-d') }}</title>
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
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-table td {
        vertical-align: middle;
    }
    .company-title {
        font-size: 11pt;
        font-weight: 900;
        color: #000000;
        letter-spacing: 0.4px;
    }
    .doc-title {
        font-size: 9pt;
        font-weight: 800;
        color: #1e3a8a;
        margin-top: 1px;
        letter-spacing: 0.2px;
    }
    .catering-vendor {
        font-size: 6.5pt;
        color: #475569;
        margin-top: 1px;
    }
    .badge-header {
        display: inline-block;
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
        font-size: 7.5pt;
        font-weight: bold;
        padding: 2px 5px;
        border-radius: 4px;
        margin-bottom: 1px;
    }
    .date-text {
        font-size: 7.5pt;
        color: #334155;
    }

    /* Main Table */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 3px;
        font-size: 6.8pt;
        table-layout: fixed;
    }
    .main-table th {
        background: #1e3a8a;
        color: #ffffff;
        padding: 4px 2px;
        font-weight: 800;
        font-size: 6.8pt;
        text-align: center;
        border: 1px solid #1e3a8a;
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
        word-wrap: break-word;
        height: 22.5px;
    }
    .main-table tr:nth-child(even) {
        background: #f8fafc;
    }

    /* Cell Types for Meal Boxes */
    .cell-box-active {
        background: #e0f2fe !important;
        border: 1.5px solid #0284c7 !important;
        text-align: center;
        color: #0369a1;
        font-weight: 900;
        font-size: 6.5pt;
    }
    .cell-disabled {
        background: #ffffff !important;
        color: #94a3b8;
        text-align: center;
        font-weight: bold;
        font-size: 7.5pt;
    }

    /* Signatures Footer (3 Columns) */
    .signature-area {
        margin-top: 16px;
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .signature-area td {
        width: 33.33%;
        text-align: center;
        vertical-align: top;
        padding: 0 4px;
    }
    .sign-title {
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 32px;
        font-size: 7pt;
    }
    .sign-name {
        font-weight: bold;
        color: #000000;
        border-top: 1px solid #000000;
        display: inline-block;
        width: 150px;
        padding-top: 3px;
        font-size: 7pt;
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
                    <div class="doc-title">MANIFEST PENGANTARAN MAKANAN — WILAYAH {{ strtoupper($target_region_name ?? 'RAMBA') }}</div>
                    <div class="catering-vendor">Penyedia Katering: <strong>{{ $catering_vendor ?? \App\Services\ManifestService::getCateringVendorName() }}</strong></div>
                </td>
                <td style="width: 42%; text-align: right;">
                    <div class="badge-header">Wilayah {{ $target_region_name ?? 'Ramba' }}</div>
                    <div class="date-text">Tanggal: <strong>{{ $date->translatedFormat('l, d F Y') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Main Table (Optimized Columns Width) --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th class="th-left" style="width: 24%; white-space: nowrap;">Nama Karyawan</th>
                <th class="th-left" style="width: 12%;">Fungsi</th>
                <th class="th-left" style="width: 20%;">Tujuan Pengantaran</th>
                <th style="width: 10%;">B'fast</th>
                <th style="width: 10%;">Lunch</th>
                <th style="width: 10%;">Dinner</th>
                <th style="width: 10%;">Supper</th>
            </tr>
        </thead>
        <tbody>
            @php $hasPrintedAny = false; $rowNum = 1; @endphp
            @foreach($regions_data as $regName => $regData)
                @if(count($regData['rows']) > 0)
                    @php $hasPrintedAny = true; @endphp
                    @if(count($regions_data) > 1)
                    <tr style="background:#e2e8f0;">
                        <td colspan="8" style="padding: 4px 6px; font-weight: bold; color: #1e3a8a; font-size: 7.5pt;">
                            📍 WILAYAH: {{ strtoupper($regName) }} ({{ count($regData['rows']) }} Karyawan Terdaftar Pengantaran)
                        </td>
                    </tr>
                    @endif

                    @foreach($regData['rows'] as $r)
                    <tr>
                        <td style="text-align: center; color: #000000; font-weight: bold;">{{ $rowNum++ }}</td>
                        <td>
                            <div style="font-weight: bold; color: #000000; font-size: 7.5pt;">{{ $r['name'] }}</div>
                            @if(!empty($r['jabatan']))
                            <div style="font-size: 6pt; color: #475569;">{{ $r['jabatan'] }}</div>
                            @endif
                        </td>
                        <td style="font-size: 6.5pt; color: #1e293b;">{{ $r['department'] }}</td>
                        <td style="font-size: 6.5pt; color: #0369a1; font-weight: bold;">
                            {{ $r['destination'] ?? 'Lokasi Kerja' }}
                        </td>

                        {{-- B'fast --}}
                        @if($r['breakfast'] !== '-')
                            <td class="cell-box-active">1 Porsi</td>
                        @else
                            <td class="cell-disabled">—</td>
                        @endif

                        {{-- Lunch --}}
                        @if($r['lunch'] !== '-')
                            <td class="cell-box-active">1 Porsi</td>
                        @else
                            <td class="cell-disabled">—</td>
                        @endif

                        {{-- Dinner --}}
                        @if($r['dinner'] !== '-')
                            <td class="cell-box-active">1 Porsi</td>
                        @else
                            <td class="cell-disabled">—</td>
                        @endif

                        {{-- Supper --}}
                        @if($r['supper'] !== '-')
                            <td class="cell-box-active">1 Porsi</td>
                        @else
                            <td class="cell-disabled">—</td>
                        @endif

                    </tr>
                    @endforeach
                @endif
            @endforeach

            @if(!$hasPrintedAny)
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Tidak ada jadwal pengantaran makanan (drop point) untuk tanggal ini.
                </td>
            </tr>
            @endif

            {{-- Kolom Kosong Tambahan (Spare Manual Write-In) --}}
            @php
                $totalDeliveryRows = count($rows);
                $spareDeliveryCount = ($totalDeliveryRows < 27) ? max(5, 27 - $totalDeliveryRows) : 5;
            @endphp
            @for($i = 1; $i <= $spareDeliveryCount; $i++)
            <tr style="height: 22px;">
                <td style="text-align: center; color: #000000; font-weight: bold; font-size: 7pt; border-bottom: 1px dotted #cbd5e1;">{{ $totalDeliveryRows + $i }}</td>
                <td style="border-bottom: 1px dotted #cbd5e1; font-size: 7pt; color: #cbd5e1;">&nbsp;</td>
                <td style="border-bottom: 1px dotted #cbd5e1;">&nbsp;</td>
                <td style="border-bottom: 1px dotted #cbd5e1;">&nbsp;</td>
                <td class="cell-box-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1; color:#94a3b8; font-weight:normal;">&nbsp;</td>
                <td class="cell-box-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1; color:#94a3b8; font-weight:normal;">&nbsp;</td>
                <td class="cell-box-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1; color:#94a3b8; font-weight:normal;">&nbsp;</td>
                <td class="cell-box-active" style="background:#fff; border-bottom: 1px dotted #cbd5e1; color:#94a3b8; font-weight:normal;">&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- Signatures Footer (3 Columns) --}}
    <table class="signature-area">
        <tr>
            <td>
                <div class="sign-title">Disiapkan Oleh (Penyedia Katering)</div>
                <div class="sign-name">{{ $catering_vendor ?? \App\Services\ManifestService::getCateringVendorName() }}</div>
                <div style="font-size:6.5pt;color:#64748b;margin-top:2px">Driver / Ekspedisi Katering</div>
            </td>
            <td>
                <div class="sign-title">Diterima Oleh (PIC Lokasi / Security)</div>
                <div class="sign-name">PIC Penerima Lokasi</div>
                <div style="font-size:6.5pt;color:#64748b;margin-top:2px">Nama & Tanda Tangan</div>
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
