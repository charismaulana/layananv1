@extends('layouts.app')
@section('title', 'Detail Outside Meal')
@section('page-title', 'Detail Outside Meal')

@section('content')
@php
    $canApprove = auth()->user()->isGS() || auth()->user()->isSysAdmin();
    $s = $outsideMeal->status;
    $statusConfig = [
        'pending'  => ['label'=>'Menunggu Persetujuan GS', 'class'=>'badge-gold',  'bg'=>'#fffbeb', 'border'=>'#fde68a', 'accent'=>'#d97706', 'icon'=>'⏳'],
        'approved' => ['label'=>'Disetujui',               'class'=>'badge-green', 'bg'=>'#f0fdf4', 'border'=>'#bbf7d0', 'accent'=>'#16a34a', 'icon'=>'✓'],
        'rejected' => ['label'=>'Ditolak',                 'class'=>'badge-red',   'bg'=>'#fef2f2', 'border'=>'#fecaca', 'accent'=>'#dc2626', 'icon'=>'✕'],
    ][$s] ?? ['label'=>$s, 'class'=>'badge-gray', 'bg'=>'#f9fafb', 'border'=>'#e5e7eb', 'accent'=>'#64748b', 'icon'=>'•'];
@endphp

<div style="max-width:720px;margin:0 auto">

    {{-- ── 1. Status Banner Card ── --}}
    <div style="background:{{ $statusConfig['bg'] }};border:1px solid {{ $statusConfig['border'] }};border-left:4px solid {{ $statusConfig['accent'] }};border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="badge {{ $statusConfig['class'] }}" style="font-size:11.5px;font-weight:700;padding:4px 10px">
                    {{ $statusConfig['icon'] }} {{ $statusConfig['label'] }}
                </span>
                <span style="font-size:12px;font-weight:600;color:#64748b">No. OM-{{ str_pad($outsideMeal->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            @if($outsideMeal->approver)
            <p style="font-size:11.5px;color:#475569;margin:4px 0 0">
                Diproses oleh: <strong>{{ $outsideMeal->approver->name }}</strong> ({{ $outsideMeal->approver->role?->name }}) · {{ $outsideMeal->updated_at->translatedFormat('d M Y H:i') }} WIB
            </p>
            @endif
        </div>

        {{-- GS Approval Actions --}}
        @if($canApprove && $s === 'pending')
        <div style="display:flex;gap:8px;align-items:center">
            <form method="POST" action="{{ route('outside-meal.approve', $outsideMeal) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"
                        style="background:#006738;color:#fff;font-weight:700;padding:8px 16px"
                        onclick="return confirm('Setujui permohonan outside meal ini?')">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Setujui Outside Meal
                </button>
            </form>
            <form method="POST" action="{{ route('outside-meal.reject', $outsideMeal) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm" style="font-weight:700;padding:8px 14px"
                        onclick="return confirm('Tolak permohonan outside meal ini?')">
                    ✕ Tolak
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- ── 2. Informasi Permohonan Card ── --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:20px">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Informasi Permohonan</h3>
            <span style="font-size:12px;font-weight:700;color:#64748b">{{ $outsideMeal->meal_date->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="card-body" style="padding:20px">
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Tanggal Pelaksanaan:</span>
                    <span style="font-weight:700;color:#1e293b">{{ $outsideMeal->meal_date->translatedFormat('d F Y') }}</span>
                </div>

                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Jenis Makan:</span>
                    <span class="badge badge-blue" style="font-size:11.5px;font-weight:700">{{ $outsideMeal->mealType->name }}</span>
                </div>

                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Diajukan Oleh:</span>
                    <span style="font-weight:600;color:#1e293b">
                        {{ $outsideMeal->requester->name }}
                        <span style="color:#64748b;font-weight:400">({{ $outsideMeal->requester->role?->name }} @if($outsideMeal->requester->department) · {{ $outsideMeal->requester->department->name }} @endif)</span>
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Waktu Pengajuan:</span>
                    <span style="color:#1e293b;font-weight:600">{{ $outsideMeal->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>

                <div>
                    <span style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:4px">Alasan:</span>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:10px 14px;font-size:13px;color:#334155;line-height:1.5">
                        {{ $outsideMeal->reason ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Daftar Peserta Card ── --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:24px">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Daftar Peserta Outside Meal</h3>
            <span style="font-size:11.5px;font-weight:700;color:#006738;background:#e8f5ee;padding:3px 10px;border-radius:99px">
                {{ $outsideMeal->people->count() }} orang
            </span>
        </div>
        <div style="padding:0">
            @foreach($outsideMeal->people as $person)
            @php $u = $person->user; @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f1f5f9">
                <div style="width:36px;height:36px;border-radius:50%;background:#006738;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
                    {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                        <p style="font-size:13.5px;font-weight:700;color:#1e293b;margin:0">{{ $u->name }}</p>
                        <span class="badge badge-gray" style="font-size:10px">{{ $u->role?->name }}</span>
                    </div>
                    <p style="font-size:11.5px;color:#64748b;margin:2px 0 0">
                        {{ $u->nomor_pegawai ? 'NIP: '.$u->nomor_pegawai.' · ' : '' }}
                        {{ $u->department?->name ?? 'Pertamina EP' }}
                        @if($u->homebaseRegion) · Homebase: {{ $u->homebaseRegion->name }} @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <a href="{{ route('outside-meal.index') }}" class="btn btn-secondary btn-sm" style="padding:8px 16px">
            Kembali
        </a>
        <a href="{{ route('outside-meal.create') }}" class="btn btn-outline btn-sm" style="border-color:#006738;color:#006738;font-weight:700;padding:8px 16px">
            + Buat Pengajuan Baru
        </a>
    </div>

</div>
@endsection
