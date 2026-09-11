@extends('layouts.app')
@section('title', 'Manifest & Daftar Hadir')
@section('page-title', 'Manifest & Daftar Hadir')

@section('content')
@php
    $canGenerate = auth()->user()->isGS() || auth()->user()->isCatering() || auth()->user()->isSysAdmin();
    $isGS = auth()->user()->isGS() || auth()->user()->isSysAdmin();

    $cardStyles = [
        'ramba-staff'    => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'accent' => '#16a34a', 'text' => '#15803d', 'icon' => '🏢'],
        'ramba-nonstaff' => ['bg' => '#faf5ff', 'border' => '#e9d5ff', 'accent' => '#9333ea', 'text' => '#7e22ce', 'icon' => '🏢'],
        'bentayan'       => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'accent' => '#2563eb', 'text' => '#1d4ed8', 'icon' => '🏢'],
        'mangunjaya'     => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'accent' => '#ea580c', 'text' => '#c2410c', 'icon' => '🏢'],
        'kluang'         => ['bg' => '#fefce8', 'border' => '#fef08a', 'accent' => '#d97706', 'text' => '#a16207', 'icon' => '🏢'],
    ];
@endphp

{{-- ── 1. Date Filter & Print Setting Bar ── --}}
<div class="card" style="margin-bottom:20px;background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.03)">
    <div class="card-body" style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <form method="GET" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:22px">📅</span>
                <div>
                    <label class="form-label" style="margin-bottom:4px;font-size:12px;font-weight:700;color:#15803d">Pilih Tanggal Pelaksanaan Makan</label>
                    <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
                           class="form-input" style="font-weight:700;font-size:13px;min-width:180px;background:#fff;border-color:#bbf7d0">
                </div>
            </div>
        </form>

        @if($canGenerate)
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div style="font-size:12px;color:#166534">
                <div>🏢 Katering: <strong>{{ \App\Services\ManifestService::getCateringVendorName() }}</strong></div>
                <div style="margin-top:2px">✍️ Mengetahui GS: <strong>{{ \App\Services\ManifestService::getGsOfficerName() }}</strong></div>
            </div>
            <button type="button" onclick="openPrintSettingModal()" class="btn btn-secondary btn-sm" style="padding:7px 14px;font-size:12px;font-weight:700;border-color:#86efac;color:#15803d;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)">
                ⚙️ Atur Tanda Tangan PDF
            </button>
        </div>
        @else
        <div style="font-size:12px;color:#166534">
            <div>🏢 Katering: <strong>{{ \App\Services\ManifestService::getCateringVendorName() }}</strong></div>
            <div style="margin-top:2px">✍️ Mengetahui GS: <strong>{{ \App\Services\ManifestService::getGsOfficerName() }}</strong></div>
        </div>
        @endif
    </div>
</div>

