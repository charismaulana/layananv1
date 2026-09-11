@extends('layouts.app')
@section('title', 'Perpindahan Lokasi')
@section('page-title', 'Perpindahan Lokasi')

@section('content')
<div style="margin-bottom:24px">

    {{-- Top Action Bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap">
        <div>
            <p style="font-size:13px;color:#64748b;margin:0">
                Daftar permohonan perpindahan lokasi dan pemindahan porsi makan antar wilayah.
            </p>
        </div>
        <a href="{{ route('movement.create') }}" class="btn btn-primary" style="padding:9px 18px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
            Ajukan Perpindahan Lokasi
        </a>
    </div>

    {{-- Data Table Card --}}
    <div class="card" style="padding:0;overflow:hidden;border-radius:14px;background:#fff;border:1px solid #e2e8f0;border-left:4px solid #006738;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div style="overflow-x:auto">
            <table class="tbl" style="margin:0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th style="min-width:110px">Tanggal Movement</th>
                        <th style="min-width:180px">Rute Wilayah</th>
                        <th style="text-align:center;min-width:90px">Personel</th>
                        <th style="min-width:140px">Pemohon</th>
                        <th>Alasan & Keperluan Movement</th>
                        <th style="text-align:center;min-width:120px">Status</th>
                        <th style="text-align:center;width:90px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $idx => $mv)
                    @php
                        $statusBadge = match($mv->status) {
                            'approved' => 'badge-green',
                            'rejected' => 'badge-red',
                            default    => 'badge-gold',
                        };
                        $statusText = match($mv->status) {
                            'approved' => '✓ Disetujui',
                            'rejected' => '✕ Ditolak',
                            default    => '⏳ Menunggu GS',
                        };
                    @endphp
                    <tr>
                        <td style="text-align:center;font-weight:700;color:#64748b">
                            {{ $movements->firstItem() + $idx }}
                        </td>
                        <td style="font-size:12.5px;font-weight:700;color:#1e293b;white-space:nowrap">
                            {{ $mv->movement_date->translatedFormat('d M Y') }}
                            <div style="font-size:11px;font-weight:500;color:#64748b">{{ $mv->movement_date->translatedFormat('l') }}</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:#1a2332">
                                <span>{{ $mv->fromRegion->name }}</span>
                                <span style="color:#006738;font-size:12px">➔</span>
                                <span>{{ $mv->toRegion->name }}</span>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-blue" style="font-size:11px;font-weight:700">
                                {{ $mv->people->count() }} orang
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:12.5px;color:#1a2332">
                                {{ $mv->requester->name }}
                            </div>
                            <div style="font-size:11px;color:#64748b">
                                {{ $mv->requester->department?->name ?? $mv->requester->role?->name }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;color:#334155;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $mv->reason }}">
                                {{ $mv->reason }}
                            </div>
                        </td>
                        <td style="text-align:center">
                            <span class="badge {{ $statusBadge }}" style="font-size:11px;font-weight:700;padding:3px 8px">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('movement.show', $mv) }}" class="btn btn-secondary btn-sm" style="font-size:11.5px;padding:4px 10px;font-weight:700">
                                Detail →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:36px 20px">
                            <div style="font-size:24px;margin-bottom:6px">↔</div>
                            <p style="font-size:14px;font-weight:700;color:#374151;margin:0 0 4px">Belum Ada Permohonan Movement</p>
                            <p style="font-size:12px;color:#9ca3af;margin:0 0 14px">Jika Anda atau tim akan pindah lokasi tugas, ajukan permohonan movement untuk mengalihkan jatah makan.</p>
                            <a href="{{ route('movement.create') }}" class="btn btn-primary btn-sm" style="padding:6px 14px;font-weight:700">
                                Ajukan Perpindahan Lokasi
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div style="padding:12px 18px;border-top:1px solid #e2e8f0;background:#fafafa">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
