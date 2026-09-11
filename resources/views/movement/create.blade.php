@extends('layouts.app')
@section('title', 'Perpindahan Lokasi')
@section('page-title', 'Perpindahan Lokasi')

@section('content')
<div style="max-width:680px;margin:0 auto">
    <div class="card" style="background:#fff;border-radius:14px;border:1px solid #e8edf2;border-left:4px solid #006738;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;border-bottom:1px solid #e8edf2;padding:14px 20px">
            <div style="display:flex;align-items:center;gap:10px">
                <a href="{{ route('movement.index') }}" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:12px">← Kembali</a>
                <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Perpindahan Lokasi</h3>
            </div>
        </div>
        <div class="card-body" style="padding:22px">

            {{-- Notice Box / Penjelasan Permohonan Perpindahan Lokasi --}}
            <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 16px;margin-bottom:20px">
                <div style="font-size:12.5px;color:#166534;line-height:1.5">
                    <strong style="color:#15803d">Fungsi Perpindahan Lokasi:</strong><br>
                    Permohonan ini bertujuan untuk <strong>memindahkan alokasi porsi makan</strong> pekerja ke <strong>Mess Hall struktur wilayah tujuan</strong> saat bertugas antar lapangan (Ramba, Bentayan, Mangunjaya, Kluang). Setelah disetujui GS, dapur di lokasi tujuan akan memasak porsi Anda dan nama pekerja otomatis terdaftar pada lembar absensi manifest wilayah baru.
                    <div style="margin-top:6px;font-size:11.5px;color:#166534">
                        <em>Batas pengajuan reguler adalah sebelum batas waktu Cut-Off (H-1 pukul 19:00 WIB).</em>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('movement.store') }}" id="movementForm">
                @csrf

                {{-- 1. Tanggal Movement --}}
                <div class="form-group">
                    <label class="form-label">Tanggal Pelaksanaan Movement <span style="color:var(--red)">*</span></label>
                    <input type="date" name="movement_date"
                           min="{{ now()->format('Y-m-d') }}"
                           value="{{ old('movement_date', now()->addDay()->format('Y-m-d')) }}"
                           class="form-input" required style="font-weight:600">
                </div>

                {{-- 2. Wilayah Asal & Tujuan --}}
                <div class="grid-2" style="gap:14px;margin-bottom:18px">
                    <div>
                        <label class="form-label">Dari Wilayah (Asal) <span style="color:var(--red)">*</span></label>
                        <select name="from_region_id" id="fromRegion" class="form-select" required>
                            @foreach($regions as $r)
                            <option value="{{ $r->id }}" {{ old('from_region_id', auth()->user()->homebase_region_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ke Wilayah (Tujuan) <span style="color:var(--red)">*</span></label>
                        <select name="to_region_id" id="toRegion" class="form-select" required>
                            @foreach($regions as $r)
                            <option value="{{ $r->id }}" {{ old('to_region_id') == $r->id ? 'selected' : ($loop->iteration == 2 ? 'selected' : '') }}>
                                {{ $r->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. Jenis Makan yang Dipindahkan --}}
                <div class="form-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <label class="form-label" style="margin:0">Jenis Makan yang Dipindahkan ke Lokasi Baru</label>
                        <div style="display:flex;gap:8px">
                            <button type="button" onclick="toggleAllMeals(true)" style="background:none;border:none;color:#006738;font-size:11px;font-weight:600;cursor:pointer;padding:0">Pilih Semua</button>
                            <span style="color:#d1d5db">|</span>
                            <button type="button" onclick="toggleAllMeals(false)" style="background:none;border:none;color:#6b7280;font-size:11px;font-weight:600;cursor:pointer;padding:0">Kosongkan</button>
                        </div>
                    </div>
                    <p class="form-hint" style="margin:0 0 10px">Pilih waktu makan mana saja yang akan diambil di lokasi tujuan (biarkan kosong / pilih semua jika seharian penuh):</p>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:10px" id="mealTypeContainer">
                        @foreach($mealTypes as $mt)
                        @php
                            $isOldChecked = is_array(old('meal_type_ids')) ? in_array($mt->id, old('meal_type_ids')) : true;
                        @endphp
                        <label id="labelMeal{{ $mt->id }}"
                               style="display:flex;align-items:center;gap:8px;padding:12px 14px;border:2px solid {{ $isOldChecked ? '#006738' : '#e8edf2' }};background:{{ $isOldChecked ? '#e8f5ee' : '#fff' }};border-radius:10px;cursor:pointer;user-select:none;transition:all .15s"
                               onclick="toggleMealCheckbox('{{ $mt->id }}')">
                            <input type="checkbox" name="meal_type_ids[]" value="{{ $mt->id }}" id="chkMeal{{ $mt->id }}"
                                   {{ $isOldChecked ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#006738;cursor:pointer;pointer-events:none">
                            <span style="font-size:13px;font-weight:700;color:{{ $isOldChecked ? '#006738' : '#374151' }}" id="txtMeal{{ $mt->id }}">
                                {{ $mt->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- 4. Pemilih Pekerja (People Picker) dengan Pencarian Nama Akurat --}}
                <div class="form-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <label class="form-label" style="margin:0">Daftar Pekerja yang Ikut Movement <span style="color:var(--red)">*</span></label>
                        <span id="selectedCountBadge" style="font-size:11.5px;font-weight:700;color:#006738;background:#e8f5ee;padding:2px 8px;border-radius:99px">1 pekerja dipilih</span>
                    </div>

                    {{-- Search Input Box --}}
                    <div style="position:relative;margin-bottom:10px">
                        <input type="text" id="userSearchInput" placeholder="🔍 Ketik nama, NIP, atau departemen pekerja..."
                               class="form-input" style="padding-left:38px" oninput="filterUserList()">
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="position:absolute;left:12px;top:50%;transform:translateY(-50%)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Selected Chips Area --}}
                    <div id="selectedChipsArea" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">
                        <!-- Populated via JavaScript -->
                    </div>

                    {{-- User List Box --}}
                    <div style="border:1.5px solid #e8edf2;border-radius:10px;max-height:240px;overflow-y:auto;background:#fff" id="userListBox">
                        @foreach($users as $u)
                        @php
                            $isSelf = $u->id === auth()->id();
                            $isOldUser = is_array(old('user_ids')) ? in_array($u->id, old('user_ids')) : $isSelf;
                        @endphp
                        <label class="user-row" id="userRow{{ $u->id }}"
                               data-id="{{ $u->id }}"
                               data-name="{{ strtolower($u->name) }}"
                               data-nip="{{ strtolower($u->nomor_pegawai ?? '') }}"
                               data-dept="{{ strtolower($u->department?->name ?? '') }}"
                               data-display-name="{{ $u->name }}"
                               data-display-info="{{ $u->nomor_pegawai ? $u->nomor_pegawai.' · ' : '' }}{{ $u->department?->name ?? $u->role?->name }}"
                               style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #f9fafb;cursor:pointer;transition:background .1s;{{ $isOldUser ? 'background:#f0fdf4;' : '' }}"
                               onclick="handleUserRowClick(event, '{{ $u->id }}')">
                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" id="chkUser{{ $u->id }}"
                                   {{ $isOldUser ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#006738;cursor:pointer;flex-shrink:0"
                                   onchange="syncSelectedUsers()">
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

                {{-- 5. Alasan & Keperluan Movement --}}
                <div class="form-group">
                    <label class="form-label">Alasan & Keperluan Movement <span style="color:var(--red)">*</span></label>
                    <textarea name="reason" class="form-textarea" rows="3" required
                              placeholder="Contoh: HSSE Committee, MWT, Audit Lapangan, Perbaikan Rig, dsb.">{{ old('reason') }}</textarea>
                </div>

                {{-- 6. Tombol Submit Kontras Tinggi --}}
                <div style="margin-top:24px;border-top:1px solid #e8edf2;padding-top:18px;display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary btn-full"
                            style="background:#006738;color:#ffffff;font-size:14px;font-weight:800;padding:13px 20px;border-radius:10px;box-shadow:0 4px 14px rgba(0,103,56,.25);cursor:pointer;border:none;letter-spacing:.3px"
                            onmouseover="this.style.background='#004d28'" onmouseout="this.style.background='#006738'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Permohonan Movement
                    </button>
                    <a href="{{ route('movement.index') }}" class="btn btn-secondary" style="padding:13px 20px">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── 1. Checkbox Toggle for Meal Types ──
function toggleMealCheckbox(id) {
    var chk = document.getElementById('chkMeal' + id);
    if (!chk) return;
    chk.checked = !chk.checked;
    updateMealStyle(id, chk.checked);
}

function updateMealStyle(id, isChecked) {
    var label = document.getElementById('labelMeal' + id);
    var txt = document.getElementById('txtMeal' + id);
    if (!label) return;
    if (isChecked) {
        label.style.borderColor = '#006738';
        label.style.background = '#e8f5ee';
        if (txt) txt.style.color = '#006738';
    } else {
        label.style.borderColor = '#e8edf2';
        label.style.background = '#fff';
        if (txt) txt.style.color = '#6b7280';
    }
}

function toggleAllMeals(selectAll) {
    var checkboxes = document.querySelectorAll('#mealTypeContainer input[type="checkbox"]');
    checkboxes.forEach(function(chk) {
        chk.checked = selectAll;
        updateMealStyle(chk.value, selectAll);
    });
}

// ── 2. Search & User Selection Logic ──
function filterUserList() {
    var query = document.getElementById('userSearchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.user-row');
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

function handleUserRowClick(event, id) {
    if (event.target.tagName === 'INPUT') return;
    var chk = document.getElementById('chkUser' + id);
    if (chk) {
        chk.checked = !chk.checked;
        syncSelectedUsers();
    }
}

function removeSelectedUser(id) {
    var chk = document.getElementById('chkUser' + id);
    if (chk) {
        chk.checked = false;
        syncSelectedUsers();
    }
}

function syncSelectedUsers() {
    var checkboxes = document.querySelectorAll('#userListBox input[type="checkbox"]');
    var selectedChips = [];
    var count = 0;

    checkboxes.forEach(function(chk) {
        var row = document.getElementById('userRow' + chk.value);
        if (chk.checked) {
            count++;
            if (row) row.style.background = '#f0fdf4';
            var name = row ? row.getAttribute('data-display-name') : 'User';
            selectedChips.push({ id: chk.value, name: name });
        } else {
            if (row) row.style.background = '#fff';
        }
    });

    // Update count badge
    var badge = document.getElementById('selectedCountBadge');
    if (badge) badge.textContent = count + ' pekerja dipilih';

    // Render chips
    var chipsArea = document.getElementById('selectedChipsArea');
    if (chipsArea) {
        chipsArea.innerHTML = '';
        selectedChips.forEach(function(item) {
            var chip = document.createElement('span');
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;background:#e8f5ee;border:1px solid #bbf7d0;color:#006738;padding:4px 10px;border-radius:99px;font-size:11.5px;font-weight:600';
            chip.innerHTML = item.name + ' <span style="cursor:pointer;font-weight:800;color:#dc2626;margin-left:2px" onclick="removeSelectedUser(\'' + item.id + '\')">✕</span>';
            chipsArea.appendChild(chip);
        });
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    syncSelectedUsers();
});
</script>
@endpush
@endsection
