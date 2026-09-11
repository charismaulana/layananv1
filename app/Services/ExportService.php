<?php

namespace App\Services;

use App\Models\{User, Roster, MealPlan, Movement, OutsideMeal, MealCancellation,
                ManifestBatch, Menu, Rating, Suggestion, UserActivity, MealType, MealLocation, Region, VisitorMeal};
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use Illuminate\Support\Facades\Response;

class ExportService
{
    public function exportUsers(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $data = User::with(['role', 'company', 'department', 'workerStatus', 'homebaseRegion'])
            ->when($filters['is_active'] ?? null, fn($q, $v) => $q->where('is_active', $v))
            ->when($startDate, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->get()
            ->map(fn($u) => [
                'Nopeg / No SIM-L' => $u->nomor_pegawai,
                'Nama'             => $u->name,
                'Email'            => $u->email,
                'Role'             => $u->role?->name,
                'Perusahaan'       => $u->company?->name,
                'Departemen'       => $u->department?->name,
                'Jabatan'          => $u->jabatan,
                'Status Karyawan'  => $u->workerStatus?->name,
                'Homebase'         => $u->homebaseRegion?->name,
                'Shift'            => $u->is_shift ? 'Ya' : 'Tidak',
                'Status Akun'      => $u->is_active ? 'Aktif' : 'Nonaktif',
                'Tanggal Daftar'   => $u->created_at ? $u->created_at->format('d/m/Y') : '-',
            ]);

        return $this->toExcel($data->toArray(), 'users', "Daftar Pengguna");
    }

    public function exportRosters(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $query = Roster::with(['user.department', 'region'])
            ->when($startDate, fn($q, $v) => $q->whereDate('roster_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('roster_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->orderBy('roster_date')->orderBy('user_id');

        $data = $query->get()->map(fn($r) => [
            'Tanggal'      => $r->roster_date->format('d/m/Y'),
            'Nama'         => $r->user?->name,
            'Nomor Pegawai'=> $r->user?->nomor_pegawai,
            'Departemen'   => $r->user?->department?->name,
            'Status Roster'=> $r->status,
            'Wilayah'      => $r->region?->name,
        ]);

        return $this->toExcel($data->toArray(), 'rosters', "Data Roster");
    }

    public function exportMealPlans(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $query = MealPlan::with(['user.department', 'region', 'mealType', 'mealLocation'])
            ->when($startDate, fn($q, $v) => $q->whereDate('meal_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('meal_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->when($filters['meal_type_id'] ?? null, fn($q, $v) => $q->where('meal_type_id', $v))
            ->orderBy('meal_date');

        $employeeRows = $query->get()->map(fn($mp) => [
            'Tanggal'      => $mp->meal_date->format('d/m/Y'),
            'Nama'         => $mp->user?->name,
            'Nomor Pegawai'=> $mp->user?->nomor_pegawai,
            'Departemen'   => $mp->user?->department?->name,
            'Jenis Makan'  => $mp->mealType?->name,
            'Wilayah'      => $mp->region?->name,
            'Lokasi Makan' => $mp->mealLocation?->name,
            'Status'       => $mp->status,
            'Dibatalkan'   => $mp->cancelled ? 'Ya' : 'Tidak',
            'Outside Meal' => $mp->outside_meal ? 'Ya' : 'Tidak',
            'Sumber'       => $mp->source,
        ])->toArray();

        $visitorQuery = \App\Models\VisitorMeal::with(['region', 'mealLocation', 'creator'])
            ->when($startDate, fn($q, $v) => $q->whereDate('meal_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('meal_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->orderBy('meal_date');

        $visitorRows = [];
        foreach ($visitorQuery->get() as $vm) {
            $mealTypes = [
                'Breakfast' => $vm->has_breakfast,
                'Lunch'     => $vm->has_lunch,
                'Dinner'    => $vm->has_dinner,
                'Supper'    => $vm->has_supper,
            ];
            foreach ($mealTypes as $mtName => $active) {
                if ($active) {
                    $paxText = $vm->pax_count > 1 ? " ({$vm->pax_count} Pax)" : "";
                    $visitorRows[] = [
                        'Tanggal'      => $vm->meal_date->format('d/m/Y'),
                        'Nama'         => "[VISITOR] {$vm->visitor_name}{$paxText}",
                        'Nomor Pegawai'=> '-',
                        'Departemen'   => $vm->institution ?: 'Visitor / Tamu',
                        'Jenis Makan'  => $mtName,
                        'Wilayah'      => $vm->region?->name ?? 'Ramba',
                        'Lokasi Makan' => $vm->mealLocation?->name ?? 'Mess Hall',
                        'Status'       => 'Active',
                        'Dibatalkan'   => $vm->status === 'cancelled' ? 'Ya' : 'Tidak',
                        'Outside Meal' => 'Tidak',
                        'Sumber'       => 'Visitor Ad-Hoc',
                    ];
                }
            }
        }

        $allData = array_merge($employeeRows, $visitorRows);

        return $this->toExcel($allData, 'meal_plans', "Data Meal Plan");
    }

    public function exportPredictedPob(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $planCounts = MealPlan::finalActive()
            ->selectRaw('meal_date, region_id, meal_type_id, meal_location_id, COUNT(*) as pax')
            ->with(['region', 'mealType', 'mealLocation'])
            ->when($startDate, fn($q, $v) => $q->whereDate('meal_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('meal_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->groupBy('meal_date', 'region_id', 'meal_type_id', 'meal_location_id')
            ->get();

        $mealTypes = MealType::active()->get();

        $visitorQuery = \App\Models\VisitorMeal::with(['region', 'mealLocation'])
            ->where('status', 'confirmed')
            ->when($startDate, fn($q, $v) => $q->whereDate('meal_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('meal_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->get();

        // Build composite matrix: date_region_location_mealtype
        $matrix = [];

        foreach ($planCounts as $pc) {
            $d = Carbon::parse($pc->meal_date)->format('Y-m-d');
            $k = "{$d}_{$pc->region_id}_{$pc->meal_location_id}_{$pc->meal_type_id}";
            $matrix[$k] = [
                'date'          => $d,
                'region_name'   => $pc->region?->name ?? '-',
                'location_name' => $pc->mealLocation?->name ?? 'Mess Hall',
                'meal_type'     => $pc->mealType?->name ?? '-',
                'employee_pax'  => (int)$pc->pax,
                'visitor_pax'   => 0,
            ];
        }

        foreach ($visitorQuery as $vm) {
            $d = $vm->meal_date->format('Y-m-d');
            $mtMap = [
                'breakfast' => $vm->has_breakfast,
                'lunch'     => $vm->has_lunch,
                'dinner'    => $vm->has_dinner,
                'supper'    => $vm->has_supper,
            ];

            foreach ($mealTypes as $mt) {
                $slug = strtolower($mt->slug);
                if (!empty($mtMap[$slug])) {
                    $k = "{$d}_{$vm->region_id}_{$vm->meal_location_id}_{$mt->id}";
                    if (!isset($matrix[$k])) {
                        $matrix[$k] = [
                            'date'          => $d,
                            'region_name'   => $vm->region?->name ?? '-',
                            'location_name' => $vm->mealLocation?->name ?? 'Mess Hall',
                            'meal_type'     => $mt->name,
                            'employee_pax'  => 0,
                            'visitor_pax'   => 0,
                        ];
                    }
                    $matrix[$k]['visitor_pax'] += (int)$vm->pax_count;
                }
            }
        }

        // Sort by date ascending
        ksort($matrix);

        $rows = [];
        foreach ($matrix as $item) {
            $totalPax = $item['employee_pax'] + $item['visitor_pax'];
            $rows[] = [
                'Tanggal'          => Carbon::parse($item['date'])->format('d/m/Y'),
                'Wilayah'          => $item['region_name'],
                'Lokasi Makan'     => $item['location_name'],
                'Jenis Makan'      => $item['meal_type'],
                'Pax Karyawan'     => $item['employee_pax'],
                'Pax Visitor'      => $item['visitor_pax'],
                'Total Predicted'  => $totalPax,
            ];
        }

        return $this->toExcel($rows, 'predicted_pob', "Predicted POB");
    }

    public function exportMovements(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $movements = Movement::with(['requester', 'fromRegion', 'toRegion', 'people.user'])
            ->when($startDate, fn($q, $v) => $q->whereDate('movement_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('movement_date', '<=', $v))
            ->orderBy('movement_date', 'desc')
            ->get();

        $rows = [];
        foreach ($movements as $m) {
            foreach ($m->people as $p) {
                $rows[] = [
                    'Tanggal Pindah'  => $m->movement_date ? $m->movement_date->format('d/m/Y') : '-',
                    'Nama Karyawan'   => $p->user?->name,
                    'Nopeg'           => $p->user?->nomor_pegawai,
                    'Dari Wilayah'    => $m->fromRegion?->name,
                    'Ke Wilayah'      => $m->toRegion?->name,
                    'Status'          => ucfirst($m->status),
                    'Pemohon'         => $m->requester?->name,
                    'Catatan'         => $m->notes ?: '-',
                ];
            }
        }

        return $this->toExcel($rows, 'movement_log', "Movement Log");
    }

    public function exportUserActivities(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $data = UserActivity::with('user')
            ->when($startDate, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->get()
            ->map(fn($a) => [
                'Waktu'        => $a->created_at->format('d/m/Y H:i:s'),
                'Pengguna'     => $a->user?->name,
                'Modul'        => $a->module,
                'Aktivitas'    => $a->activity_type,
                'Deskripsi'    => $a->description,
                'IP Address'   => $a->ip_address,
            ]);

        return $this->toExcel($data->toArray(), 'user_activities', "Log Aktivitas Pengguna");
    }

    public function exportFeedback(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;
        $regionId  = $filters['region_id'] ?? null;

        // 1. Ratings Data
        $ratingsQuery = Rating::with(['user.department', 'user.company', 'menu.items', 'region', 'mealType'])
            ->when($startDate, fn($q, $v) => $q->whereDate('rating_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('rating_date', '<=', $v))
            ->when($regionId,  fn($q, $v) => $q->where('region_id', $v))
            ->orderBy('rating_date', 'desc');

        $ratingsData = $ratingsQuery->get()->map(fn($r) => [
            'Tanggal'      => $r->rating_date ? $r->rating_date->format('d/m/Y') : '-',
            'Nama Menu'    => $r->menu ? $r->menu->items->pluck('name')->implode(', ') : '-',
            'Wilayah'      => $r->region?->name ?? '-',
            'Jenis Makan'  => $r->mealType?->name ?? '-',
            'Skor Bintang' => $r->score . ' / 5',
            'Komentar'     => $r->comment ?: '-',
            'Pengulas'     => $r->user?->name ?? 'Anonim',
            'Nopeg'        => $r->user?->nomor_pegawai ?? '-',
            'Departemen'   => $r->user?->department?->name ?? '-',
            'Perusahaan'   => $r->user?->company?->name ?? 'PT Pertamina EP',
        ])->toArray();

        // 2. Suggestions Data
        $suggestionsQuery = Suggestion::with(['user.department', 'user.company', 'region'])
            ->when($startDate, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($regionId,  fn($q, $v) => $q->where('region_id', $v))
            ->orderBy('created_at', 'desc');

        $suggestionsData = $suggestionsQuery->get()->map(fn($s) => [
            'Waktu Masuk'  => $s->created_at ? $s->created_at->format('d/m/Y H:i') : '-',
            'Pengirim'     => $s->user?->name ?? 'Anonim',
            'Nopeg'        => $s->user?->nomor_pegawai ?? '-',
            'Departemen'   => $s->user?->department?->name ?? '-',
            'Wilayah'      => $s->region?->name ?? '-',
            'Kategori'     => ucfirst($s->category ?: 'Umum'),
            'Isi Saran'    => $s->content,
            'Status'       => $s->is_read ? 'Sudah Ditinjau' : 'Belum Ditinjau',
        ])->toArray();

        return Excel::download(
            new \App\Exports\FeedbackMultiSheetExport($ratingsData, $suggestionsData),
            "rekap_rating_dan_saran_" . now()->format('Ymd_His') . ".xlsx"
        );
    }

    public function exportMenus(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $startDate = $filters['from'] ?? $filters['start_date'] ?? null;
        $endDate   = $filters['to']   ?? $filters['end_date']   ?? null;

        $query = Menu::with(['region', 'mealType', 'items', 'createdBy'])
            ->when($startDate, fn($q, $v) => $q->whereDate('menu_date', '>=', $v))
            ->when($endDate,   fn($q, $v) => $q->whereDate('menu_date', '<=', $v))
            ->when($filters['region_id'] ?? null, fn($q, $v) => $q->where('region_id', $v))
            ->orderBy('menu_date')
            ->orderBy('region_id')
            ->orderBy('meal_type_id');

        $data = $query->get()->map(function($m) {
            $itemsList = $m->items->pluck('name')->implode(', ');
            $mainDish = $m->items->where('category', 'main')->pluck('name')->implode(', ') ?: '-';
            $sideDish = $m->items->where('category', 'side')->pluck('name')->implode(', ') ?: '-';
            $veggie   = $m->items->where('category', 'vegetable')->pluck('name')->implode(', ') ?: '-';
            $dessert  = $m->items->where('category', 'dessert')->pluck('name')->implode(', ') ?: '-';

            return [
                'Tanggal Menu'   => $m->menu_date ? $m->menu_date->format('d/m/Y') : '-',
                'Hari'           => $m->menu_date ? $m->menu_date->translatedFormat('l') : '-',
                'Wilayah Dapur'  => $m->region?->name ?? '-',
                'Waktu Makan'    => $m->mealType?->name ?? '-',
                'Daftar Menu'    => $itemsList ?: ($m->description ?: '-'),
                'Menu Utama'     => $mainDish,
                'Lauk Pendamping'=> $sideDish,
                'Sayur / Sup'    => $veggie,
                'Buah / Dessert' => $dessert,
                'Deskripsi / Catatan' => $m->description ?? '-',
                'Diupload Oleh'  => $m->createdBy?->name ?? 'Penyedia Katering',
                'Waktu Upload'   => $m->created_at ? $m->created_at->format('d/m/Y H:i') : '-',
            ];
        });

        return $this->toExcel($data->toArray(), 'rekap_menu_catering', "Rekap Menu Katering");
    }

    public function exportMissingRoster(array $filters = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $date = isset($filters['date']) ? Carbon::parse($filters['date']) : Carbon::today();

        // Query identik dengan DashboardController@gsIndex
        $users = User::with(['role', 'company', 'department', 'homebaseRegion'])
            ->where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', '!=', 'catering'))
            ->whereDoesntHave('rosters', fn($rq) => $rq->whereDate('roster_date', $date))
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'Nama'             => $u->name,
                'Nopeg / No SIM-L' => $u->nomor_pegawai ?? '-',
                'Role'             => $u->role?->name ?? '-',
                'Perusahaan'       => $u->company?->name ?? '-',
                'Departemen'       => $u->department?->name ?? '-',
                'Jabatan'          => $u->jabatan ?? '-',
                'Homebase'         => $u->homebaseRegion?->name ?? '-',
                'Status'           => 'Belum Mengisi Roster',
                'Tanggal'          => $date->translatedFormat('d F Y'),
            ]);

        return $this->toExcel($users->toArray(), 'pekerja_belum_isi_roster', "Belum Isi Roster – {$date->format('d-m-Y')}");
    }

    private function toExcel(array $data, string $filename, string $sheetTitle): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new GenericExport($data, $sheetTitle),
            "{$filename}_" . now()->format('Ymd_His') . ".xlsx"
        );
    }
}
