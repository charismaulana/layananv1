@extends('layouts.app')
@section('title', 'Rencana Makan')
@section('page-title', 'Rencana Makan')

@section('content')
@php
    $statusMap = [
        'active'       => ['label'=>'Aktif',       'class'=>'badge-green'],
        'cancelled'    => ['label'=>'Dibatalkan',   'class'=>'badge-red'],
        'outside_meal' => ['label'=>'Outside Meal', 'class'=>'badge-gold'],
        'moved'        => ['label'=>'Dipindah',     'class'=>'badge-blue'],
    ];
    $mealColors = [
        'breakfast' => '#fff7ed',
        'lunch'     => '#eff6ff',
        'dinner'    => '#f5f3ff',
        'supper'    => '#fdf2f8',
    ];
@endphp

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:14px 16px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="flex:1;min-width:140px">
                <label class="form-label" style="margin-bottom:4px">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input">
            </div>
            <div style="flex:1;min-width:140px">
                <label class="form-label" style="margin-bottom:4px">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('meal-plan.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Eco-Friendly Educational Notice Banner --}}
<div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;box-shadow:0 1px 3px rgba(0,0,0,.02)">
    <span style="font-size:18px;line-height:1">🌱</span>
    <div style="font-size:12.5px;color:#166534;line-height:1.45">
        <strong>Cegah Food Waste & Sayangi Bumi:</strong><br>
        Apabila Anda berencana membeli makan di luar atau tidak makan dari katering, mohon berikan konfirmasi <strong>Batalkan</strong> pada jadwal terkait sebelum batas cut-off (H-1 pukul 19:00 WIB) agar katering tidak menyiapkan makanan berlebih dan mencegah terbuangnya makanan secara sia-sia.
    </div>
</div>

@if($plans->isEmpty())
<div class="card">
    <div class="empty-state">
        <svg width="48" height="48" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p style="font-weight:600;color:#374151;margin-bottom:4px">Tidak ada rencana makan</p>
        <p>Isi roster untuk menghasilkan rencana makan otomatis</p>
        <a href="{{ route('roster.index') }}" class="btn btn-primary btn-sm" style="margin-top:12px;display:inline-flex">Isi Roster Sekarang</a>
    </div>
</div>
@else

{{-- Group by date --}}
@php
    $grouped = $plans->getCollection()->groupBy(fn($p) => $p->meal_date->format('Y-m-d'));
@endphp

@foreach($grouped as $dateStr => $dayPlans)
@php $date = \Carbon\Carbon::parse($dateStr); $isPast = $date->lt($today); @endphp

<div style="margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
        <div style="width:40px;height:40px;background:{{ $date->isToday() ? 'var(--g)' : '#f3f4f6' }};border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0">
            <span style="font-size:8px;font-weight:700;color:{{ $date->isToday() ? 'rgba(255,255,255,.8)' : 'var(--faint)' }};text-transform:uppercase;line-height:1">{{ $date->translatedFormat('M') }}</span>
            <span style="font-size:16px;font-weight:800;color:{{ $date->isToday() ? '#fff' : 'var(--text)' }};line-height:1">{{ $date->format('d') }}</span>
        </div>
        <div>
            <p style="font-size:13.5px;font-weight:700;color:var(--text);margin:0">{{ $date->translatedFormat('l, d F Y') }}{{ $date->isToday() ? ' — Hari Ini' : '' }}</p>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($dayPlans->sortBy('meal_type_id') as $plan)
        @php
            $isCancelled = $plan->status === 'cancelled';
            $isActive = $plan->status === 'active';
            $isMess = $plan->mealLocation ? $plan->mealLocation->isMessHall() : true;
            $locName = $plan->mealLocation?->name ?? $plan->region?->name ?? 'Mess Hall';
            $isCutoffPassed = app(\App\Services\PlanningCutoffService::class)->isClosed($plan->meal_date);
            $canEdit = (!$isCutoffPassed || auth()->user()->isGS() || auth()->user()->isSysAdmin()) && !$plan->meal_date->lt($today);
        @endphp
        
        <div class="card" style="margin:0;overflow:hidden;border-radius:12px;transition:all .15s;
             {{ $isCancelled 
                ? 'background:#f8fafc;border:1.5px dashed #cbd5e1;border-left:5px solid #dc2626;opacity:0.92;' 
                : 'background:#ffffff;border:1.5px solid #bbf7d0;border-left:5px solid #006738;box-shadow:0 2px 6px rgba(0,103,56,0.04);' }}">
            <div style="padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                
                {{-- Info Sisi Kiri --}}
                <div style="flex:1;min-width:180px">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px">
                        <p style="font-size:14px;margin:0;
                                  {{ $isCancelled ? 'font-weight:700;color:#94a3b8;text-decoration:line-through;' : 'font-weight:800;color:#0f172a;' }}">
                            {{ $plan->mealType->name }}
                        </p>
                        
                        @if($plan->outside_meal)
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;border-radius:20px">
                                📦 Outside Meal
                            </span>
                        @elseif($isCancelled)
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;padding:2px 8px;border-radius:20px">
                                🚫 Dibatalkan (Tidak Makan)
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;color:#166534;background:#dcfce7;border:1px solid #86efac;padding:2px 8px;border-radius:20px">
                                🟢 Makan Aktif
                            </span>
                        @endif
                    </div>

                    <div style="font-size:12px;line-height:1.4;
                                {{ $isCancelled ? 'color:#94a3b8;' : 'color:#334155;font-weight:600;' }}">
                        <span>{{ $isMess ? '🍽️' : '🚚' }} {{ $locName }}</span>
                        @if($isCancelled && $plan->cancellationReason)
                            <span style="font-size:11.5px;color:#dc2626;font-style:italic"> · Alasan: {{ $plan->cancellationReason }}</span>
                        @endif
                    </div>
                </div>

                {{-- Tombol Aksi Sisi Kanan --}}
                @if($canEdit)
                    @if($isActive)
                    <div style="display:flex;gap:6px;align-items:center">
                        <button type="button" 
                                onclick="openLocationModal({{ $plan->id }}, '{{ $plan->mealType->name }}', '{{ $plan->meal_date->translatedFormat('d M Y') }}', {{ $plan->meal_location_id ?? 'null' }}, {{ $plan->region_id ?? auth()->user()->homebase_region_id ?? 1 }})" 
                                style="font-size:11.5px;padding:6px 12px;font-weight:600;background:#f8fafc;border:1px solid #cbd5e1;color:#334155;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                            📍 Lokasi
                        </button>
                        <a href="{{ route('cancellation.create', $plan) }}" 
                           style="font-size:11.5px;padding:6px 12px;font-weight:600;background:#ffffff;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
                            ✕ Batalkan
                        </a>
                    </div>
                    @elseif($isCancelled)
                    <div style="display:flex;gap:6px">
                        <form method="POST" action="{{ route('cancellation.reactivate', $plan) }}" style="margin:0">
                            @csrf
                            <button type="submit" 
                                    style="font-size:11.5px;padding:6px 14px;font-weight:800;background:#ffffff;border:1.5px solid #006738;color:#006738;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;box-shadow:0 1px 3px rgba(0,103,56,0.08)">
                                ↺ Aktifkan Kembali
                            </button>
                        </form>
                    </div>
                    @endif
                @endif

            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

<div>{{ $plans->links() }}</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL GANTI LOKASI MAKAN / TITIK PENGANTARAN
══════════════════════════════════════════════════════════════════════════ --}}
<div id="changeLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25)">
        <div style="padding:16px 20px;border-bottom:1px solid #e8edf2;display:flex;align-items:center;justify-content:space-between">
            <div>
                <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">📍 Pilih Lokasi Makan / Pengantaran</h3>
                <p id="modalMealSubtitle" style="font-size:11.5px;color:#6b7280;margin:2px 0 0">Pilih tempat makan atau lokasi pengantaran</p>
            </div>
            <button type="button" onclick="closeLocModal()" style="border:none;background:#f3f4f6;border-radius:8px;padding:6px 9px;cursor:pointer">✕</button>
        </div>
        <form id="changeLocForm" method="POST" action="" style="padding:20px">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label">Pilih Lokasi Makan / Titik Pengantaran <span style="color:var(--red)">*</span></label>
                <select name="meal_location_id" id="modalLocationSelect" class="form-select" required>
                    @foreach($deliveryLocations as $loc)
                    @php
                        $isMess = $loc->isMessHall();
                    @endphp
                    <option value="{{ $loc->id }}" data-region="{{ $loc->region_id }}">
                        {{ $isMess ? '🍽️' : '🚚' }} {{ $loc->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Info Ketentuan Ambil vs Diantar --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-top:14px">
                <div style="font-size:12px;color:#334155;line-height:1.45">
                    <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:6px">
                        <span>🍽️</span>
                        <span><strong>Pilih Mess Hall:</strong> Makanan <strong>diambil sendiri</strong> di Mess Hall (baik makan di tempat / dine in maupun bawa sendiri / take away). <em>Tidak diantar</em>.</span>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:6px">
                        <span>🚚</span>
                        <span><strong>Pilih Selain Mess Hall:</strong> Makanan akan <strong>diantar oleh katering</strong> ke titik lokasi kerja Anda (Kantor / SP / Pos).</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#006738">Simpan Pilihan Lokasi</button>
                <button type="button" onclick="closeLocModal()" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openLocationModal(planId, mealName, mealDate, currentLocId, regionId) {
    document.getElementById('changeLocForm').action = '/rencana-makan/' + planId + '/lokasi';
    document.getElementById('modalMealSubtitle').textContent = mealName + ' · ' + mealDate;
    
    var select = document.getElementById('modalLocationSelect');
    var options = select.options;
    var firstVisibleValue = null;
    var isCurrentLocVisible = false;

    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        var optRegion = opt.getAttribute('data-region');
        
        if (!regionId || optRegion == regionId) {
            opt.hidden = false;
            opt.disabled = false;
            if (!firstVisibleValue) firstVisibleValue = opt.value;
            if (opt.value == currentLocId) isCurrentLocVisible = true;
        } else {
            opt.hidden = true;
            opt.disabled = true;
        }
    }

    if (currentLocId && isCurrentLocVisible) {
        select.value = currentLocId;
    } else if (firstVisibleValue) {
        select.value = firstVisibleValue;
    }

    document.getElementById('changeLocModal').style.display = 'flex';
}
function closeLocModal() {
    document.getElementById('changeLocModal').style.display = 'none';
}
</script>
@endpush
@endsection