{{-- ── 2. PUSAT PENCETAKAN DAFTAR HADIR MESS HALL & PENGANTARAN ── --}}
<div style="margin-bottom:28px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
        <div>
            <h2 style="font-size:16px;font-weight:800;color:#1a2332;margin:0">
                Cetak Daftar Hadir Mess Hall
            </h2>
            <p style="font-size:12px;color:#6b7280;margin:3px 0 0">
                Cetak daftar hadir untuk diletakkan di mess hall dan ditandatangani karyawan
            </p>
        </div>
    </div>

    <div class="grid-2" style="gap:16px">
        {{-- 5 MESS HALL CARDS --}}
        @foreach($messHallsSummary as $key => $mh)
        @php
            $cs = $cardStyles[$key] ?? ['bg' => '#f8fafc', 'border' => '#e2e8f0', 'accent' => '#006738', 'text' => '#006738', 'icon' => '🏢'];
        @endphp
        <div class="card" style="background:{{ $cs['bg'] }};border:1px solid {{ $cs['border'] }};border-left:4px solid {{ $cs['accent'] }};border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03);transition:transform .12s">
            <div style="padding:14px 18px;border-bottom:1px solid {{ $cs['border'] }};display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.6)">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">{{ $cs['icon'] }}</span>
                    <h3 style="font-size:14.5px;font-weight:800;color:#1a2332;margin:0">{{ $mh['name'] }}</h3>
                </div>
            </div>

            <div style="padding:16px 18px">
                {{-- Breakdown Pax: B'fast, Lunch, Dinner, Supper --}}
                <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:8px;margin-bottom:14px;background:#fff;padding:10px 12px;border-radius:10px;border:1px solid {{ $cs['border'] }};text-align:center">
                    <div>
                        <p style="font-size:10px;color:#c2410c;font-weight:700;margin:0">B'fast</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $mh['breakfast_pax'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#1d4ed8;font-weight:700;margin:0">Lunch</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $mh['lunch_pax'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6d28d9;font-weight:700;margin:0">Dinner</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $mh['dinner_pax'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#9d174d;font-weight:700;margin:0">Supper</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $mh['supper_pax'] }}</p>
                    </div>
                </div>

                {{-- Action Buttons & Total Workers --}}
                <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap">
                    <span style="font-size:12px;color:#475569">
                        👥 <strong>{{ $mh['total_workers'] }}</strong> karyawan terdaftar
                    </span>

                    <div style="display:flex;gap:6px">
                        <a href="{{ route('manifest.messhall.show', ['date' => $date->format('Y-m-d'), 'key' => $key]) }}"
                           class="btn btn-secondary btn-sm" style="font-weight:600;font-size:12px;background:#fff">
                            👁️ Lihat Tabel (A-Z)
                        </a>
                        <a href="{{ route('manifest.messhall.pdf', ['date' => $date->format('Y-m-d'), 'key' => $key]) }}" target="_blank"
                           class="btn btn-primary btn-sm" style="background:{{ $cs['accent'] }};border-color:{{ $cs['accent'] }};color:#fff;font-weight:700;font-size:12px">
                            🖨️ Cetak Lembar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- 6. DELIVERY & DROP-POINT MANIFEST CARD WITH SUB-LOCATION CHECKLIST --}}
        <div class="card" style="background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.03)">
            <div style="padding:14px 18px;border-bottom:1px solid #bae6fd;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.6)">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">🚚</span>
                    <h3 style="font-size:14.5px;font-weight:800;color:#0369a1;margin:0">Pengantaran & Drop-point</h3>
                </div>
            </div>

            <div style="padding:16px 18px">
                {{-- Breakdown Pax: B'fast, Lunch, Dinner, Supper --}}
                <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:8px;margin-bottom:14px;background:#fff;padding:10px 12px;border-radius:10px;border:1px solid #bae6fd;text-align:center">
                    <div>
                        <p style="font-size:10px;color:#c2410c;font-weight:700;margin:0">B'fast</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $deliverySummary['totals']['breakfast'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#1d4ed8;font-weight:700;margin:0">Lunch</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $deliverySummary['totals']['lunch'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#6d28d9;font-weight:700;margin:0">Dinner</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $deliverySummary['totals']['dinner'] }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#9d174d;font-weight:700;margin:0">Supper</p>
                        <p style="font-size:15px;font-weight:800;color:#1a2332;margin:2px 0 0">{{ $deliverySummary['totals']['supper'] }}</p>
                    </div>
                </div>

                {{-- Checklist Sub-Lokasi Pengantaran & Tombol Cetak PDF per 1 Wilayah --}}
                @php
                    $hasAnyDelivery = !empty($deliverySummary['available_locations']);
                @endphp

                @if($hasAnyDelivery)
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
                        @foreach($deliverySummary['regions_data'] as $regName => $regData)
                        @if(count($regData['available_locations']) > 0 || count($regData['rows']) > 0)
                        <form method="GET" action="{{ route('manifest.delivery.pdf', ['date' => $date->format('Y-m-d')]) }}" target="_blank"
                              style="background:#fff;border:1px solid #cbd5e1;border-radius:10px;padding:12px">
                            <input type="hidden" name="region_id" value="{{ $regData['region']->id }}">

                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #f1f5f9">
                                <div style="display:flex;align-items:center;gap:6px">
                                    <span style="font-size:11.5px;font-weight:800;color:#0284c7;text-transform:uppercase;letter-spacing:.3px">
                                        📍 Wilayah {{ $regName }}
                                    </span>
                                    <span class="badge badge-blue" style="font-size:10px;padding:2px 6px">
                                        {{ count($regData['rows']) }} orang · {{ $regData['grand_total'] }} pax
                                    </span>
                                </div>
                                <button type="button" onclick="toggleFormCheckboxes(this)" style="font-size:11px;color:#0284c7;background:transparent;border:none;cursor:pointer;font-weight:700;padding:0">
                                    Pilih / Batal
                                </button>
                            </div>

                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:6px;margin-bottom:10px">
                                @foreach($regData['available_locations'] as $loc)
                                <label style="display:flex;align-items:center;gap:8px;font-size:11.5px;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;cursor:pointer">
                                    <input type="checkbox" name="location_ids[]" value="{{ $loc['id'] }}" checked class="delivery-loc-checkbox" style="accent-color:#0284c7;width:14px;height:14px">
                                    <span style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $loc['name'] }}</span>
                                    <span style="margin-left:auto;font-size:10px;color:#0284c7;background:#e0f2fe;font-weight:700;padding:1px 5px;border-radius:4px">{{ $loc['count'] }} pax</span>
                                </label>
                                @endforeach
                            </div>

                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary btn-sm" style="background:#0284c7;border-color:#0284c7;color:#fff;font-weight:700;font-size:11.5px">
                                    🖨️ Cetak PDF Wilayah {{ $regName }}
                                </button>
                            </div>
                        </form>
                        @endif
                        @endforeach
                    </div>
                @else
                    <div style="background:#fff;border:1px solid #cbd5e1;border-radius:10px;padding:14px;margin-bottom:14px">
                        <p style="font-size:11.5px;color:#94a3b8;margin:0">Tidak ada pesanan pengantaran pada tanggal ini.</p>
                    </div>
                @endif

                {{-- Action Buttons & Total Deliveries --}}
                <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap">
                    <span style="font-size:12px;color:#0369a1">
                        📦 <strong>{{ count($deliverySummary['rows']) }}</strong> total pesanan diantar
                    </span>

                    <div style="display:flex;gap:6px">
                        <a href="{{ route('manifest.delivery.show', ['date' => $date->format('Y-m-d')]) }}"
                           class="btn btn-secondary btn-sm" style="font-weight:600;font-size:12px;background:#fff">
                            👁️ Lihat Rekap Semua Wilayah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── MODAL: PENGATURAN TANDA TANGAN & CETAK PDF ── --}}
