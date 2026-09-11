@extends('layouts.app')
@section('title', 'Master Data Sistem')
@section('page-title', 'Master Data')

@section('content')
<div style="max-width:1100px;margin:0 auto">

    {{-- Tabs Navigation --}}
    <div style="display:flex;gap:8px;border-bottom:2px solid #e8edf2;margin-bottom:20px;overflow-x:auto;padding-bottom:2px">
        <a href="{{ route('admin.master.index', ['tab' => 'departments']) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:700;text-decoration:none;border-radius:8px 8px 0 0;display:flex;align-items:center;gap:7px;
                  color:{{ $tab === 'departments' ? '#006738' : '#6b7280' }};
                  border-bottom:3px solid {{ $tab === 'departments' ? '#006738' : 'transparent' }};
                  background:{{ $tab === 'departments' ? '#fff' : 'transparent' }}">
            🏢 Fungsi & Departemen ({{ $departments->count() }})
        </a>
        <a href="{{ route('admin.master.index', ['tab' => 'meal-locations']) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:700;text-decoration:none;border-radius:8px 8px 0 0;display:flex;align-items:center;gap:7px;
                  color:{{ $tab === 'meal-locations' ? '#c2410c' : '#6b7280' }};
                  border-bottom:3px solid {{ $tab === 'meal-locations' ? '#c2410c' : 'transparent' }};
                  background:{{ $tab === 'meal-locations' ? '#fff' : 'transparent' }}">
            🍽️ Lokasi Tempat Makan ({{ $mealLocations->count() }})
        </a>
        <a href="{{ route('admin.master.index', ['tab' => 'rooms']) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:700;text-decoration:none;border-radius:8px 8px 0 0;display:flex;align-items:center;gap:7px;
                  color:{{ $tab === 'rooms' ? '#7c3aed' : '#6b7280' }};
                  border-bottom:3px solid {{ $tab === 'rooms' ? '#7c3aed' : 'transparent' }};
                  background:{{ $tab === 'rooms' ? '#fff' : 'transparent' }}">
            🛏️ Kamar Mess ({{ $rooms->count() }})
        </a>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════
         TAB 1: FUNGSI & DEPARTEMEN (DEPARTMENTS)
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'departments')
    <div class="card" style="border-left:4px solid #006738;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">🏢</span>
                <div>
                    <h3 style="margin:0;font-size:15px;font-weight:700;color:#1a2332">Daftar Fungsi</h3>
                </div>
            </div>
            <button type="button" onclick="openModal('addDeptModal')" class="btn btn-primary btn-sm" style="background:#006738;font-weight:700">
                + Tambah
            </button>
        </div>

        <div style="overflow-x:auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:60px;text-align:center">No</th>
                        <th>Fungsi</th>
                        <th style="text-align:center;width:180px">Jumlah Anggota</th>
                        <th style="text-align:right;width:120px">Aksi Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $i => $d)
                    <tr>
                        <td style="text-align:center;color:#6b7280;font-weight:600">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-weight:700;color:#1a2332;font-size:13.5px">{{ $d->name }}</span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-green">{{ $d->users_count }} orang</span>
                        </td>
                        <td style="text-align:right">
                            <button type="button" onclick="editDept({{ json_encode($d) }})" class="btn btn-secondary btn-sm" style="font-size:12px;padding:5px 12px">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:30px;color:#9ca3af">Belum ada data fungsi kerja.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         TAB 2: LOKASI TEMPAT MAKAN (MEAL LOCATIONS)
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'meal-locations')
    <div class="card" style="border-left:4px solid #c2410c;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">🍽️</span>
                <div>
                    <h3 style="margin:0;font-size:15px;font-weight:700;color:#1a2332">Daftar Lokasi Tempat Makan</h3>
                </div>
            </div>
            <button type="button" onclick="openModal('addLocModal')" class="btn btn-primary btn-sm" style="background:#c2410c;border-color:#c2410c;font-weight:700">
                + Tambah
            </button>
        </div>

        <div style="overflow-x:auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:60px;text-align:center">No</th>
                        <th style="width:160px">Wilayah</th>
                        <th>Lokasi Tempat Makan</th>
                        <th style="text-align:center;width:180px">Pengguna Default</th>
                        <th style="text-align:right;width:120px">Aksi Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mealLocations as $i => $loc)
                    <tr>
                        <td style="text-align:center;color:#6b7280;font-weight:600">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-weight:700;color:#006738">{{ $loc->region?->name ?? '-' }}</span>
                        </td>
                        <td>
                            <span style="font-weight:700;color:#1a2332;font-size:13.5px">{{ $loc->name }}</span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-green">{{ $loc->users_count }} orang</span>
                        </td>
                        <td style="text-align:right">
                            <button type="button" onclick="editLoc({{ json_encode($loc) }})" class="btn btn-secondary btn-sm" style="font-size:12px;padding:5px 12px">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:30px;color:#9ca3af">Belum ada lokasi tempat makan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         TAB 3: KAMAR MESS
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'rooms')
    <div class="card" style="border-left:4px solid #7c3aed;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fafafa;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">🛏️</span>
                <div>
                    <h3 style="margin:0;font-size:15px;font-weight:700;color:#1a2332">Daftar Kamar Mess</h3>
                    <p style="margin:2px 0 0;font-size:11.5px;color:#6b7280">Daftar kamar akan muncul sebagai pilihan di profil pengguna</p>
                </div>
            </div>
            <button type="button" onclick="openModal('addRoomModal')" class="btn btn-primary btn-sm" style="background:#7c3aed;border-color:#7c3aed;font-weight:700">
                + Tambah Kamar
            </button>
        </div>

        <div style="overflow-x:auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:50px;text-align:center">No</th>
                        <th style="width:130px">Lokasi</th>
                        <th style="width:150px">Blok / Gedung</th>
                        <th>Nomor Kamar</th>
                        <th style="text-align:center;width:140px">Penghuni</th>
                        <th style="text-align:center;width:100px">Status</th>
                        <th style="text-align:right;width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms->load('region') as $i => $room)
                    <tr style="{{ !$room->is_active ? 'opacity:0.55' : '' }}">
                        <td style="text-align:center;color:#6b7280;font-weight:600">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-size:12px;color:#006738;font-weight:700">{{ $room->region?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span style="font-size:12px;color:#7c3aed;font-weight:700">{{ $room->block ?: '—' }}</span>
                        </td>
                        <td>
                            <span style="font-weight:700;color:#1a2332;font-size:13.5px">{{ $room->name }}</span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-green">{{ $room->users_count }} orang</span>
                        </td>
                        <td style="text-align:center">
                            <form method="POST" action="{{ route('admin.master.rooms.toggle', $room) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" style="border:none;background:none;cursor:pointer;padding:0"
                                        title="{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <span class="badge {{ $room->is_active ? 'badge-green' : 'badge-gray' }}" style="font-size:11px">
                                        {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right">
                            <button type="button" onclick="editRoom({{ json_encode(['id'=>$room->id,'region_id'=>$room->region_id,'block'=>$room->block,'name'=>$room->name,'is_active'=>$room->is_active]) }})" class="btn btn-secondary btn-sm" style="font-size:12px;padding:5px 12px">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#9ca3af">Belum ada data kamar. Klik "+ Tambah Kamar" untuk menambahkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS: ADD & EDIT
══════════════════════════════════════════════════════════════════════════ --}}

