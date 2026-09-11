@extends('layouts.app')
@section('title', 'Outside Meal')
@section('page-title', 'Outside Meal')

@section('content')
@php
    $user = auth()->user();
    $canApprove = $user->isGS() || $user->isSysAdmin();
    $statusClass = fn($s) => match($s) { 'approved'=>'badge-green','rejected'=>'badge-red',default=>'badge-gold' };
    $statusLabel = fn($s) => match($s) { 'approved'=>'Disetujui','rejected'=>'Ditolak',default=>'Menunggu' };
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div></div>
    <a href="{{ route('outside-meal.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajukan Outside Meal
    </a>
</div>

@if($records->isEmpty())
<div class="card">
    <div class="empty-state">
        <svg width="48" height="48" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        <p style="font-weight:600;color:#374151;margin-bottom:4px">Belum ada outside meal</p>
        <p>Klik tombol "Ajukan" untuk mengajukan permohonan</p>
    </div>
</div>
@else
<div class="card" style="padding:0;overflow:hidden;border-radius:14px;background:#fff;border:1px solid #e2e8f0;border-left:4px solid #006738;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div style="overflow-x:auto">
        <table class="tbl" style="margin:0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis Makan</th>
                    <th>Pengaju</th>
                    <th>Peserta</th>
                    <th>Status</th>
                    <th>Diproses oleh</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $om)
                <tr>
                    <td>
                        <p style="font-weight:600;margin:0">{{ $om->meal_date->translatedFormat('d M Y') }}</p>
                        <p style="font-size:11px;color:var(--faint);margin:0">Diajukan {{ $om->created_at->diffForHumans() }}</p>
                    </td>
                    <td><span class="badge badge-blue">{{ $om->mealType->name }}</span></td>
                    <td>{{ $om->requester->name }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px">
                            <span style="font-weight:600">{{ $om->people->count() }}</span>
                            <span style="font-size:11px;color:var(--faint)">orang</span>
                        </div>
                    </td>
                    <td><span class="badge {{ $statusClass($om->status) }}">{{ $statusLabel($om->status) }}</span></td>
                    <td style="font-size:12px;color:var(--muted)">{{ $om->approver?->name ?? '-' }}</td>
                    <td style="text-align:right">
                        <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                            <a href="{{ route('outside-meal.show', $om) }}" class="btn btn-secondary btn-sm">Detail</a>
                            @if($canApprove && $om->status === 'pending')
                            <form method="POST" action="{{ route('outside-meal.approve', $om) }}" style="display:inline">
                                @csrf
                                <button class="btn btn-primary btn-sm">✓ Setujui</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div>{{ $records->links() }}</div>
@endif
@endsection
