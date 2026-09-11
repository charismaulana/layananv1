@extends('layouts.app')
@section('title', 'Kartu Makan Digital')
@section('page-title', 'Kartu Makan')

@section('content')
@php $u = $user; @endphp

<div style="max-width:420px;margin:0 auto">

    {{-- Card --}}
    <div style="background:#006738;border-radius:20px;padding:24px;margin-bottom:20px;position:relative;overflow:hidden">
        {{-- Decorative circles --}}
        <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
        <div style="position:absolute;bottom:-60px;left:-20px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04)"></div>

        <div style="position:relative">
            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <div>
                    <p style="font-size:10px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1px;margin:0">Ramba Meal Planning System</p>
                    <p style="font-size:11px;color:rgba(255,255,255,.7);margin:2px 0 0">General Services — Field Ramba</p>
                </div>
                <div style="width:36px;height:36px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#ffffff;letter-spacing:0.5px">
                    GS
                </div>
            </div>

            {{-- QR Code --}}
            <div style="background:#fff;border-radius:14px;padding:16px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;min-height:200px">
                @if($qrSvg)
                {!! $qrSvg !!}
                @else
                <div style="text-align:center">
                    <svg width="64" height="64" fill="none" stroke="#e5e7eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <p style="font-size:12px;color:#9ca3af;margin:8px 0 0">QR Code tidak tersedia</p>
                </div>
                @endif
            </div>

            {{-- User Info --}}
            <div>
                <h2 style="font-size:18px;font-weight:800;color:white;margin:0 0 4px">{{ $u->name }}</h2>
                <p style="font-size:12px;color:rgba(255,255,255,.65);margin:0">{{ $u->role?->name }}</p>
                @if($u->company)
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:2px 0 0">{{ $u->company->name }}</p>
                @endif
                @if($u->nomor_pegawai)
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:2px 0 0">ID: {{ $u->nomor_pegawai }}</p>
                @endif
            </div>

            {{-- Token info --}}
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:10px;color:rgba(255,255,255,.4);margin:0">Token berlaku hingga</p>
                    <p style="font-size:12px;font-weight:600;color:rgba(255,255,255,.75);margin:0">
                        {{ $card->expires_at?->translatedFormat('d M Y H:i') ?? 'Tidak terbatas' }}
                    </p>
                </div>
                @if($card->expires_at?->isPast())
                <span class="badge badge-cancelled">Kedaluwarsa</span>
                @else
                <span class="badge badge-active" style="background:rgba(34,197,94,.2);color:#86efac">Aktif</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;margin-bottom:20px">
        <form method="POST" action="{{ route('meal-card.regenerate') }}" style="flex:1">
            @csrf
            <button type="submit" class="btn btn-outline btn-full" onclick="return confirm('Refresh token QR Code? Token lama tidak bisa digunakan.')">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Token
            </button>
        </form>
        <button onclick="window.print()" class="btn btn-primary" style="flex:1">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>

    {{-- Info --}}
    <div class="card">
        <div class="card-header"><h3>Cara Penggunaan</h3></div>
        <div style="padding:16px">
            @foreach([
                ['1','Tunjukkan QR Code ini kepada petugas catering saat mengambil makan'],
                ['2','Petugas akan scan QR Code untuk verifikasi jatah makan Anda'],
                ['3','Pastikan kartu ini sesuai nama dan tanggal berlaku'],
                ['4','Jika QR Code tidak terbaca, tunjukkan nama dan No. Pegawai'],
            ] as [$n, $t])
            <div style="display:flex;gap:10px;margin-bottom:10px;align-items:flex-start">
                <div style="width:22px;height:22px;border-radius:50%;background:#006738;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:white;flex-shrink:0">{{ $n }}</div>
                <p style="font-size:12px;color:#374151;margin:3px 0 0;line-height:1.5">{{ $t }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<style>
@media print {
    #sidebar, #topbar, #bottom-nav, .card-header a, form[method="POST"], .card:last-child { display:none!important; }
    #main-wrap { margin:0!important; padding:0!important; }
    body { background:white!important; }
}
</style>
@endpush
@endsection
