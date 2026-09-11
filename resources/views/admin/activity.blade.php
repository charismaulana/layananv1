@extends('layouts.app')
@section('title','Log Aktivitas')
@section('page-title','Log Aktivitas')
@section('content')
{{-- Filter --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding:12px 16px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="flex:1;min-width:140px"><label class="form-label" style="margin-bottom:4px">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau aksi..." class="form-input"></div>
            <div style="flex:1;min-width:130px"><label class="form-label" style="margin-bottom:4px">Dari</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
            <div style="flex:1;min-width:130px"><label class="form-label" style="margin-bottom:4px">Sampai</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.activity') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr>
                <th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Detail</th><th>IP</th>
            </tr></thead>
            <tbody>
            @forelse($activities as $act)
            <tr>
                <td style="white-space:nowrap">
                    <p style="font-size:12px;font-weight:600;margin:0">{{ $act->created_at->translatedFormat('d M Y') }}</p>
                    <p style="font-size:11px;color:var(--faint);margin:0">{{ $act->created_at->format('H:i:s') }}</p>
                </td>
                <td>
                    <p style="font-size:12px;font-weight:600;margin:0">{{ $act->user?->name ?? 'System' }}</p>
                    <p style="font-size:10px;color:var(--faint);margin:0">{{ $act->user?->role?->name }}</p>
                </td>
                <td>
                    @php
                        $actColor = match(true) {
                            str_contains($act->action,'create') || str_contains($act->action,'store') => 'badge-green',
                            str_contains($act->action,'delete') || str_contains($act->action,'cancel') => 'badge-red',
                            str_contains($act->action,'update') || str_contains($act->action,'approve') => 'badge-blue',
                            default => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $actColor }}" style="font-size:10px">{{ $act->action }}</span>
                </td>
                <td style="font-size:12px;color:var(--muted);max-width:220px">
                    <p style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($act->description ?? '-', 60) }}</p>
                </td>
                <td style="font-size:11px;color:var(--faint)">{{ $act->ip_address ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--faint)">Belum ada log aktivitas</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $activities->links() }}</div>
@endsection
