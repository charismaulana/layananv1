@extends('layouts.app')
@section('title', 'Outside Meal')
@section('page-title', 'Outside Meal')

@section('content')
<div style="max-width:680px;margin:0 auto">
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px">
            <div style="display:flex;align-items:center;gap:10px">
                <a href="{{ route('outside-meal.index') }}" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:12px">Kembali</a>
                <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Outside Meal</h3>
            </div>
        </div>
        <div class="card-body" style="padding:22px">

            {{-- Notice Box --}}
            <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 16px;margin-bottom:20px">
                <div style="font-size:12.5px;color:#166534;line-height:1.5">
                    <strong style="color:#15803d">Ketentuan Outside Meal:</strong><br>
                    Apabila Anda telah memesan konsumsi khusus untuk kegiatan operasional atau acara melalui tim GS, mohon kesediaannya mengajukan permohonan Outside Meal sebelum batas waktu cut-off (H-1 pukul 19:00 WIB). Konfirmasi Anda sangat membantu pihak katering agar tidak menyiapkan porsi berlebih demi mencegah terjadinya <em>food waste</em>.
                </div>
            </div>

            <form method="POST" action="{{ route('outside-meal.store') }}" id="outsideMealForm">
                @csrf

                <div class="grid-2" style="gap:14px;margin-bottom:18px">
                    {{-- 1. Tanggal --}}
                    <div>
                        <label class="form-label">Tanggal Pelaksanaan <span style="color:var(--red)">*</span></label>
                        <input type="date" name="meal_date"
                               min="{{ now()->format('Y-m-d') }}"
                               value="{{ old('meal_date', now()->addDay()->format('Y-m-d')) }}"
                               class="form-input" required style="font-weight:600">
                    </div>

                    {{-- 2. Jenis Makan --}}
                    <div>
                        <label class="form-label">Jenis Makan <span style="color:var(--red)">*</span></label>
                        <select name="meal_type_id" class="form-select" required>
                            @foreach($mealTypes as $mt)
                            <option value="{{ $mt->id }}" {{ old('meal_type_id') == $mt->id ? 'selected':'' }}>
                                {{ $mt->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. Alasan --}}
                <div class="form-group">
                    <label class="form-label">Alasan <span style="color:var(--red)">*</span></label>
                    <textarea name="reason" class="form-textarea" rows="3" required
                              placeholder="Contoh: Tie in, moving rig...">{{ old('reason') }}</textarea>
                </div>

                {{-- 4. Pemilih Pekerja (People Picker) dengan Pencarian Nama Akurat --}}
                <div class="form-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <label class="form-label" style="margin:0">Daftar Pekerja yang Makan di Luar (Outside Meal) <span style="color:var(--red)">*</span></label>
                        <span id="omSelectedCountBadge" style="font-size:11.5px;font-weight:700;color:#006738;background:#e8f5ee;padding:2px 8px;border-radius:99px">1 pekerja dipilih</span>
                    </div>

                    {{-- Search Input Box --}}
                    <div style="position:relative;margin-bottom:10px">
                        <input type="text" id="omSearchInput" placeholder="🔍 Ketik nama, NIP, atau departemen pekerja..."
                               class="form-input" style="padding-left:38px" oninput="filterOmUserList()">
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="position:absolute;left:12px;top:50%;transform:translateY(-50%)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Selected Chips Area --}}
                    <div id="omSelectedChipsArea" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">
                        <!-- Populated via JavaScript -->
                    </div>

                    {{-- User List Box --}}
                    <div style="border:1.5px solid #e8edf2;border-radius:10px;max-height:240px;overflow-y:auto;background:#fff" id="omUserListBox">
                        @foreach($users as $u)
                        @php
                            $isSelf = $u->id === auth()->id();
                            $isOldUser = is_array(old('user_ids')) ? in_array($u->id, old('user_ids')) : $isSelf;
                        @endphp
                        <label class="om-user-row" id="omUserRow{{ $u->id }}"
                                data-id="{{ $u->id }}"
                                data-name="{{ strtolower($u->name) }}"
                                data-nip="{{ strtolower($u->nomor_pegawai ?? '') }}"
                                data-dept="{{ strtolower($u->department?->name ?? '') }}"
                                data-display-name="{{ $u->name }}"
                                style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #f9fafb;cursor:pointer;transition:background .1s;{{ $isOldUser ? 'background:#f0fdf4;' : '' }}"
                                onclick="handleOmUserRowClick(event, '{{ $u->id }}')">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" id="chkOmUser{{ $u->id }}"
                                   {{ $isOldUser ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#006738;cursor:pointer;flex-shrink:0"
                                   onchange="syncOmSelectedUsers()">
                            <div style="width:32px;height:32px;border-radius:50%;background:#006738;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <p style="font-size:13px;font-weight:600;color:#1a2332;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $u->name }} @if($isSelf) <span style="font-size:10px;color:#006738;font-weight:700">(Saya)</span> @endif
                                </p>
                                <p style="font-size:11px;color:#6b7280;margin:1px 0 0">
                                    {{ $u->nomor_pegawai ? $u->nomor_pegawai.' · ' : '' }}{{ $u->department?->name ?? $u->role?->name }}
                                    @if($u->homebaseRegion) · {{ $u->homebaseRegion->name }} @endif
                                </p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Tombol Submit Kontras Tinggi --}}
                <div style="margin-top:24px;border-top:1px solid #e8edf2;padding-top:18px;display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary btn-full"
                            style="background:#006738;color:#ffffff;font-size:14px;font-weight:800;padding:13px 20px;border-radius:10px;box-shadow:0 4px 14px rgba(0,103,56,.25);cursor:pointer;border:none;letter-spacing:.3px"
                            onmouseover="this.style.background='#004d28'" onmouseout="this.style.background='#006738'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Permohonan Outside Meal
                    </button>
                    <a href="{{ route('outside-meal.index') }}" class="btn btn-secondary" style="padding:13px 20px">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterOmUserList() {
    var query = document.getElementById('omSearchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.om-user-row');
    rows.forEach(function(row) {
        var name = row.getAttribute('data-name') || '';
        var nip = row.getAttribute('data-nip') || '';
        var dept = row.getAttribute('data-dept') || '';
        if (!query || name.includes(query) || nip.includes(query) || dept.includes(query)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}

function handleOmUserRowClick(event, id) {
    if (event.target.tagName === 'INPUT') return;
    var chk = document.getElementById('chkOmUser' + id);
    if (chk) {
        chk.checked = !chk.checked;
        syncOmSelectedUsers();
    }
}

function removeOmSelectedUser(id) {
    var chk = document.getElementById('chkOmUser' + id);
    if (chk) {
        chk.checked = false;
        syncOmSelectedUsers();
    }
}

function syncOmSelectedUsers() {
    var checkboxes = document.querySelectorAll('#omUserListBox input[type="checkbox"]');
    var selectedChips = [];
    var count = 0;

    checkboxes.forEach(function(chk) {
        var row = document.getElementById('omUserRow' + chk.value);
        if (chk.checked) {
            count++;
            if (row) row.style.background = '#f0fdf4';
            var name = row ? row.getAttribute('data-display-name') : 'User';
            selectedChips.push({ id: chk.value, name: name });
        } else {
            if (row) row.style.background = '#fff';
        }
    });

    var badge = document.getElementById('omSelectedCountBadge');
    if (badge) badge.textContent = count + ' pekerja dipilih';

    var chipsArea = document.getElementById('omSelectedChipsArea');
    if (chipsArea) {
        chipsArea.innerHTML = '';
        selectedChips.forEach(function(item) {
            var chip = document.createElement('span');
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:4px 10px;border-radius:99px;font-size:11.5px;font-weight:600';
            chip.innerHTML = item.name + ' <span style="cursor:pointer;font-weight:800;color:#dc2626;margin-left:2px" onclick="removeOmSelectedUser(\'' + item.id + '\')">✕</span>';
            chipsArea.appendChild(chip);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    syncOmSelectedUsers();
});
</script>
@endpush
@endsection
