@extends('layouts.app')
@section('title','Tambah Pengguna Baru')
@section('page-title','Tambah Pengguna Baru')
@section('content')
<div style="max-width:650px;margin:0 auto">
<div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
        <h3 style="margin:0;font-size:15px;font-weight:800;color:#1a2332">Tambah Pengguna Baru</h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm" style="background:#f8fafc;font-weight:600">Kembali</a>
    </div>
    <div class="card-body" style="padding:20px">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="grid-2" style="gap:14px">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Nama Lengkap <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" required>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Email <span style="color:var(--red)">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Role <span style="color:var(--red)">*</span></label>
                    <select name="role_id" class="form-select" style="background:#fff;border-color:#cbd5e1" required>
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)<option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected':'' }}>{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Wilayah Homebase</label>
                    <select name="homebase_region_id" id="homebaseRegionSelect" onchange="filterAdminLocations()" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($regions as $r)<option value="{{ $r->id }}" {{ old('homebase_region_id') == $r->id ? 'selected':'' }}>{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Lokasi Kehadiran Absensi Mess Hall</label>
                    <select name="meal_location_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Otomatis Sesuai Wilayah --</option>
                        @foreach($mealLocations->filter(fn($l) => in_array($l->id, [1, 2, 5, 7, 9]) || in_array($l->slug, ['mess-hall-staff', 'mess-hall-nonstaff', 'mess-hall-bentayan', 'mess-hall-mangunjaya', 'mess-hall-kluang'])) as $loc)
                        <option value="{{ $loc->id }}" data-region-id="{{ $loc->region_id }}" {{ old('meal_location_id') == $loc->id ? 'selected':'' }}>{{ $loc->region?->name }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Perusahaan</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" placeholder="PT Pertamina EP / Mitra">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Departemen / Fungsi</label>
                    <select name="department_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Pilih Departemen / Fungsi --</option>
                        @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected':'' }}>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Nopeg / No SIM-L</label>
                    <input type="text" name="nomor_pegawai" value="{{ old('nomor_pegawai') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" placeholder="754321 / SIML-123">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" placeholder="Contoh: Operator Produksi">
                </div>

                {{-- Status Karyawan --}}
                <div class="form-group">
                    <label class="form-label" style="font-weight:700;color:#15803d">Status Karyawan</label>
                    <select name="worker_status_id" class="form-select" style="background:#fff;border-color:#cbd5e1">
                        <option value="">-- Pilih Status Karyawan --</option>
                        @foreach($workerStatuses as $ws)<option value="{{ $ws->id }}" {{ old('worker_status_id') == $ws->id ? 'selected':'' }}>{{ $ws->name }}</option>@endforeach
                    </select>
                </div>

                {{-- Tipe Jam Kerja diletakkan tepat di bawah Status Karyawan --}}
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Tipe Jam Kerja</label>
                    <select name="is_shift" id="isShiftSelect" class="form-select" onchange="toggleSupperPreference()" style="background:#fff;border-color:#cbd5e1;font-weight:600">
                        <option value="0" {{ old('is_shift') == '1' ? '' : 'selected' }}>Non-Shift (Reguler Normal)</option>
                        <option value="1" {{ old('is_shift') == '1' ? 'selected' : '' }}>Shift (Pekerja Bergilir)</option>
                    </select>
                </div>

                {{-- Preferensi Lokasi Makan --}}
                <div class="form-group" style="grid-column:1/-1;background:#fff;border:1px solid #bbf7d0;border-radius:12px;padding:14px;margin:4px 0">
                    <p style="font-size:12.5px;font-weight:800;color:#15803d;margin:0 0 10px">🍽️ Preferensi Lokasi Tempat Makan</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:10px">
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#c2410c;font-weight:700">🍳 B'fast (Sarapan)</label>
                            <select name="breakfast_location_id" class="form-select admin-meal-loc-dropdown" style="border-color:#fed7aa;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('breakfast_location_id') == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#1d4ed8;font-weight:700">🍲 Lunch (Siang)</label>
                            <select name="lunch_location_id" class="form-select admin-meal-loc-dropdown" style="border-color:#bfdbfe;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('lunch_location_id') == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:11.5px;color:#6d28d9;font-weight:700">🍛 Dinner (Malam)</label>
                            <select name="dinner_location_id" class="form-select admin-meal-loc-dropdown" style="border-color:#e9d5ff;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('dinner_location_id') == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div id="supperPrefBox" style="display:none">
                            <label class="form-label" style="font-size:11.5px;color:#9d174d;font-weight:700">🌙 Supper (Shift Malam)</label>
                            <select name="supper_location_id" id="supperLocationSelect" class="form-select admin-meal-loc-dropdown" style="border-color:#fbcfe8;font-size:12.5px">
                                <option value="">-- Standar Mess Hall --</option>
                                @foreach($mealLocations as $loc)<option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}" {{ old('supper_location_id') == $loc->id ? 'selected':'' }}>{{ $loc->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label" style="font-weight:700;color:#15803d">Password <span style="color:var(--red)">*</span></label>
                    <input type="text" name="password" value="{{ old('password', 'password') }}" class="form-input" style="background:#fff;border-color:#cbd5e1" required>
                    <p class="form-hint" style="color:#64748b;margin-top:4px">Default password akun baru: <code>password</code></p>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:14px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#16a34a;border-color:#16a34a;font-weight:800;padding:10px">Buat Akun</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="padding:10px 18px;background:#fff">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>

@push('scripts')
<script>
function toggleSupperPreference() {
    var select = document.getElementById('isShiftSelect');
    var supperBox = document.getElementById('supperPrefBox');
    var supperSelect = document.getElementById('supperLocationSelect');
    
    if (select && supperBox) {
        if (select.value === '1') {
            supperBox.style.display = 'block';
        } else {
            supperBox.style.display = 'none';
            if (supperSelect) supperSelect.value = '';
        }
    }
}

function filterAdminLocations() {
    var regSelect = document.getElementById('homebaseRegionSelect');
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
    var dropdowns = document.querySelectorAll('.admin-meal-loc-dropdown');
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
    toggleSupperPreference();
    filterAdminLocations();
});
</script>
@endpush
@endsection