<div id="printSettingModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:16px" onclick="if(event.target===this) closePrintSettingModal()">
    <div style="background:#fff;border-radius:16px;max-width:480px;width:100%;box-shadow:0 20px 40px rgba(0,0,0,.2);overflow:hidden;animation:fadeIn .2s ease-out">
        <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-size:15px;font-weight:800;color:#1a2332;margin:0">Pengaturan Tanda Tangan Cetak PDF</h3>
            <button type="button" onclick="closePrintSettingModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1">✕</button>
        </div>

        <form method="POST" action="{{ route('manifest.setting.vendor') }}" style="padding:20px">
            @csrf
            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                    🏢 Nama Mitra Penyedia Katering
                </label>
                <input type="text" name="catering_vendor_name" value="{{ \App\Services\ManifestService::getCateringVendorName() }}"
                       class="form-input" style="font-size:13px" placeholder="Contoh: PT Brylian Indah" required>
                <p class="form-hint">Nama perusahaan mitra katering yang akan dicetak di lembar manifest.</p>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                    ✍️ Nama Penanggung Jawab Mengetahui (GS)
                </label>
                <input type="text" name="gs_officer_name" value="{{ \App\Services\ManifestService::getGsOfficerName() }}"
                       class="form-input" style="font-size:13px" placeholder="Nama Pejabat / Staf GS" required>
                <p class="form-hint">Nama orang/pejabat GS yang dicetak pada kolom "Mengetahui".</p>
            </div>

            <div class="form-group" style="margin-bottom:20px">
                <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                    💼 Jabatan / Bagian GS
                </label>
                <input type="text" name="gs_officer_title" value="{{ \App\Services\ManifestService::getGsOfficerTitle() }}"
                       class="form-input" style="font-size:13px" placeholder="Contoh: General Services Field Ramba / Sr. Spv GS">
                <p class="form-hint">Keterangan jabatan di bawah nama penandatangan GS.</p>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid #f1f5f9">
                <button type="button" onclick="closePrintSettingModal()" class="btn btn-secondary btn-sm" style="padding:8px 16px;font-weight:600">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary btn-sm" style="padding:8px 18px;font-weight:700">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPrintSettingModal() {
    document.getElementById('printSettingModal').style.display = 'flex';
}
function closePrintSettingModal() {
    document.getElementById('printSettingModal').style.display = 'none';
}

function toggleFormCheckboxes(btn) {
    var form = btn.closest('form');
    if (!form) return;
    var checkboxes = form.querySelectorAll('.delivery-loc-checkbox');
    var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });
    checkboxes.forEach(function(cb) {
        cb.checked = !allChecked;
    });
}
</script>
@endpush
@endsection
