<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manifest {{ $batch->manifest_number }}</title>
<style>
    @page { margin: 14mm 14mm 10mm 14mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Arial', sans-serif; font-size: 9px; color: #1a1a1a; }
    .header { border-bottom: 2px solid #1a5c2a; padding-bottom: 6px; margin-bottom: 8px; }
    .header-top { display: flex; align-items: flex-start; justify-content: space-between; }
    .company-info h1 { font-size: 14px; font-weight: bold; color: #1a5c2a; }
    .company-info p { font-size: 8.5px; color: #666; margin-top: 1px; }
    .manifest-info { text-align: right; }
    .manifest-info .number { font-size: 11px; font-weight: bold; color: #1a5c2a; font-family: monospace; }
    .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin: 6px 0; }
    .meta-box { background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 4px; padding: 4px 6px; }
    .meta-box .label { font-size: 7px; color: #888; text-transform: uppercase; letter-spacing: 0.3px; }
    .meta-box .value { font-size: 9.5px; font-weight: bold; color: #1a1a1a; margin-top: 1px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    thead { background: #1a5c2a; color: white; }
    thead th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody tr:hover { background: #f0faf0; }
    tbody td { padding: 4.5px 6px; border-bottom: 1px solid #e8e8e8; vertical-align: middle; }
    .no-col { width: 30px; text-align: center; color: #888; }
    .badge { display: inline-block; padding: 1.5px 6px; border-radius: 10px; font-size: 7.5px; font-weight: 500; }
    .badge-pep { background: #dcfce7; color: #166534; }
    .badge-kon { background: #dbeafe; color: #1e40af; }
    .badge-tkjp { background: #fef3c7; color: #92400e; }
    .signature-area { margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; page-break-inside: avoid; }
    .signature-box { border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; }
    .signature-box .title { font-size: 8.5px; font-weight: 600; color: #374151; margin-bottom: 32px; }
    .signature-box .line { border-top: 1px solid #9ca3af; padding-top: 3px; font-size: 7.5px; color: #6b7280; }
    .footer { margin-top: 8px; font-size: 7.5px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 4px; }
    .override-badge { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; font-size: 7.5px; padding: 2px 6px; border-radius: 4px; }
</style>
</head>
<body>
<div class="header">
    <div class="header-top">
        <div class="company-info">
            <h1>PT PERTAMINA EP — FIELD RAMBA</h1>
            <p>Ramba Meal Planning System — Manifest Rencana Makan</p>
            <p style="color:#475569; font-weight:600; margin-top:2px">Penyedia Katering: <strong>PT Brylian Indah</strong></p>
        </div>
        <div class="manifest-info">
            <div class="number">{{ $batch->manifest_number }}</div>
            @if($batch->is_override)
            <div style="margin-top:4px"><span class="override-badge">⚠ GS OVERRIDE</span></div>
            @endif
            <p style="font-size:8px; color:#888; margin-top:4px">Dibuat: {{ $batch->generated_at?->format('d/m/Y H:i') }} WIB</p>
            <p style="font-size:8px; color:#888">Oleh: {{ $batch->generatedBy?->name }}</p>
            <p style="font-size:8px; color:#888">Versi: {{ $batch->version }}</p>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-box">
            <div class="label">Tanggal</div>
            <div class="value">{{ $batch->manifest_date->translatedFormat('d M Y') }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Wilayah</div>
            <div class="value">{{ $batch->region->name }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Jenis Makan</div>
            <div class="value">{{ $batch->mealType->name }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Total PAX</div>
            <div class="value" style="color:#1a5c2a; font-size:14px">{{ $batch->total_pax }}</div>
        </div>
    </div>
    <p style="font-size:8.5px; color:#555">Lokasi: <strong>{{ $batch->mealLocation->name }}</strong></p>
    @if($batch->is_override && $batch->override_reason)
    <p style="font-size:8px; color:#dc2626; margin-top:4px">Alasan Override: {{ $batch->override_reason }}</p>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th class="no-col">No</th>
            <th>Nama Lengkap</th>
            <th>Jabatan</th>
            <th>Fungsi</th>
            <th>Status</th>
            <th>Jenis Pekerja</th>
            <th style="text-align:center">Paraf</th>
        </tr>
    </thead>
    <tbody>
        @foreach($batch->people as $person)
        <tr>
            <td class="no-col">{{ $person->sequence }}</td>
            <td style="font-weight:500">{{ $person->user->name }}</td>
            <td style="font-size:9px;color:#555">{{ $person->user->jabatan ?? '-' }}</td>
            <td style="color:#555">{{ $person->user->department?->name ?? '-' }}</td>
            <td>
                @php $role = $person->user->role?->slug; @endphp
                <span class="badge {{ in_array($role, ['pep','admin-departemen']) ? 'badge-pep' : ($role === 'kontraktor' ? 'badge-kon' : 'badge-tkjp') }}">
                    {{ $person->user->role?->name ?? '-' }}
                </span>
            </td>
            <td style="color:#555">{{ $person->user->workerStatus?->name ?? '-' }}</td>
            <td style="text-align:center; border-bottom: 1px solid #ccc; width:60px">&nbsp;</td>
        </tr>
        @endforeach

        {{-- Kolom Kosong Tambahan (Spare Manual Write-In) --}}
        @php
            $totalBatchRows = $batch->people->count();
            $spareBatchCount = ($totalBatchRows < 27) ? max(5, 27 - $totalBatchRows) : 5;
        @endphp
        @for($i = 1; $i <= $spareBatchCount; $i++)
        <tr style="height: 22px;">
            <td class="no-col" style="text-align: center; color: #000000; font-weight: bold; font-size: 8px; border-bottom: 1px dotted #ccc;">{{ $totalBatchRows + $i }}</td>
            <td style="border-bottom: 1px dotted #ccc;">&nbsp;</td>
            <td style="border-bottom: 1px dotted #ccc;">&nbsp;</td>
            <td style="border-bottom: 1px dotted #ccc;">&nbsp;</td>
            <td style="border-bottom: 1px dotted #ccc;">&nbsp;</td>
            <td style="border-bottom: 1px dotted #ccc;">&nbsp;</td>
            <td style="text-align:center; border-bottom: 1px dotted #ccc; width:60px">&nbsp;</td>
        </tr>
        @endfor
    </tbody>
</table>

<div class="signature-area">
    <div class="signature-box">
        <div class="title">Disiapkan Oleh (Penyedia Katering)</div>
        <div class="line">{{ $catering_vendor ?? \App\Services\ManifestService::getCateringVendorName() }}</div>
    </div>
    <div class="signature-box">
        <div class="title">Mengetahui (General Services)</div>
        <div class="line">{{ $gs_officer_name ?? \App\Services\ManifestService::getGsOfficerName() }}</div>
    </div>
</div>

<div class="footer">
    Dicetak pada tanggal: {{ now()->translatedFormat('d F Y') }} pukul {{ now()->format('H:i') }} WIB oleh {{ auth()->user()?->name ?? 'General Services' }} | Manifest No. {{ $batch->manifest_number }}
</div>
</body>
</html>