{{-- 1. Modal Tambah Departemen --}}
<div id="addDeptModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #006738">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🏢 + Tambah Departemen / Fungsi</h3>
            <button type="button" onclick="closeModal('addDeptModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.master.departments.store') }}" style="padding:20px">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Departemen / Fungsi <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: Produksi, RAM, HSE, GS..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Kode Singkatan</label>
                <input type="text" name="code" class="form-input" placeholder="Contoh: PROD, RAM, HSE...">
            </div>
            <div class="form-group">
                <label class="form-label">Perusahaan</label>
                <select name="company_id" class="form-select">
                    @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#006738">Simpan Departemen</button>
                <button type="button" onclick="closeModal('addDeptModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. Modal Edit Departemen --}}
<div id="editDeptModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #006738">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🏢 Edit Departemen / Fungsi</h3>
            <button type="button" onclick="closeModal('editDeptModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form id="editDeptForm" method="POST" action="" style="padding:20px">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Departemen / Fungsi <span style="color:var(--red)">*</span></label>
                <input type="text" id="editDeptName" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kode Singkatan</label>
                <input type="text" id="editDeptCode" name="code" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Perusahaan</label>
                <select id="editDeptCompany" name="company_id" class="form-select">
                    @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#006738">Simpan Perubahan</button>
                <button type="button" onclick="closeModal('editDeptModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. Modal Tambah Kamar --}}
