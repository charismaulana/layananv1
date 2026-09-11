@extends('layouts.app')
@section('title', 'Menu Harian')
@section('page-title', 'Menu Harian')

@section('content')
@php
    $today = $today ?? \Carbon\Carbon::today();
    $tabs = [
        ['date' => $today->copy()->subDay(),   'label' => 'Kemarin',   'sublabel' => $today->copy()->subDay()->translatedFormat('d M')],
        ['date' => $today,                    'label' => 'Hari Ini',  'sublabel' => $today->translatedFormat('d M')],
        ['date' => $today->copy()->addDay(),   'label' => 'Besok',     'sublabel' => $today->copy()->addDay()->translatedFormat('d M')],
        ['date' => $today->copy()->addDays(2), 'label' => 'Lusa',      'sublabel' => $today->copy()->addDays(2)->translatedFormat('d M')],
    ];
@endphp

<div style="max-width:1100px;margin:0 auto">

    {{-- Header & Wilayah Filter Card --}}
    <div class="card" style="margin-bottom:20px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="card-body" style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span style="font-size:13px;font-weight:700;color:#166534">📍 Wilayah:</span>
                @if($canSwitchRegion)
                <form id="regionFilterForm" method="GET" style="display:inline-block">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <select name="region_id" class="form-select" style="min-width:180px;padding:7px 32px 7px 12px;font-size:13px;font-weight:700;background:#fff;border:1px solid #cbd5e1;border-radius:8px;color:#1e293b" onchange="this.form.submit()">
                        <option value="">Semua Wilayah</option>
                        @foreach($regions as $r)
                        <option value="{{ $r->id }}" {{ $regionId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </form>
                @else
                <span class="badge" style="background:#fff;border:1px solid #bbf7d0;color:#166534;font-size:13px;font-weight:800;padding:6px 12px">
                    {{ $userRegionName }} (Homebase)
                </span>
                @endif
            </div>
            <div style="font-size:12.5px;color:#166534;font-weight:700">
                📅 {{ $date->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>

    {{-- 4 Date Navigation Tabs (Kemarin, Hari Ini, Besok, Lusa) --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:10px;margin-bottom:22px">
        @foreach($tabs as $tab)
        @php
            $isTabActive = $tab['date']->format('Y-m-d') === $date->format('Y-m-d');
            $isToday = $tab['date']->format('Y-m-d') === $today->format('Y-m-d');
        @endphp
        <a href="{{ route('menu.index', array_merge(request()->except('date'), ['date' => $tab['date']->format('Y-m-d')])) }}"
           style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:12px 14px;border-radius:12px;text-decoration:none;
                  border:1.5px solid {{ $isTabActive ? '#16a34a' : '#cbd5e1' }};
                  background:{{ $isTabActive ? '#16a34a' : '#fff' }};
                  color:{{ $isTabActive ? '#fff' : '#1e293b' }};
                  box-shadow:{{ $isTabActive ? '0 4px 12px rgba(22,163,74,.25)' : '0 1px 2px rgba(0,0,0,.03)' }};
                  transition:all .2s cubic-bezier(0.4, 0, 0.2, 1);cursor:pointer">
            <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:14px;font-weight:800">{{ $tab['label'] }}</span>
                @if($isToday && !$isTabActive)
                <span style="width:7px;height:7px;border-radius:50%;background:#16a34a"></span>
                @endif
            </div>
            <span style="font-size:11.5px;opacity:{{ $isTabActive ? '.9' : '.7' }};margin-top:2px;font-weight:600">
                {{ $tab['sublabel'] }}
            </span>
        </a>
        @endforeach
    </div>

    @if($menus->isEmpty())
    <div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
        <div class="empty-state" style="padding:48px 24px;text-align:center">
            <svg width="48" height="48" fill="none" stroke="#86efac" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v10M8 8v4a2 2 0 002 2m4-6v4a2 2 0 002 2"/></svg>
            <p style="font-weight:800;color:#166534;margin-bottom:4px;font-size:15px">Menu Belum Tersedia</p>
            <p style="font-size:13px;color:#4b5563;margin:0">Belum ada daftar hidangan yang diinput oleh tim catering untuk tanggal {{ $date->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>
    @else
    <div class="grid-2" style="gap:16px">
        @foreach($menus->flatten(1) as $menu)
        @php
            $typeColors = [
                'breakfast' => ['bg'=>'#fff7ed','border'=>'#fed7aa','accent'=>'#ea580c','text'=>'#c2410c'],
                'lunch'     => ['bg'=>'#eff6ff','border'=>'#bfdbfe','accent'=>'#2563eb','text'=>'#1d4ed8'],
                'dinner'    => ['bg'=>'#f5f3ff','border'=>'#ddd6fe','accent'=>'#7c3aed','text'=>'#6d28d9'],
                'supper'    => ['bg'=>'#fdf2f8','border'=>'#fbcfe8','accent'=>'#db2777','text'=>'#9d174d'],
            ];
            $tc = $typeColors[$menu->mealType->slug ?? 'lunch'] ?? ['bg'=>'#f0fdf4','border'=>'#bbf7d0','accent'=>'#16a34a','text'=>'#166534'];
            $avgRating = $menu->ratings->count() ? round($menu->ratings->avg('score'), 1) : null;
            $userRated = $menu->ratings->where('user_id', auth()->id())->first();
        @endphp
        <div class="card" style="background:{{ $tc['bg'] }};border:1px solid {{ $tc['border'] }};border-left:4px solid {{ $tc['accent'] }};border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
            <div style="padding:14px 18px;background:#fff;border-bottom:1px solid {{ $tc['border'] }}">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <span style="font-size:12.5px;font-weight:800;color:{{ $tc['text'] }};text-transform:uppercase;letter-spacing:.5px">{{ $menu->mealType->name }}</span>
                        <p style="font-size:11.5px;color:#64748b;margin:2px 0 0;font-weight:600">{{ $menu->region->name }} · {{ $menu->menu_date->translatedFormat('d M Y') }}</p>
                    </div>
                    @if($avgRating)
                    <div style="display:flex;align-items:center;gap:4px;background:#fffbeb;border:1px solid #fde68a;padding:3px 8px;border-radius:6px">
                        <span style="color:#f59e0b;font-size:13px">★</span>
                        <span style="font-size:12.5px;font-weight:800;color:#92400e">{{ $avgRating }}</span>
                        <span style="font-size:10px;color:#b45309">({{ $menu->ratings->count() }})</span>
                    </div>
                    @endif
                </div>
            </div>
            <div style="padding:16px 18px;background:{{ $tc['bg'] }}">
                @if($menu->description)
                <p style="font-size:12.5px;color:#475569;margin-bottom:12px;font-style:italic">{{ $menu->description }}</p>
                @endif

                {{-- Direct list of menu items without category grouping --}}
                <div style="display:flex;flex-direction:column;gap:6px">
                    @foreach($menu->items as $item)
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:6px;height:6px;border-radius:50%;background:{{ $tc['accent'] }};flex-shrink:0"></div>
                        <span style="font-size:13.5px;color:#1e293b;font-weight:600">{{ $item->name }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Rating / Edit --}}
                @php
                    $canRate = auth()->check() && auth()->user()->canRateMenu($menu);
                @endphp
                <div style="margin-top:16px;padding-top:12px;border-top:1px solid {{ $tc['border'] }};display:flex;align-items:center;justify-content:space-between">
                    @if($userRated)
                    <span style="font-size:12px;color:#475569;font-weight:600">Rating Anda: <strong style="color:#f59e0b">{{ str_repeat('★', $userRated->score) }}</strong></span>
                    @elseif($canRate)
                    <a href="{{ route('rating.create', ['menu_id'=>$menu->id]) }}" class="btn btn-outline btn-sm" style="background:#fff;font-size:11.5px;font-weight:600">
                        <span style="color:#f59e0b">★</span> Beri Rating
                    </a>
                    @else
                    <span style="font-size:11px;color:#94a3b8;font-style:italic">
                        @if($menu->menu_date->gt(\Carbon\Carbon::today()))
                            Rating dibuka setelah jam makan
                        @else
                            Tidak ada jatah makan terdaftar
                        @endif
                    </span>
                    @endif
                    @if(auth()->user()->isCatering() || auth()->user()->isGS())
                    <a href="{{ route('menu.create') }}?region_id={{ $menu->region_id }}&date={{ $menu->menu_date->format('Y-m-d') }}&meal_type_id={{ $menu->meal_type_id }}" class="btn btn-secondary btn-sm" style="background:#fff;font-size:11.5px;font-weight:600">
                        ✏️ Edit Menu
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
