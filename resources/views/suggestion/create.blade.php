@extends('layouts.app')
@section('title', 'Saran & Masukan')
@section('page-title', 'Saran & Masukan')

@section('content')
<div style="max-width:640px;margin:0 auto">
    <div class="card" style="border-left:4px solid #0284c7">
        <div class="card-header" style="background:#fafafa;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:18px">💬</span>
                <h3 style="font-size:15px;font-weight:700;color:#1a2332;margin:0">Kirim Saran & Masukan</h3>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="font-size:11.5px">← Beranda</a>
        </div>
        <div class="card-body" style="padding:20px">
            <p style="font-size:12.5px;color:var(--muted);margin-bottom:18px;line-height:1.45">
                Saran dan masukan Anda sangat berarti untuk meningkatkan mutu masakan, variasi menu, kebersihan, serta kualitas layanan katering Field Ramba.
            </p>

            <form method="POST" action="{{ route('suggestion.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Kategori Masukan <span style="color:var(--red)">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Menu" {{ old('category') === 'Menu' ? 'selected':'' }}>Menu & Variasi Hidangan</option>
                        <option value="Rasa" {{ old('category') === 'Rasa' ? 'selected':'' }}>Cita Rasa Masakan</option>
                        <option value="Porsi" {{ old('category') === 'Porsi' ? 'selected':'' }}>Porsi Makanan</option>
                        <option value="Distribusi" {{ old('category') === 'Distribusi' ? 'selected':'' }}>Ketepatan Waktu & Distribusi Antar</option>
                        <option value="Kebersihan" {{ old('category') === 'Kebersihan' ? 'selected':'' }}>Kebersihan Makanan & Fasilitas Mess Hall</option>
                        <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected':'' }}>Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Saran</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input"
                           placeholder="Ringkasan singkat saran Anda">
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Saran / Masukan <span style="color:var(--red)">*</span></label>
                    <textarea name="content" class="form-textarea" rows="6" required
                              placeholder="Jelaskan saran atau masukan Anda dengan detail...">{{ old('content') }}</textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked':'' }}
                               style="width:15px;height:15px;accent-color:var(--g)">
                        <span style="font-size:13px;color:#374151">Kirim secara anonim</span>
                    </label>
                    <p class="form-hint" style="margin-left:23px">Nama Anda tidak akan ditampilkan kepada catering/GS</p>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:20px">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Saran
                </button>
            </form>
        </div>
    </div>

    {{-- Previous Suggestions --}}
    @if($mySuggestions->isNotEmpty())
    <div class="card" style="margin-top:20px">
        <div class="card-header"><h3>Saran Sebelumnya</h3></div>
        <div>
            @foreach($mySuggestions as $s)
            <div style="padding:14px 16px;border-bottom:1px solid #f9fafb">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span class="badge badge-gray" style="font-size:10px">{{ ucfirst($s->category) }}</span>
                    <span style="font-size:10px;color:var(--faint)">{{ $s->created_at->diffForHumans() }}</span>
                </div>
                @if($s->title)
                <p style="font-size:13px;font-weight:600;color:var(--text);margin:0 0 2px">{{ $s->title }}</p>
                @endif
                <p style="font-size:12px;color:var(--muted);margin:0">{{ Str::limit($s->content, 100) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