<div id="addRoomModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #7c3aed">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🛏️ + Tambah Kamar Mess</h3>
            <button type="button" onclick="closeModal('addRoomModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.master.rooms.store') }}" style="padding:20px">
            @csrf
            <div class="form-group">
                <label class="form-label">Lokasi / Wilayah <span style="color:var(--red)">*</span></label>
                <select name="region_id" class="form-select" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Blok / Gedung <span style="color:#9ca3af;font-weight:400">(opsional)</span></label>
                <input type="text" name="block" class="form-input" placeholder="Contoh: Blok A, Gedung B, Mess Staff...">
                <p class="form-hint">Isi jika kamar dikelompokkan berdasarkan blok/gedung.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor / Nama Kamar <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: 101, A-12, Kamar Barat..." required>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#7c3aed;border-color:#7c3aed">Simpan Kamar</button>
                <button type="button" onclick="closeModal('addRoomModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. Modal Edit Kamar --}}
<div id="editRoomModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #7c3aed">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🛏️ Edit Kamar Mess</h3>
            <button type="button" onclick="closeModal('editRoomModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form id="editRoomForm" method="POST" action="" style="padding:20px">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Lokasi / Wilayah <span style="color:var(--red)">*</span></label>
                <select id="editRoomRegion" name="region_id" class="form-select" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Blok / Gedung <span style="color:#9ca3af;font-weight:400">(opsional)</span></label>
                <input type="text" id="editRoomBlock" name="block" class="form-input" placeholder="Contoh: Blok A, Mess Staff...">
            </div>
            <div class="form-group">
                <label class="form-label">Nomor / Nama Kamar <span style="color:var(--red)">*</span></label>
                <input type="text" id="editRoomName" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select id="editRoomActive" name="is_active" class="form-select">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#7c3aed;border-color:#7c3aed">Simpan Perubahan</button>
                <button type="button" onclick="closeModal('editRoomModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>
<div id="addLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #c2410c">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🍽️ + Tambah Lokasi Tempat Makan</h3>
            <button type="button" onclick="closeModal('addLocModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.master.meal-locations.store') }}" style="padding:20px">
            @csrf
            <div class="form-group">
                <label class="form-label">Wilayah Lapangan <span style="color:var(--red)">*</span></label>
                <select name="region_id" class="form-select" required>
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lokasi Tempat Makan <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: Mess Hall Staff, Kantor, SP, Pos Keamanan..." required>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#c2410c;border-color:#c2410c">Simpan Lokasi</button>
                <button type="button" onclick="closeModal('addLocModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. Modal Edit Lokasi Tempat Makan --}}
<div id="editLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);border-left:4px solid #c2410c">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">🍽️ Edit Lokasi Tempat Makan</h3>
            <button type="button" onclick="closeModal('editLocModal')" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form id="editLocForm" method="POST" action="" style="padding:20px">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Wilayah Lapangan <span style="color:var(--red)">*</span></label>
                <select id="editLocRegion" name="region_id" class="form-select" required>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lokasi Tempat Makan <span style="color:var(--red)">*</span></label>
                <input type="text" id="editLocName" name="name" class="form-input" required>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#c2410c;border-color:#c2410c">Simpan Perubahan</button>
                <button type="button" onclick="closeModal('editLocModal')" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'flex';
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'none';
}

function editDept(d) {
    document.getElementById('editDeptForm').action = '/admin/master/departments/' + d.id;
    document.getElementById('editDeptName').value = d.name;
    document.getElementById('editDeptCode').value = d.code || '';
    document.getElementById('editDeptCompany').value = d.company_id || '';
    openModal('editDeptModal');
}

function editLoc(loc) {
    document.getElementById('editLocForm').action = '/admin/master/meal-locations/' + loc.id;
    document.getElementById('editLocRegion').value = loc.region_id;
    document.getElementById('editLocName').value = loc.name;
    openModal('editLocModal');
}

function editRoom(room) {
    document.getElementById('editRoomForm').action = '/admin/master/rooms/' + room.id;
    document.getElementById('editRoomRegion').value = room.region_id || '';
    document.getElementById('editRoomBlock').value = room.block || '';
    document.getElementById('editRoomName').value = room.name;
    document.getElementById('editRoomActive').value = room.is_active ? '1' : '0';
    openModal('editRoomModal');
}
</script>
@endpush
@endsection
