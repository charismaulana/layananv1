@extends('layouts.app')
@section('title','Kelola Pengguna')
@section('page-title','Kelola Pengguna')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div style="flex:1;min-width:240px;max-width:380px">
        <input type="text" id="userSearchInput" value="{{ request('search') }}"
               placeholder="🔍 Cari nama / fungsi..."
               class="form-input"
               oninput="filterUsersTable(this.value)"
               style="font-size:13px;padding:8px 14px">
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="font-weight:800;font-size:15px;padding:8px 18px;display:inline-flex;align-items:center;gap:4px">
        +
    </a>
</div>

<div class="card" style="border:1px solid #e2e8f0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden">
    <div style="overflow-x:auto">
        <table class="tbl" id="usersTable">
            <thead>
                <tr>
                    <th>Nama / Email</th>
                    <th>Fungsi</th>
                    <th>Role</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $usr)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:34px;height:34px;border-radius:50%;background:var(--g-lt);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--g);flex-shrink:0">{{ strtoupper(substr($usr->name,0,2)) }}</div>
                        <div>
                            <p style="font-weight:700;margin:0;font-size:13px;color:#1a2332">{{ $usr->name }}</p>
                            <p style="font-size:11px;color:var(--faint);margin:0">{{ $usr->email }}</p>
                        </div>
                    </div>
                </td>
                <td style="font-size:12.5px;color:#334155;font-weight:600">
                    {{ $usr->department?->name ?? ($usr->company_name ?: ($usr->company?->name ?? '-')) }}
                </td>
                <td><span class="badge badge-green">{{ $usr->role?->name ?? '-' }}</span></td>
                <td style="font-size:12px;color:var(--muted)">{{ $usr->homebaseRegion?->name ?? '-' }}</td>
                <td>
                    @if($usr->registration_status === 'pending_approval')
                        <span class="badge badge-gold">Menunggu</span>
                    @elseif(!$usr->is_active)
                        <span class="badge badge-red">Nonaktif</span>
                    @else
                        <span class="badge badge-green">Aktif</span>
                    @endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                        <a href="{{ route('admin.users.edit', $usr) }}" class="btn btn-secondary btn-sm">Edit</a>
                        @if($usr->registration_status === 'pending_approval')
                        <form method="POST" action="{{ route('admin.users.approve-contractor', $usr) }}">@csrf<button class="btn btn-primary btn-sm">✓ Setujui</button></form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr id="emptyRow"><td colspan="6" style="text-align:center;padding:32px;color:var(--faint)">Tidak ada pengguna ditemukan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:12px">{{ $users->links() }}</div>

@push('scripts')
<script>
function filterUsersTable(keyword) {
    var filter = keyword.toLowerCase().trim();
    var table = document.getElementById('usersTable');
    if (!table) return;
    var rows = table.querySelectorAll('tbody tr');
    var visibleCount = 0;

    rows.forEach(function(row) {
        if (row.id === 'emptyRow') return;
        var text = row.textContent || row.innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush
@endsection
