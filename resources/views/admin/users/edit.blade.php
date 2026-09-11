@extends('layouts.app')
@section('title','Edit Pengguna')
@section('page-title','Edit Pengguna')
@section('content')
<div style="max-width:700px">

{{-- Status Banner (Contractor) --}}
@if($user->registration_status === 'pending_approval')
<div style="background:var(--gold-lt);border:1px solid #fde68a;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div>
        <span class="badge badge-gold">Menunggu Persetujuan Kontraktor</span>
        <p style="font-size:12px;color:#92400e;margin:4px 0 0">Akun kontraktor ini belum disetujui</p>
    </div>
    <div style="display:flex;gap:8px">
        <form method="POST" action="{{ route('admin.users.approve-contractor', $user) }}">@csrf<button class="btn btn-primary btn-sm">✓ Setujui</button></form>
        <form method="POST" action="{{ route('admin.users.reject-contractor', $user) }}">@csrf<button class="btn btn-danger btn-sm">✕ Tolak</button></form>
    </div>
</div>
@endif

<div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
        <h3 style="margin:0;font-size:15px;font-weight:800;color:#1a2332">Edit: {{ $user->name }}</h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm" style="background:#f8fafc;font-weight:600">Kembali</a>
    </div>
    <div class="card-body" style="padding:20px">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="grid-2" style="gap:14px">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" style="background:#fff;border-color:#cbd5e1" required>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" style="background:#fff;border-color:#cbd5e1" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Role</label>
                    <select name="role_id" class="form-select" style="background:#fff;border-color:#cbd5e1" required>
                        @foreach($roles as $r)<option value="{{ $r->id }}" {{ (old('role_id',$user->role_id)) == $r->id ? 'selected':'' }}>{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Wilayah Homebase</label>
                    <select name="homebase_region_id" id="homebaseRegionSelectEdit" onchange="filterAdminLocationsEdit()" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">--</option>
                        @foreach($regions as $r)<option value="{{ $r->id }}" {{ old('homebase_region_id',$user->homebase_region_id) == $r->id ? 'selected':'' }}>{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Lokasi Kehadiran Absensi Mess Hall</label>
                    <select name="meal_location_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Otomatis Sesuai Wilayah --</option>
                        @foreach($mealLocations->filter(fn($l) => in_array($l->id, [1, 2, 5, 7, 9]) || in_array($l->slug, ['mess-hall-staff', 'mess-hall-nonstaff', 'mess-hall-bentayan', 'mess-hall-mangunjaya', 'mess-hall-kluang'])) as $loc)
                        <option value="{{ $loc->id }}" data-region-id="{{ $loc->region_id }}" {{ old('meal_location_id',$user->meal_location_id) == $loc->id ? 'selected':'' }}>{{ $loc->region?->name }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Perusahaan</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name ?: $user->company?->name) }}" class="form-input" style="background:#fff;border-color:#cbd5e1" placeholder="Contoh: PT Pertamina EP, PT Mitra, dll">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Departemen / Fungsi</label>
                    <select name="department_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Pilih Departemen / Fungsi --</option>
                        @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id',$user->department_id) == $d->id ? 'selected':'' }}>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Nopeg / No SIM-L</label>
                    <input type="text" name="nomor_pegawai" value="{{ old('nomor_pegawai',$user->nomor_pegawai) }}" class="form-input" style="background:#fff;border-color:#cbd5e1" placeholder="754321 / SIML-123">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan',$user->jabatan) }}" class="form-input" style="background:#fff;border-color:#cbd5e1">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Status Karyawan</label>
                    <select name="worker_status_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">--</option>
                        @foreach($workerStatuses as $ws)<option value="{{ $ws->id }}" {{ old('worker_status_id',$user->worker_status_id) == $ws->id ? 'selected':'' }}>{{ $ws->name }}</option>@endforeach
                    </select>
                </div>

                {{-- Tipe Jam Kerja tepat di bawah Status Karyawan --}}
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Tipe Jam Kerja</label>
                    <select name="is_shift" id="isShiftSelectEdit" class="form-select" onchange="toggleSupperPreferenceEdit()" style="background:#fff;border-color:#cbd5e1;font-weight:600">
                        <option value="0" {{ old('is_shift', $user->is_shift) ? '' : 'selected' }}>Non-Shift (Reguler Normal)</option>
                        <option value="1" {{ old('is_shift', $user->is_shift) ? 'selected' : '' }}>Shift (Pekerja Bergilir)</option>
                    </select>
                </div>

                {{-- Preferensi Lokasi Makan --}}
                <div class="form-group" style="grid-column:1/-1;background:#fff;border:1px solid #bbf7d0;border-radius:12px;padding:14px;margin:4px 0">
                    <p style="font-size:12.5px;font-weight:800;color:#15803d;margin:0 0 10px">🍽️ Preferensi Lokasi Tempat Makan</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:10px">
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#c2410c;font-weight:700">🍳 B'fast (Sarapan)</label>
                            <select name="breakfast_location_id" class="form-select admin-meal-loc-dropdown-edit" style="border-color:#fed7aa;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('breakfast_location_id', $user->breakfast_location_id) == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#1d4ed8;font-weight:700">🍲 Lunch (Siang)</label>
                            <select name="lunch_location_id" class="form-select admin-meal-loc-dropdown-edit" style="border-color:#bfdbfe;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('lunch_location_id', $user->lunch_location_id) == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#6d28d9;font-weight:700">🍛 Dinner (Malam)</label>
                            <select name="dinner_location_id" class="form-select admin-meal-loc-dropdown-edit" style="border-color:#e9d5ff;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('dinner_location_id', $user->dinner_location_id) == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div id="supperPrefBoxEdit" style="display:none">
                            <label class="form-label" style="font-size:11.5px;color:#9d174d;font-weight:700">🌙 Supper (Shift Malam)</label>
                            <select name="supper_location_id" id="supperLocationSelectEdit" class="form-select admin-meal-loc-dropdown-edit" style="border-color:#fbcfe8;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('supper_location_id', $user->supper_location_id) == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Status Akun --}}
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Status Akun</label>
                    <select name="is_active" class="form-select" style="background:#fff;border-color:#cbd5e1;font-weight:600">
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected':'' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $user->is_active) ? 'selected':'' }}>Nonaktif (Suspended)</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:14px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#16a34a;border-color:#16a34a;font-weight:800;padding:10px">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="padding:10px 18px;background:#fff">Batal</a>
            </div>
        </form>

        {{-- Reset Password --}}
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #bbf7d0">
            <p style="font-size:12.5px;font-weight:700;color:#15803d;margin-bottom:8px">Reset Password Pengguna</p>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" style="display:flex;gap:8px">
                @csrf @method('PUT')
                <input type="hidden" name="reset_password" value="1">
                <input type="text" name="new_password" class="form-input" placeholder="Password baru" style="flex:1;background:#fff;border-color:#cbd5e1">
                <button type="submit" class="btn btn-secondary btn-sm" style="background:#fff;font-weight:700" onclick="return confirm('Reset password user ini?')">Reset</button>
            </form>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
