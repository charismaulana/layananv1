@extends('layouts.app')
@section('title', 'Detail Perpindahan Lokasi')
@section('page-title', 'Detail Perpindahan Lokasi')

@section('content')
@php
    $canApprove = auth()->user()->isGS() || auth()->user()->isSysAdmin();
    $s = $movement->status;
    $statusConfig = [
        'pending'  => ['label'=>'Menunggu Persetujuan GS', 'class'=>'badge-gold',  'bg'=>'#fffbeb', 'border'=>'#fde68a', 'accent'=>'#d97706', 'icon'=>'⏳'],
        'approved' => ['label'=>'Disetujui',               'class'=>'badge-green', 'bg'=>'#f0fdf4', 'border'=>'#bbf7d0', 'accent'=>'#16a34a', 'icon'=>'✓'],
        'rejected' => ['label'=>'Ditolak',                 'class'=>'badge-red',   'bg'=>'#fef2f2', 'border'=>'#fecaca', 'accent'=>'#dc2626', 'icon'=>'✕'],
    ][$s] ?? ['label'=>$s, 'class'=>'badge-gray', 'bg'=>'#f9fafb', 'border'=>'#e5e7eb', 'accent'=>'#64748b', 'icon'=>'•'];

    // Meal types transferred
    $allMealTypes = \App\Models\MealType::active()->get();
    $movementMealTypeIds = $movement->people->pluck('meal_type_ids')->flatten()->unique()->filter()->toArray();
    $movementMealNames = $allMealTypes->whereIn('id', $movementMealTypeIds)->pluck('name');
@endphp

