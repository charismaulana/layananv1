@extends('layouts.app')
@section('title','Dashboard Management')
@section('page-title','Dashboard Management')
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <form method="GET" style="display:flex;gap:8px">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-input">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
    <span style="font-size:13px;color:var(--muted)">Data per {{ $date->translatedFormat('l, d M Y') }}</span>
</div>

{{-- KPI Cards --}}
<div class="grid-4" style="margin-bottom:24px">
    @php
        $grandTotal = collect($pobSummary['rows'] ?? [])->sum('grand_total');
        $kpis = [
            ['label'=>'Total POB',          'value'=>$grandTotal,        'unit'=>'pax',    'color'=>'var(--g)',   'bg'=>'var(--g-lt)'],
            ['label'=>'Movement Hari Ini',  'value'=>$totalMovements,   'unit'=>'req',    'color'=>'#1d4ed8',   'bg'=>'#eff6ff'],
            ['label'=>'Outside Meal',       'value'=>$totalOutside,     'unit'=>'req',    'color'=>'#92400e',   'bg'=>'#fffbeb'],
            ['label'=>'Pembatalan',         'value'=>$totalCancelled,   'unit'=>'makan',  'color'=>'#E32529',   'bg'=>'#fef2f2'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="card" style="padding:18px">
        <p style="font-size:11px;font-weight:700;color:{{ $kpi['color'] }};text-transform:uppercase;margin:0 0 6px">{{ $kpi['label'] }}</p>
        <p style="font-size:30px;font-weight:800;color:{{ $kpi['color'] }};margin:0"><span>{{ $kpi['value'] ?? 0 }}</span><span style="font-size:13px;font-weight:500;margin-left:3px">{{ $kpi['unit'] }}</span></p>
    </div>
    @endforeach
</div>

{{-- POB per Region Table --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header"><h3>Predicted POB per Wilayah — {{ $date->translatedFormat('d M Y') }}</h3></div>
    <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr>
                <th>Wilayah</th>
                @foreach($pobSummary['mealTypes'] ?? [] as $mt)<th style="text-align:center">{{ $mt->name }}</th>@endforeach
                <th style="text-align:center">Total</th>
            </tr></thead>
            <tbody>
            @forelse($pobSummary['rows'] ?? [] as $row)
            <tr>
                <td style="font-weight:600">{{ $row['region']->name }}</td>
                @foreach($pobSummary['mealTypes'] ?? [] as $mt)
                <td style="text-align:center">{{ $row['by_type'][$mt->id] ?? 0 }}</td>
                @endforeach
                <td style="text-align:center;font-weight:700;color:var(--g)">{{ $row['grand_total'] }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;padding:24px;color:var(--faint)">Tidak ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 7-day Trend --}}
<div class="card">
    <div class="card-header"><h3>Tren POB 7 Hari Terakhir</h3></div>
    <div style="padding:20px">
        @php $maxPob = collect($trend)->max('pob') ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:8px;height:120px;border-bottom:1px solid var(--border)">
            @foreach($trend as $t)
            @php $h = max(4, ($t['pob'] / $maxPob) * 100); @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:4px">
                <span style="font-size:10px;font-weight:700;color:var(--g)">{{ $t['pob'] }}</span>
                <div style="width:100%;height:{{ $h }}%;background:var(--g);border-radius:4px 4px 0 0;opacity:.8;min-height:4px"></div>
            </div>
            @endforeach
        </div>
        <div style="display:flex;gap:8px;margin-top:6px">
            @foreach($trend as $t)
            <div style="flex:1;text-align:center;font-size:10px;color:var(--faint)">{{ $t['date'] }}</div>
            @endforeach
        </div>
    </div>
</div>
@endsection
