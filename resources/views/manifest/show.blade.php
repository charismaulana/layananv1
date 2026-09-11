@extends('layouts.app')
@section('title', 'Detail Manifest')
@section('page-title', 'Detail Manifest')

@section('content')
<div style="max-width:920px;margin:0 auto">

    {{-- ── Header Card ── --}}
    <div class="card" style="margin-bottom:20px">
        <div class="card-header" style="background:#fafafa;padding:16px 20px">
            <div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <h3 style="font-size:16px;font-weight:800;color:#1a2332;margin:0">
                        {{ $manifest->manifest_number }}
                    </h3>
                    <span class="badge badge-blue">{{ $manifest->mealType?->name ?? 'Makan' }}</span>
                    <span style="background:#f3f4f6;padding:2px 8px;border-radius:6px;font-size:11.5px;font-weight:700">V{{ $manifest->version }}</span>
                    @if($manifest->is_gs_override)
                        <span class="badge badge-gold">GS Override</span>
                    @endif
                </div>
                <p style="font-size:12px;color:#6b7280;margin:3px 0 0">
                    📍 Wilayah: <strong>{{ $manifest->region?->name }}</strong>
                    · Lokasi: <strong>{{ $manifest->mealLocation?->name ?? 'Mess Hall' }}</strong>
                    · Tanggal: <strong>{{ $manifest->manifest_date->translatedFormat('l, d F Y') }}</strong>
                </p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('manifest.pdf', $manifest) }}" target="_blank" class="btn btn-primary btn-sm" style="background:#006738;padding:8px 14px">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak / Download PDF
                </a>
                <a href="{{ route('manifest.index') }}" class="btn btn-secondary btn-sm" style="padding:8px 14px">← Kembali</a>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:0;border-top:1px solid #f1f5f9">
            @php
                $creatorName = $manifest->generatedBy?->name ?? $manifest->creator?->name ?? 'Sistem';
                $stats = [
                    ['label'=>'Total Porsi (Pax)', 'value'=>$manifest->people->count().' pax', 'color'=>'var(--g)'],
                    ['label'=>'Dibuat Oleh',      'value'=>$creatorName,                     'color'=>'#1e293b'],
                    ['label'=>'Waktu Generate',   'value'=>$manifest->created_at->translatedFormat('d M Y H:i').' WIB', 'color'=>'#1e293b'],
                    ['label'=>'Status Dokumen',   'value'=>strtoupper($manifest->status ?? 'FINAL'), 'color'=>'#1e293b'],
                ];
            @endphp
            @foreach($stats as $st)
            <div style="padding:14px 18px;border-right:1px solid #f1f5f9;background:#fff">
                <p style="font-size:10.5px;color:#9ca3af;margin:0 0 3px;text-transform:uppercase;font-weight:700;letter-spacing:.4px">{{ $st['label'] }}</p>
                <p style="font-size:13.5px;font-weight:700;color:{{ $st['color'] }};margin:0">{{ $st['value'] }}</p>
            </div>
            @endforeach
        </div>

        @if($manifest->override_reason)
        <div style="background:#fffbeb;border-top:1px solid #fde68a;padding:12px 18px;font-size:12px;color:#92400e">
            <strong>Catatan GS Override:</strong> {{ $manifest->override_reason }}
        </div>
        @endif
    </div>

    {{-- ── Daftar Peserta Table ── --}}
    <div class="card">
        <div class="card-header" style="background:#fafafa;padding:14px 20px">
            <div style="display:flex;align-items:center;gap:8px">
                <h3 style="margin:0">Daftar Penerima Porsi Makan</h3>
                <span class="badge badge-green">{{ $manifest->people->count() }} orang</span>
            </div>
            {{-- Real-time Search Box --}}
            <input type="text" id="searchPeople" oninput="filterPeople(this.value)"
                   placeholder="🔍 Cari nama / NIP..."
                   class="form-input" style="width:220px;padding:6px 12px;font-size:12px">
        </div>
        <div style="overflow-x:auto">
            <table class="tbl" id="peopleTable">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center">No</th>
                        <th>Nama Pekerja</th>
                        <th>Perusahaan</th>
                        <th>Departemen / Jabatan</th>
                        <th>Lokasi Pengambilan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manifest->people as $i => $person)
                    <tr>
                        <td style="color:#9ca3af;font-size:12px;text-align:center;font-weight:600">{{ $i + 1 }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:28px;height:28px;border-radius:50%;background:#e8f5ee;color:#006738;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0">
                                    {{ strtoupper(substr($person->user?->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <p style="font-weight:700;color:#1a2332;margin:0;font-size:13px">{{ $person->user?->name ?? 'Pekerja' }}</p>
                                    <p style="font-size:11px;color:#9ca3af;margin:0">{{ $person->user?->nomor_pegawai ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12.5px;color:#4b5563">{{ $person->user?->company?->name ?? 'PT Pertamina EP' }}</td>
                        <td style="font-size:12.5px;color:#4b5563">{{ $person->user?->department?->name ?? $person->user?->jabatan ?? '-' }}</td>
                        <td style="font-size:12.5px;color:#4b5563">{{ $manifest->mealLocation?->name ?? 'Mess Hall' }}</td>
                        <td>
                            @if($person->mealPlan?->status === 'moved')
                                <span class="badge badge-blue" style="font-size:10px">Movement</span>
                            @elseif($person->mealPlan?->outside_meal)
                                <span class="badge badge-gold" style="font-size:10px">Outside Meal</span>
                            @else
                                <span class="badge badge-green" style="font-size:10px">Roster Normal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:32px;color:#9ca3af">
                            Tidak ada data pekerja pada batch manifest ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filterPeople(val) {
    var query = val.toLowerCase().trim();
    var rows = document.querySelectorAll('#peopleTable tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = (!query || text.includes(query)) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
