@extends('layouts.app')
@section('title', 'Pembatalan Makan')
@section('page-title', 'Batalkan Makan')

@section('content')
@php
    $user = auth()->user();
    $canCancel = !$isCutoffPassed || $user->isGS() || $user->isSysAdmin();
@endphp
<div style="max-width:500px">
    <div class="card">
        <div class="card-header">
            <h3>Konfirmasi Pembatalan</h3>
            <a href="{{ route('meal-plan.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
        </div>
        <div class="card-body">
            {{-- Meal Plan Info --}}
            <div style="background:var(--red-lt);border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px">
                <p style="font-size:12px;color:#9b1c1c;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Makan yang akan dibatalkan</p>
                <div style="display:flex;align-items:center;gap:12px">
                    <div>
                        <p style="font-size:15px;font-weight:700;color:var(--red);margin:0">{{ $mealPlan->mealType->name }}</p>
                        <p style="font-size:12px;color:#6b7280;margin:0">{{ $mealPlan->meal_date->translatedFormat('l, d M Y') }}</p>
                        <p style="font-size:12px;color:#6b7280;margin:0">{{ $mealPlan->mealLocation?->name ?? $mealPlan->region?->name }}</p>
                    </div>
                </div>
            </div>

            @if($isCutoffPassed)
                @if($user->isGS() || $user->isSysAdmin())
                <div style="background:var(--gold-lt);border:1px solid #fde68a;border-radius:10px;padding:12px 14px;margin-bottom:20px">
                    <p style="font-size:12px;color:#92400e;margin:0">
                        <strong>⚡ GS Override:</strong> Cutoff sudah lewat. Sebagai General Services / Administrator, pembatalan Anda akan dicatat sebagai GS Override.
                    </p>
                </div>
                @else
                <div style="background:var(--red-lt);border:1px solid #fecaca;border-radius:10px;padding:12px 14px;margin-bottom:20px">
                    <p style="font-size:12px;color:#9b1c1c;margin:0">
                        <strong>⚠ Batas Waktu Berakhir:</strong> Waktu pembatalan mandiri untuk jadwal makan ini sudah lewat (Cut-off: H-1 pukul 19:00 WIB). Silakan hubungi General Services (GS) untuk permohonan pembatalan.
                    </p>
                </div>
                @endif
            @endif

            @if($canCancel)
            <form method="POST" action="{{ route('cancellation.store', $mealPlan) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Alasan Pembatalan <span style="font-size:11.5px;font-weight:400;color:var(--muted)">(Opsional)</span></label>
                    <textarea name="reason" class="form-textarea" rows="3"
                              placeholder="Tuliskan alasan jika ada (opsional)...">{{ old('reason') }}</textarea>
                </div>

                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-danger" style="flex:1"
                            onclick="return confirm('Yakin ingin membatalkan makan ini?')">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Konfirmasi Batalkan Makan
                    </button>
                    <a href="{{ route('meal-plan.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
            @else
            <div style="text-align:center;margin-top:10px">
                <a href="{{ route('meal-plan.index') }}" class="btn btn-secondary btn-full">Kembali ke Rencana Makan</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
