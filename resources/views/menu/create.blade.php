@extends('layouts.app')
@section('title', 'Tambah / Edit Menu')
@section('page-title', 'Tambah / Edit Menu')

@section('content')
<div style="max-width:750px;margin:0 auto">
    <div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-header" style="background:#fff;border-bottom:1px solid #bbf7d0;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#166534">🍽️ Input / Edit Menu Harian</h3>
            <a href="{{ route('menu.index') }}" class="btn btn-secondary btn-sm" style="background:#fff;font-weight:600;padding:6px 14px">Kembali</a>
        </div>
        <div class="card-body" style="padding:20px">
            <form method="POST" action="{{ route('menu.store') }}" x-data="menuForm()">
                @csrf
                <div class="grid-2" style="gap:14px;margin-bottom:16px">
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700;color:#166534">Tanggal</label>
                        <input type="date" name="menu_date" value="{{ old('menu_date', request('date', now()->format('Y-m-d'))) }}" class="form-input" style="background:#fff;font-weight:600" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700;color:#166534">Wilayah</label>
                        <select name="region_id" class="form-select" style="background:#fff;font-weight:600" required>
                            @foreach($regions as $r)
                            <option value="{{ $r->id }}" {{ old('region_id', request('region_id')) == $r->id ? 'selected':'' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700;color:#166534">Jenis Makan</label>
                        <select name="meal_type_id" class="form-select" style="background:#fff;font-weight:600" required>
                            @foreach($mealTypes as $mt)
                            <option value="{{ $mt->id }}" {{ old('meal_type_id', request('meal_type_id')) == $mt->id ? 'selected':'' }}>{{ $mt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:700;color:#166534">Deskripsi / Tema (opsional)</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-input" placeholder="e.g. Menu Hari Kemerdekaan" style="background:#fff">
                    </div>
                </div>

                <div style="margin-bottom:20px;background:#fff;border:1px solid #bbf7d0;border-radius:10px;padding:16px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <label class="form-label" style="margin:0;font-weight:800;color:#166534;font-size:13px">Daftar Item Menu</label>
                        <button type="button" @click="addItem()" class="btn btn-secondary btn-sm" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;font-weight:700">+ Tambah Baris Item</button>
                    </div>

                    <div style="border:1.5px solid #e2e8f0;border-radius:8px;overflow:hidden">
                        {{-- Header --}}
                        <div style="display:grid;grid-template-columns:1fr 40px;gap:0;background:#f8fafc;padding:9px 14px;border-bottom:1px solid #e2e8f0">
                            <span style="font-size:11px;font-weight:800;color:#475569;text-transform:uppercase">Nama Makanan / Hidangan</span>
                            <span></span>
                        </div>
                        {{-- Items --}}
                        <template x-for="(item, idx) in items" :key="idx">
                        <div style="display:grid;grid-template-columns:1fr 40px;gap:0;border-bottom:1px solid #f1f5f9;padding:8px 12px;align-items:center;background:#fff">
                            <input :name="'items[' + idx + '][name]'"
                                   x-model="item.name"
                                   placeholder="e.g. Nasi Goreng Spesial / Ayam Bakar Madu / Sayur Asem"
                                   style="border:none;outline:none;font-size:13.5px;color:#1e293b;font-weight:600;font-family:inherit;width:100%;padding:4px 8px"
                                   required>
                            <button type="button" @click="removeItem(idx)"
                                    style="width:28px;height:28px;border-radius:6px;border:none;background:#fef2f2;color:#dc2626;cursor:pointer;display:flex;align-items:center;justify-content:center;justify-self:center"
                                    x-show="items.length > 1">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        </template>
                    </div>

                    {{-- Suggestions --}}
                    <div style="margin-top:12px">
                        <p style="font-size:11px;color:#64748b;font-weight:700;margin-bottom:6px">Pilihan cepat:</p>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            @foreach(['Nasi Putih','Ayam Goreng','Tempe Orek','Sayur Bening','Buah Segar','Puding','Kopi / Teh','Sup Ayam','Ikan Bakar','Telur Dadar'] as $sug)
                            <button type="button" @click="addSuggestion('{{ $sug }}')"
                                    style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;font-weight:600;cursor:pointer;transition:all .12s">
                                + {{ $sug }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary" style="flex:1;background:#16a34a;border-color:#16a34a;font-weight:800;padding:10px 18px;display:flex;align-items:center;justify-content:center;gap:6px">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Menu
                    </button>
                    <a href="{{ route('menu.index') }}" class="btn btn-secondary" style="background:#fff;font-weight:600;padding:10px 18px">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function menuForm() {
    return {
        items: [{ name: '' }],
        addItem() {
            this.items.push({ name: '' });
        },
        removeItem(idx) {
            if (this.items.length > 1) this.items.splice(idx, 1);
        },
        addSuggestion(name) {
            const empty = this.items.find(i => !i.name);
            if (empty) empty.name = name;
            else this.items.push({ name: name });
        }
    }
}
</script>
@endpush
@endsection
