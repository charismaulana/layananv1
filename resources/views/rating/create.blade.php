@extends('layouts.app')
@section('title', 'Beri Rating Menu')
@section('page-title', 'Rating & Ulasan Menu')

@section('content')
<div style="max-width:500px">
    <div class="card">
        <div class="card-header">
            <h3>Beri Penilaian Menu</h3>
            <a href="{{ route('menu.index') }}" class="btn btn-secondary btn-sm">← Menu</a>
        </div>
        <div class="card-body">
            @if($menu)
            {{-- Menu preview --}}
            <div style="background:var(--g-lt);border:1px solid var(--g-md);border-radius:10px;padding:14px;margin-bottom:24px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <span class="badge badge-green">{{ $menu->mealType->name }}</span>
                    <span style="font-size:11px;color:var(--muted)">{{ $menu->region->name }} · {{ $menu->menu_date->translatedFormat('d M Y') }}</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:4px">
                    @foreach($menu->items->take(8) as $item)
                    <span style="font-size:11px;background:#fff;border:1px solid var(--g-md);border-radius:5px;padding:2px 8px;color:#374151">{{ $item->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('rating.store') }}" x-data="{ score: 0, hover: 0 }">
                @csrf
                @if($menu)
                <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                @else
                <div class="form-group">
                    <label class="form-label">Pilih Menu</label>
                    <select name="menu_id" class="form-select" required>
                        @foreach($recentMenus as $m)
                        <option value="{{ $m->id }}">{{ $m->menu_date->translatedFormat('d M') }} · {{ $m->mealType->name }} · {{ $m->region->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Star Rating --}}
                <div class="form-group" style="text-align:center">
                    <label class="form-label" style="text-align:left;display:block">Rating <span style="color:var(--red)">*</span></label>
                    <div style="display:flex;gap:8px;justify-content:center;margin:12px 0 8px">
                        @foreach([1,2,3,4,5] as $star)
                        <button type="button"
                                @click="score = {{ $star }}"
                                @mouseover="hover = {{ $star }}"
                                @mouseleave="hover = 0"
                                style="background:none;border:none;cursor:pointer;padding:4px;transition:transform .1s"
                                :style="(hover || score) >= {{ $star }} ? 'transform:scale(1.15)' : ''">
                            <svg width="36" height="36" viewBox="0 0 24 24"
                                 :fill="(hover || score) >= {{ $star }} ? '#f59e0b' : '#e5e7eb'"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </button>
                        @endforeach
                    </div>
                    <p style="font-size:12px;color:var(--muted);height:18px"
                       x-text="['','Sangat Buruk','Buruk','Cukup','Baik','Sangat Baik'][hover || score] || 'Klik bintang untuk memberi nilai'">
                    </p>
                    <input type="hidden" name="score" :value="score" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Komentar (opsional)</label>
                    <textarea name="comment" class="form-textarea" rows="4"
                              placeholder="Ceritakan pengalaman makan Anda...">{{ old('comment') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-full" :disabled="score === 0">
                    <span style="color:#f59e0b;margin-right:2px">★</span> Kirim Rating
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