function toggleSupperPreferenceEdit() {
    var select = document.getElementById('isShiftSelectEdit');
    var supperBox = document.getElementById('supperPrefBoxEdit');
    var supperSelect = document.getElementById('supperLocationSelectEdit');
    
    if (select && supperBox) {
        if (select.value === '1') {
            supperBox.style.display = 'block';
        } else {
            supperBox.style.display = 'none';
            if (supperSelect) supperSelect.value = '';
        }
    }
}

function filterAdminLocationsEdit() {
    var regSelect = document.getElementById('homebaseRegionSelectEdit');
    if (!regSelect) return;
    var regId = regSelect.value;

    // Filter Mess Hall select
    var mhSelect = document.querySelector('select[name="meal_location_id"]');
    if (mhSelect) {
        var mhOptions = mhSelect.querySelectorAll('option');
        var isMhValid = false;
        mhOptions.forEach(function(opt) {
            if (!opt.value) { opt.hidden = false; opt.disabled = false; opt.style.display = ''; return; }
            var r = opt.getAttribute('data-region-id');
            if (!regId || r == regId) {
                opt.hidden = false; opt.disabled = false; opt.style.display = '';
                if (mhSelect.value == opt.value) isMhValid = true;
            } else {
                opt.hidden = true; opt.disabled = true; opt.style.display = 'none';
            }
        });
        if (!isMhValid) mhSelect.value = '';
    }

    // Filter the 4 meal preference selects
    var dropdowns = document.querySelectorAll('.admin-meal-loc-dropdown-edit');
    dropdowns.forEach(function(sel) {
        var options = sel.querySelectorAll('option');
        var isValid = false;
        options.forEach(function(opt) {
            if (!opt.value) { opt.hidden = false; opt.disabled = false; opt.style.display = ''; if (!sel.value) isValid = true; return; }
            var r = opt.getAttribute('data-region');
            if (!regId || r == regId) {
                opt.hidden = false; opt.disabled = false; opt.style.display = '';
                if (sel.value == opt.value) isValid = true;
            } else {
                opt.hidden = true; opt.disabled = true; opt.style.display = 'none';
            }
        });
        if (!isValid) sel.value = '';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    toggleSupperPreferenceEdit();
    filterAdminLocationsEdit();
});
</script>
@endpush
@endsection
