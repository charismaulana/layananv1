@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil & Identitas')

@section('content')
@php $user = auth()->user(); @endphp

{{-- Header Card with User info & Logout button --}}
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #006738;border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:50%;background:#006738;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#fff;flex-shrink:0">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <h2 style="font-size:16px;font-weight:800;color:#1a2332;margin:0">{{ $user->name }}</h2>
            <p style="font-size:11.5px;color:#15803d;margin:2px 0 0;font-weight:600">{{ $user->department?->name ?? $user->role?->name }} · {{ $user->homebaseRegion?->name ?? 'Field Ramba' }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:6px;padding:8px 16px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:9px;font-size:12.5px;font-weight:700;color:#E32529;cursor:pointer;transition:all .15s"
                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Keluar Akun
        </button>
    </form>
</div>

<div class="grid-2" style="max-width:960px;gap:20px">

    {{-- ── FORM EDIT IDENTITAS & PROFIL ── --}}
    <div class="card" style="background:#fff;border:1px solid #e8edf2;border-left:4px solid #006738;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.03);overflow:hidden">
        <div class="card-header">
            <h3>Ubah Profil & Identitas</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')

                {{-- 1. Data Kontak Dasar --}}
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email <span style="color:var(--red)">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                </div>

                <div class="grid-2" style="gap:12px">
                    <div class="form-group">
                        <label class="form-label">Nopeg / No SIM-L</label>
                        <input type="text" name="nomor_pegawai" value="{{ old('nomor_pegawai', $user->nomor_pegawai) }}" class="form-input" placeholder="Contoh: 754321 / SIML-123">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jabatan / Posisi</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" class="form-input" placeholder="Contoh: Operator / Pengawas">
                    </div>
                </div>

                {{-- 2. Identitas Organisasi & Wilayah --}}
                <div style="border-top:1px solid var(--border);margin:16px 0;padding-top:16px">
                    <p style="font-size:12.5px;font-weight:700;color:var(--text);margin:0 0 12px">Identitas Organisasi & Penempatan</p>

                    <div class="grid-2" style="gap:12px">
                        <div class="form-group">
                            <label class="form-label">Perusahaan</label>
                            <input type="text" name="company_name" class="form-input" placeholder="Contoh: PT Pertamina EP, PT Mitra, dll"
                                   value="{{ old('company_name', $user->company_name ?: $user->company?->name) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Departemen / Fungsi</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id', $user->department_id) == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Wilayah Homebase Utama <span style="color:var(--red)">*</span></label>
                        <select name="homebase_region_id" class="form-select" id="profileRegionSelect" onchange="onRegionChange()">
                            <option value="">-- Pilih Wilayah Homebase --</option>
                            @foreach($regions as $r)
                            <option value="{{ $r->id }}" data-slug="{{ $r->slug }}" {{ old('homebase_region_id', $user->homebase_region_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="form-hint">Wilayah utama tempat penugasan operasional Anda.</p>
                    </div>

                    {{-- Lokasi Mess Hall Absensi (Khusus Ramba: Mess Hall Staff vs Nonstaff, atau auto Bentayan/Mangunjaya/Kluang) --}}
                    <div class="form-group" id="messHallSection" style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:12px;margin-bottom:16px">
                        <label class="form-label" style="color:#166534;font-weight:700;margin-bottom:4px">🏢 Lokasi Kehadiran Absensi Mess Hall <span style="color:var(--red)">*</span></label>
                        <select name="meal_location_id" id="profileMessHallSelect" class="form-select" style="border-color:#86efac;background:#fff">
                            @foreach($mealLocations->filter(fn($l) => in_array($l->id, [1, 2, 5, 7, 9]) || in_array($l->slug, ['mess-hall-staff', 'mess-hall-nonstaff', 'mess-hall-bentayan', 'mess-hall-mangunjaya', 'mess-hall-kluang'])) as $loc)
                            <option value="{{ $loc->id }}" data-region-id="{{ $loc->region_id }}" data-region-slug="{{ $loc->region?->slug }}" data-name="{{ $loc->name }}"
                                {{ old('meal_location_id', $user->meal_location_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="form-hint" id="messHallHint" style="color:#166534;margin-top:4px">Untuk wilayah Ramba, pilih <strong>Mess Hall Staff</strong> atau <strong>Mess Hall Nonstaff</strong> untuk penetapan lembar absensi manifest.</p>
                    </div>

                    {{-- PREFERENSI LOKASI MAKAN PER WAKTU MAKAN --}}
                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:16px;margin:16px 0">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                            <span style="font-size:18px">🍽️</span>
                            <div>
                                <h4 style="font-size:13.5px;font-weight:800;color:#1e293b;margin:0">Preferensi Lokasi Makan Per Waktu Makan</h4>
                                <p style="font-size:11px;color:#64748b;margin:1px 0 0">Pilih lokasi makan (Mess Hall, SP Central, Pos Security, Kantor GS)</p>
                            </div>
                        </div>

                        <div class="grid-2" style="gap:12px">
                            {{-- 1. Sarapan --}}
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:12px;color:#c2410c">🍳 Sarapan (Breakfast)</label>
                                <select name="breakfast_location_id" class="form-select meal-loc-dropdown">
                                    <option value="">-- Standar Mess Hall --</option>
                                    @foreach($mealLocations as $loc)
                                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('breakfast_location_id', $user->breakfast_location_id ?? $user->meal_location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2. Makan Siang --}}
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:12px;color:#1d4ed8">🍲 Makan Siang (Lunch)</label>
                                <select name="lunch_location_id" class="form-select meal-loc-dropdown">
                                    <option value="">-- Standar Mess Hall --</option>
                                    @foreach($mealLocations as $loc)
                                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('lunch_location_id', $user->lunch_location_id ?? $user->meal_location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 3. Makan Malam --}}
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:12px;color:#6d28d9">🍛 Makan Malam (Dinner)</label>
                                <select name="dinner_location_id" class="form-select meal-loc-dropdown">
                                    <option value="">-- Standar Mess Hall --</option>
                                    @foreach($mealLocations as $loc)
                                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('dinner_location_id', $user->dinner_location_id ?? $user->meal_location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 4. Supper --}}
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label" style="font-size:12px;color:#9d174d">🌙 Supper (Malam Shift)</label>
                                <select name="supper_location_id" class="form-select meal-loc-dropdown">
                                    <option value="">-- Standar Mess Hall --</option>
                                    @foreach($mealLocations as $loc)
                                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('supper_location_id', $user->supper_location_id ?? $user->meal_location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Karyawan</label>
                        <select name="worker_status_id" class="form-select">
                            <option value="">-- Pilih Status Karyawan --</option>
                            @foreach($workerStatuses as $ws)
                            <option value="{{ $ws->id }}" {{ old('worker_status_id', $user->worker_status_id) == $ws->id ? 'selected' : '' }}>
                                {{ $ws->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kamar Mess --}}
                    <div class="form-group">
                        <label class="form-label">🛏️ Nomor Kamar Mess</label>
                        <select name="room_id" class="form-select">
                            <option value="">-- Pilih Kamar --</option>
                            @php
                                $roomsByRegion = $rooms->load('region')->groupBy('region_id');
                            @endphp
                            @foreach($roomsByRegion as $regionId => $regionRooms)
                                @php $regionName = $regionRooms->first()->region?->name ?? 'Lainnya'; @endphp
                                @php $byBlock = $regionRooms->groupBy('block'); @endphp
                                @foreach($byBlock as $block => $blockRooms)
                                    <optgroup label="{{ $regionName }}{{ $block ? ' – ' . $block : '' }}">
                                        @foreach($blockRooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id', $user->room_id) == $room->id ? 'selected' : '' }}>
                                            {{ $room->full_name }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endforeach
                        </select>
                        <p class="form-hint">Pilih kamar mess tempat Anda menginap. Daftar kamar dikelola oleh GS.</p>
                    </div>

                    {{-- 3. Jenis (Non-Shift vs Shift) --}}
                    <div class="form-group">
                        <label class="form-label">Tipe Jam Kerja</label>
                        <select name="is_shift" class="form-select">
                            <option value="0" {{ old('is_shift', $user->is_shift) ? '' : 'selected' }}>Non-Shift (Reguler)</option>
                            <option value="1" {{ old('is_shift', $user->is_shift) ? 'selected' : '' }}>Shift (Bergilir)</option>
                        </select>
                        <p class="form-hint" style="margin-top:6px">Pekerja <strong>Shift (Bergilir)</strong> dapat memilih jadwal <strong>Shift Pagi</strong> atau <strong>Shift Malam</strong> saat mengisi kalender roster.</p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="padding:12px;margin-top:16px">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan Identitas
                </button>
            </form>
        </div>
    </div>

    {{-- ── SIDEBAR: GANTI PASSWORD & RINGKASAN AKUN ── --}}
    <div style="display:flex;flex-direction:column;gap:18px">

        {{-- Ringkasan Identitas Saat Ini --}}
        <div class="card" style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #0284c7;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.03);overflow:hidden">
            <div class="card-header" style="background:#f1f5f9;border-bottom:1px solid #e2e8f0">
                <h3 style="color:#0369a1">Ringkasan Identitas Terdaftar</h3>
            </div>
            <div style="padding:0">
                @php
                    $rows = [
                        ['label'=>'Nopeg / No SIM-L', 'value'=>$user->nomor_pegawai ?? '-'],
                        ['label'=>'Perusahaan',       'value'=>$user->company_name ?: ($user->company?->name ?? '-')],
                        ['label'=>'Departemen',        'value'=>$user->department?->name ?? '-'],
                        ['label'=>'Wilayah Homebase',  'value'=>$user->homebaseRegion?->name ?? '-'],
                        ['label'=>'Status Karyawan',   'value'=>$user->workerStatus?->name ?? '-'],
                        ['label'=>'🛏️ Kamar Mess',    'value'=>$user->room?->full_name ?? '-'],
                        ['label'=>'Tipe Jam Kerja',    'value'=>$user->is_shift ? 'Shift (Bergilir)' : 'Non-Shift (Reguler)'],
                        ['label'=>'Hak Akses / Role',  'value'=>$user->role?->name ?? '-'],
                    ];
                @endphp
                @foreach($rows as $row)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 16px;border-bottom:1px solid #e2e8f0">
                    <span style="font-size:12px;color:var(--muted)">{{ $row['label'] }}</span>
                    <span style="font-size:12.5px;font-weight:600;color:var(--text);text-align:right">{{ $row['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Ganti Password Form --}}
        <div class="card" style="background:#fff;border:1px solid #e8edf2;border-left:4px solid #ea580c;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.03);overflow:hidden">
            <div class="card-header" style="background:#fff7ed;border-bottom:1px solid #fed7aa">
                <h3 style="color:#c2410c">Keamanan: Ganti Password</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.change.update') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-input" required autocomplete="new-password">
                        <p class="form-hint">Minimal 8 karakter.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-outline btn-full" style="padding:10px">
                        Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function updateShiftPill(isShift) {
    var p0 = document.getElementById('pill_non_shift');
    var p1 = document.getElementById('pill_shift');
    if (!p0 || !p1) return;

    if (isShift == 1) {
        p1.style.borderColor = '#006738';
        p1.style.background = '#e8f5ee';
        p1.querySelector('p').style.color = '#006738';

        p0.style.borderColor = '#e8edf2';
        p0.style.background = '#fff';
        p0.querySelector('p').style.color = '#374151';
    } else {
        p0.style.borderColor = '#006738';
        p0.style.background = '#e8f5ee';
        p0.querySelector('p').style.color = '#006738';

        p1.style.borderColor = '#e8edf2';
        p1.style.background = '#fff';
        p1.querySelector('p').style.color = '#374151';
    }
}

function onRegionChange() {
    var regSelect = document.getElementById('profileRegionSelect');
    if (!regSelect) return;
    var selectedOption = regSelect.options[regSelect.selectedIndex];
    var regId = regSelect.value;
    var regSlug = selectedOption ? selectedOption.getAttribute('data-slug') : '';

    var mhSelect = document.getElementById('profileMessHallSelect');
    var mhSection = document.getElementById('messHallSection');
    var mhHint = document.getElementById('messHallHint');

    if (!regId) {
        if (mhSection) mhSection.style.display = 'none';
        return;
    }

    if (mhSection) mhSection.style.display = 'block';

    var firstMatched = null;
    if (mhSelect) {
        var options = mhSelect.querySelectorAll('option');
        options.forEach(function(opt) {
            var optRegId = opt.getAttribute('data-region-id');
            if (optRegId == regId) {
                opt.style.display = 'block';
                if (!firstMatched) firstMatched = opt;
            } else {
                opt.style.display = 'none';
            }
        });

        // If current selected option is hidden, switch to first matched option
        var currentOpt = mhSelect.options[mhSelect.selectedIndex];
        if (!currentOpt || currentOpt.style.display === 'none') {
            if (firstMatched) firstMatched.selected = true;
        }

        if (regSlug === 'ramba') {
            if (mhHint) mhHint.innerHTML = 'Wilayah <strong>Ramba</strong> memiliki 2 Mess Hall: Pilih <strong>Mess Hall Staff</strong> (Pekerja Organik) atau <strong>Mess Hall Nonstaff</strong> (TKJP/Mitra) untuk lembar manifest.';
        } else {
            if (mhHint) mhHint.innerHTML = 'Otomatis terdaftar pada absensi <strong>' + (firstMatched ? firstMatched.text : 'Mess Hall Wilayah') + '</strong>.';
        }
    }

    filterProfileLocations();
}

function filterProfileLocations() {
    var regSelect = document.getElementById('profileRegionSelect');
    if (!regSelect) return;
    var regId = regSelect.value;

    var dropdowns = document.querySelectorAll('.meal-loc-dropdown');
    dropdowns.forEach(function(sel) {
        var options = sel.querySelectorAll('option');
        var isCurrentOptionValid = false;

        options.forEach(function(opt) {
            if (!opt.value) {
                // Standar Mess Hall option
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                if (sel.value === '' || sel.value === null) isCurrentOptionValid = true;
                return;
            }
            var optReg = opt.getAttribute('data-region');
            if (!regId || optReg == regId) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                if (sel.value == opt.value) isCurrentOptionValid = true;
            } else {
                opt.hidden = true;
                opt.disabled = true;
                opt.style.display = 'none';
            }
        });

        // If the previously selected option is not in the newly selected region, reset to default ("")
        if (!isCurrentOptionValid) {
            sel.value = '';
        }
    });
}
document.addEventListener('DOMContentLoaded', onRegionChange);
</script>
@endpush
@endsection
