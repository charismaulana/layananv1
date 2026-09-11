@extends('layouts.app')
@section('title','Export Data')
@section('page-title','Export Data')
@section('content')
<div style="max-width:700px">

<div class="grid-2" style="gap:16px">

@php
    $exports = [
        ['key'=>'users',     'label'=>'Data Pengguna',       'desc'=>'Semua user terdaftar beserta role, wilayah, perusahaan',  'icon'=>'👥', 'color'=>'#1d4ed8','bg'=>'#eff6ff'],
        ['key'=>'roster',    'label'=>'Roster Karyawan',     'desc'=>'Rekapitulasi roster kerja/libur per periode',             'icon'=>'📅', 'color'=>'#006738','bg'=>'#e8f5ee'],
        ['key'=>'meal-plan', 'label'=>'Rencana Makan',       'desc'=>'Semua meal plan (active, cancelled, outside)',            'icon'=>'🍽',  'color'=>'#c2410c','bg'=>'#fff7ed'],
        ['key'=>'pob',       'label'=>'Predicted POB',       'desc'=>'Rekapitulasi Predicted POB per wilayah & jenis makan',   'icon'=>'📊', 'color'=>'#6d28d9','bg'=>'#f5f3ff'],
        ['key'=>'movement',  'label'=>'Movement Log',        'desc'=>'Riwayat permohonan movement (approved/rejected)',         'icon'=>'↔️', 'color'=>'#065f46','bg'=>'#ecfdf5'],
        ['key'=>'feedback',  'label'=>'Rating & Masukan',    'desc'=>'Rekapitulasi rating rasa katering & saran masukan karyawan', 'icon'=>'⭐', 'color'=>'#d97706','bg'=>'#fefce8'],
        ['key'=>'menu',      'label'=>'Rekap Menu Katering', 'desc'=>'Rekapitulasi daftar menu masakan harian yang diupload catering', 'icon'=>'📋', 'color'=>'#0284c7','bg'=>'#f0f9ff'],
        ['key'=>'activity',  'label'=>'Log Aktivitas',       'desc'=>'Audit trail semua aksi sistem',                          'icon'=>'🔍', 'color'=>'#374151','bg'=>'#f9fafb'],
    ];
@endphp

@foreach($exports as $exp)
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:16px 18px;background:{{ $exp['bg'] }};border-bottom:1px solid var(--border)">
        <div style="font-size:22px;margin-bottom:4px">{{ $exp['icon'] }}</div>
        <p style="font-size:14px;font-weight:700;color:{{ $exp['color'] }};margin:0">{{ $exp['label'] }}</p>
        <p style="font-size:11px;color:var(--muted);margin:2px 0 0">{{ $exp['desc'] }}</p>
    </div>
    <div style="padding:14px 18px">
        <form method="GET" action="{{ route('admin.export.download', $exp['key']) }}">
            <div style="display:flex;gap:8px;margin-bottom:10px">
                <div style="flex:1">
                    <label class="form-label" style="margin-bottom:4px">Dari</label>
                    <input type="date" name="from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="form-input">
                </div>
                <div style="flex:1">
                    <label class="form-label" style="margin-bottom:4px">Sampai</label>
                    <input type="date" name="to" value="{{ now()->format('Y-m-d') }}" class="form-input">
                </div>
            </div>
            <button type="submit" class="btn btn-outline btn-sm btn-full">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Excel (.xlsx)
            </button>
        </form>
    </div>
</div>
@endforeach

</div>
</div>
@endsection