<div style="max-width:720px;margin:0 auto">

    {{-- ── 1. Top Navigation & Status Banner ── --}}
    <div style="background:{{ $statusConfig['bg'] }};border:1px solid {{ $statusConfig['border'] }};border-left:4px solid {{ $statusConfig['accent'] }};border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="badge {{ $statusConfig['class'] }}" style="font-size:11.5px;font-weight:700;padding:4px 10px">
                    {{ $statusConfig['icon'] }} {{ $statusConfig['label'] }}
                </span>
                <span style="font-size:12px;font-weight:600;color:#64748b">No. MV-{{ str_pad($movement->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            @if($movement->approver)
            <p style="font-size:11.5px;color:#475569;margin:4px 0 0">
                Diproses oleh: <strong>{{ $movement->approver->name }}</strong> ({{ $movement->approver->role?->name }}) · {{ $movement->updated_at->translatedFormat('d M Y H:i') }} WIB
            </p>
            @endif
        </div>

        {{-- GS Approval Actions --}}
        @if($canApprove && $movement->isPending())
        <div style="display:flex;gap:8px;align-items:center">
            <form method="POST" action="{{ route('movement.approve', $movement) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"
                        style="background:#006738;color:#fff;font-weight:700;padding:8px 16px"
                        onclick="return confirm('Setujui permohonan perpindahan lokasi ini? Jatah makan akan otomatis dialihkan ke lokasi baru.')">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Setujui Perpindahan
                </button>
            </form>
            <button type="button" onclick="toggleRejectForm()" class="btn btn-danger btn-sm" style="font-weight:700;padding:8px 14px">
                ✕ Tolak
            </button>
        </div>
        @endif
    </div>

    {{-- Rejection Input Form (Toggled for GS) --}}
    @if($canApprove && $movement->isPending())
    <div id="rejectFormBox" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:12px;padding:16px 18px;margin-bottom:20px;animation:fadeIn .2s ease-out">
        <h4 style="font-size:13px;font-weight:700;color:#991b1b;margin:0 0 8px">Konfirmasi Penolakan Perpindahan</h4>
        <form method="POST" action="{{ route('movement.reject', $movement) }}">
            @csrf
            <div class="form-group" style="margin-bottom:10px">
                <label class="form-label" style="color:#991b1b">Alasan Penolakan <span style="color:var(--red)">*</span></label>
                <textarea name="reason" rows="2" class="form-textarea" required
                          placeholder="Jelaskan alasan penolakan perpindahan ini agar pemohon mengetahui kendala..."></textarea>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-danger btn-sm" style="font-weight:700">Kirim Penolakan</button>
                <button type="button" onclick="toggleRejectForm()" class="btn btn-secondary btn-sm">Batal</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Rejection Reason Banner if already rejected --}}
    @if($movement->status === 'rejected' && $movement->rejection_reason)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:12px;padding:14px 18px;margin-bottom:20px">
        <p style="font-size:12px;font-weight:700;color:#991b1b;margin:0 0 2px">Alasan Penolakan:</p>
        <p style="font-size:13px;color:#7f1d1d;margin:0">{{ $movement->rejection_reason }}</p>
    </div>
    @endif

    {{-- ── 2. Detail Rute & Jadwal ── --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:20px">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Rute & Jadwal Perpindahan</h3>
            <span style="font-size:12px;font-weight:700;color:#64748b">{{ $movement->movement_date->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="card-body" style="padding:20px">
            {{-- Visual Route Box --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin-bottom:18px">
                <div style="text-align:center;flex:1">
                    <p style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;margin:0 0 2px">Wilayah Asal</p>
                    <p style="font-size:16px;font-weight:800;color:#1e293b;margin:0">{{ $movement->fromRegion->name }}</p>
                </div>
                <div style="width:38px;height:38px;border-radius:50%;background:#006738;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;box-shadow:0 2px 6px rgba(0,103,56,.25)">
                    ➔
                </div>
                <div style="text-align:center;flex:1">
                    <p style="font-size:10.5px;font-weight:700;color:#15803d;text-transform:uppercase;margin:0 0 2px">Wilayah Tujuan</p>
                    <p style="font-size:16px;font-weight:800;color:#006738;margin:0">{{ $movement->toRegion->name }}</p>
                    <p style="font-size:11px;color:#15803d;font-weight:600;margin:2px 0 0">Porsi makan dialihkan ke Mess Hall {{ $movement->toRegion->name }}</p>
                </div>
            </div>

            {{-- Detail Items --}}
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Waktu Makan Dialihkan:</span>
                    <div style="text-align:right">
                        @if($movementMealNames->isNotEmpty())
                            @foreach($movementMealNames as $mn)
                                <span class="badge badge-blue" style="font-size:11px;margin-left:4px">{{ $mn }}</span>
                            @endforeach
                        @else
                            <span class="badge badge-green" style="font-size:11px">Semua Waktu Makan (Seharian)</span>
                        @endif
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Diajukan Oleh:</span>
                    <span style="font-weight:600;color:#1e293b">
                        {{ $movement->requester->name }}
                        <span style="color:#64748b;font-weight:400">({{ $movement->requester->role?->name }} @if($movement->requester->department) · {{ $movement->requester->department->name }} @endif)</span>
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;font-size:13px">
                    <span style="color:#64748b">Waktu Pengajuan:</span>
                    <span style="color:#1e293b;font-weight:600">{{ $movement->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>

                <div>
                    <span style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:4px">Alasan & Keperluan Movement:</span>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:10px 14px;font-size:13px;color:#334155;line-height:1.5">
                        {{ $movement->reason }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Daftar Pekerja yang Dipindahkan ── --}}
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:24px">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Daftar Pekerja yang Dipindahkan</h3>
            <span style="font-size:11.5px;font-weight:700;color:#006738;background:#e8f5ee;padding:3px 10px;border-radius:99px">
                {{ $movement->people->count() }} orang
            </span>
        </div>
        <div style="padding:0">
            @foreach($movement->people as $person)
            @php
                $u = $person->user;
                $userMealNames = $allMealTypes->whereIn('id', $person->meal_type_ids ?? [])->pluck('name');
            @endphp
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
                @if($userMealNames->isNotEmpty())
                <div style="text-align:right;flex-shrink:0">
                    @foreach($userMealNames as $umn)
                        <span class="badge badge-blue" style="font-size:9.5px;padding:2px 6px">{{ $umn }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <a href="{{ route('movement.index') }}" class="btn btn-secondary btn-sm" style="padding:8px 16px">
            ← Kembali
        </a>
        <a href="{{ route('movement.create') }}" class="btn btn-outline btn-sm" style="border-color:#006738;color:#006738;font-weight:700;padding:8px 16px">
            + Buat Pengajuan Baru
        </a>
    </div>

</div>

@push('scripts')
<script>
function toggleRejectForm() {
    var box = document.getElementById('rejectFormBox');
    if (box) {
        box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
        if (box.style.display === 'block') {
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}
</script>
@endpush
@endsection
