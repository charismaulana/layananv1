<?php

namespace App\Services;

use App\Models\{ManifestBatch, ManifestPerson, MealPlan, MealLocation, MealType, Region, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ManifestService
{
    public function __construct(private AuditService $audit) {}

    /**
     * The 5 Official Mess Halls / Catering Distribution Centers in Field Ramba
     */
    public static function getMessHalls(): array
    {
        return [
            'ramba-staff' => [
                'key'           => 'ramba-staff',
                'name'          => 'Mess Hall Staff Ramba',
                'region_slug'   => 'ramba',
                'region_name'   => 'Ramba',
                'location_name' => 'Mess Hall Staff Ramba',
                'type'          => 'Staff & Pekerja Organik',
                'color'         => '#006738',
                'bg'            => '#e8f5ee',
            ],
            'ramba-nonstaff' => [
                'key'           => 'ramba-nonstaff',
                'name'          => 'Mess Hall Non Staff Ramba',
                'region_slug'   => 'ramba',
                'region_name'   => 'Ramba',
                'location_name' => 'Mess Hall Non Staff Ramba',
                'type'          => 'Nonstaff, TKJP & Sub-Contractor',
                'color'         => '#1d4ed8',
                'bg'            => '#eff6ff',
            ],
            'bentayan' => [
                'key'           => 'bentayan',
                'name'          => 'Mess Hall Bentayan',
                'region_slug'   => 'bentayan',
                'region_name'   => 'Bentayan',
                'location_name' => 'Mess Hall Bentayan',
                'type'          => 'Dapur Lapangan Bentayan',
                'color'         => '#c2410c',
                'bg'            => '#fff7ed',
            ],
            'mangunjaya' => [
                'key'           => 'mangunjaya',
                'name'          => 'Mess Hall Mangunjaya',
                'region_slug'   => 'mangunjaya',
                'region_name'   => 'Mangunjaya',
                'location_name' => 'Mess Hall Mangunjaya',
                'type'          => 'Dapur Lapangan Mangunjaya',
                'color'         => '#6d28d9',
                'bg'            => '#f5f3ff',
            ],
            'kluang' => [
                'key'           => 'kluang',
                'name'          => 'Mess Hall Kluang',
                'region_slug'   => 'kluang',
                'region_name'   => 'Kluang',
                'location_name' => 'Mess Hall Kluang',
                'type'          => 'Dapur Lapangan Kluang',
                'color'         => '#065f46',
                'bg'            => '#ecfdf5',
            ],
        ];
    }

    /**
     * Get collected data for all 5 Mess Halls on a specific date.
     */
    public function getAllMessHallsSummary(Carbon $date): array
    {
        $messHalls = self::getMessHalls();
        $summaries = [];

        foreach ($messHalls as $key => $mh) {
            $data = $this->getMessHallManifestData($date, $key);
            $summaries[$key] = array_merge($mh, [
                'total_workers' => count($data['rows']),
                'breakfast_pax'=> $data['totals']['breakfast'] ?? 0,
                'lunch_pax'    => $data['totals']['lunch'] ?? 0,
                'dinner_pax'   => $data['totals']['dinner'] ?? 0,
                'supper_pax'   => $data['totals']['supper'] ?? 0,
                'grand_total'  => ($data['totals']['breakfast'] ?? 0) + ($data['totals']['lunch'] ?? 0) + ($data['totals']['dinner'] ?? 0) + ($data['totals']['supper'] ?? 0),
            ]);
        }

        return $summaries;
    }

    /**
     * Determine default assigned Mess Hall key for a user.
     */
    public static function getUserDefaultMessHallKey(User $user): string
    {
        if ($user->meal_location_id) {
            $locSlug = strtolower($user->mealLocation?->slug ?? '');
            if (str_contains($locSlug, 'nonstaff')) return 'ramba-nonstaff';
            if (str_contains($locSlug, 'staff')) return 'ramba-staff';
        }

        $regionSlug = strtolower($user->homebaseRegion?->slug ?? 'ramba');
        if ($regionSlug === 'bentayan') return 'bentayan';
        if ($regionSlug === 'mangunjaya') return 'mangunjaya';
        if ($regionSlug === 'kluang') return 'kluang';

        // In Ramba: worker status differentiates Staff vs Nonstaff
        $statusSlug = strtolower($user->workerStatus?->slug ?? '');
        if (in_array($statusSlug, ['tkjp', 'sub-contractor', 'kontraktor'])) {
            return 'ramba-nonstaff';
        }

        return 'ramba-staff';
    }

    /**
     * Determine target Mess Hall key for a specific MealPlan.
     */
    public static function getPlanMessHallKey(MealPlan $plan): string
    {
        $regionSlug = strtolower($plan->region?->slug ?? 'ramba');
        if ($regionSlug === 'bentayan') return 'bentayan';
        if ($regionSlug === 'mangunjaya') return 'mangunjaya';
        if ($regionSlug === 'kluang') return 'kluang';

        $locSlug = strtolower($plan->mealLocation?->slug ?? '');
        if (str_contains($locSlug, 'nonstaff')) return 'ramba-nonstaff';
        if (str_contains($locSlug, 'staff')) return 'ramba-staff';

        $statusSlug = strtolower($plan->user?->workerStatus?->slug ?? '');
        if (in_array($statusSlug, ['tkjp', 'sub-contractor', 'kontraktor'])) {
            return 'ramba-nonstaff';
        }

        return 'ramba-staff';
    }

    /**
     * Collect manifest table data for a specific Mess Hall on a date.
     * Sorted strictly by User Name Alphabetical (A-Z).
     */
    public function getMessHallManifestData(Carbon $date, string $key): array
    {
        $messHalls = self::getMessHalls();
        $config = $messHalls[$key] ?? $messHalls['ramba-staff'];

        // 1. Fetch ALL meal plans for the date across all locations
        $allDayPlans = MealPlan::whereDate('meal_date', $date)
            ->with(['user.department', 'user.workerStatus', 'user.company', 'user.homebaseRegion', 'user.mealLocation', 'mealType', 'mealLocation', 'region'])
            ->get();

        $plansGroupedByUser = $allDayPlans->groupBy('user_id');

        // 2. Process all users with plans and include those who have meals prepared by or assigned to THIS Mess Hall
        $allUsers = User::whereIn('id', $plansGroupedByUser->keys())
            ->with(['department', 'workerStatus', 'company', 'homebaseRegion', 'mealLocation', 'role'])
            ->orderBy('name', 'asc')
            ->get();

        $rows = [];
        $totals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0];

        foreach ($allUsers as $u) {
            $userPlans = $plansGroupedByUser->get($u->id) ?? collect();

            $breakfastPlan = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'breakfast');
            $lunchPlan     = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'lunch');
            $dinnerPlan    = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'dinner');
            $supperPlan    = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'supper');

            $bfStatus = $this->formatMealCellForMessHall($breakfastPlan, $key, $u);
            $luStatus = $this->formatMealCellForMessHall($lunchPlan, $key, $u);
            $diStatus = $this->formatMealCellForMessHall($dinnerPlan, $key, $u);
            $suStatus = $this->formatMealCellForMessHall($supperPlan, $key, $u);

            // Include user if they have AT LEAST ONE meal prepared by this kitchen / mess hall
            $hasMealInThisMessHall = $bfStatus['is_prepared'] || $luStatus['is_prepared'] || $diStatus['is_prepared'] || $suStatus['is_prepared'];

            if (!$hasMealInThisMessHall) {
                continue; // Skip: do not print a row if user has zero meals prepared by this mess hall
            }

            if ($bfStatus['active'] && $bfStatus['is_prepared']) $totals['breakfast']++;
            if ($luStatus['active'] && $luStatus['is_prepared']) $totals['lunch']++;
            if ($diStatus['active'] && $diStatus['is_prepared']) $totals['dinner']++;
            if ($suStatus['active'] && $suStatus['is_prepared']) $totals['supper']++;

            $rows[] = [
                'number'        => count($rows) + 1,
                'user'          => $u,
                'name'          => $u->name,
                'nomor_pegawai' => $u->nomor_pegawai ?? '-',
                'jabatan'       => !empty($u->jabatan) ? $u->jabatan : ($u->role?->name ?? 'Staff'),
                'worker_status' => $u->workerStatus?->name ?? ($u->role?->name ?? 'Pekerja'),
                'department'    => $u->department?->name ?? ($u->company?->name ?? 'Pertamina EP'),
                'is_shift'      => $u->is_shift,
                'is_visitor'    => false,
                'breakfast'     => $bfStatus,
                'lunch'         => $luStatus,
                'dinner'        => $diStatus,
                'supper'        => $suStatus,
            ];
        }

        // 3. Process Visitor Meals registered for this Mess Hall on this date
        $visitorMeals = \App\Models\VisitorMeal::whereDate('meal_date', $date)
            ->where('status', 'confirmed')
            ->with(['region', 'mealLocation', 'creator'])
            ->get();

        foreach ($visitorMeals as $v) {
            $vMhKey = self::getLocationMessHallKey($v->mealLocation);
            if ($vMhKey !== $key) continue;

            $bfPax = $v->has_breakfast ? $v->pax_count : 0;
            $luPax = $v->has_lunch ? $v->pax_count : 0;
            $diPax = $v->has_dinner ? $v->pax_count : 0;
            $suPax = $v->has_supper ? $v->pax_count : 0;

            if ($bfPax > 0) $totals['breakfast'] += $bfPax;
            if ($luPax > 0) $totals['lunch'] += $luPax;
            if ($diPax > 0) $totals['dinner'] += $diPax;
            if ($suPax > 0) $totals['supper'] += $suPax;

            $formatVisitorCell = function(bool $active, int $pax) {
                if (!$active || $pax <= 0) {
                    return [
                        'active'      => false,
                        'type'        => 'none',
                        'label'       => '-',
                        'badge'       => 'badge-gray',
                        'is_prepared' => false,
                        'is_signable' => false,
                    ];
                }
                return [
                    'active'      => true,
                    'type'        => 'messhall',
                    'label'       => "Paraf ({$pax} pax)",
                    'badge'       => 'badge-green',
                    'is_prepared' => true,
                    'is_signable' => true,
                    'pax'         => $pax,
                ];
            };

            $paxLabel = $v->pax_count > 1 ? " ({$v->pax_count} Orang)" : "";

            $rows[] = [
                'number'        => count($rows) + 1,
                'user'          => null,
                'name'          => "[VISITOR] {$v->visitor_name}{$paxLabel}",
                'nomor_pegawai' => 'VISITOR',
                'jabatan'       => 'Tamu / Visitor' . ($v->notes ? " — {$v->notes}" : ''),
                'worker_status' => 'Visitor',
                'department'    => $v->institution ?: 'Tamu Perusahaan',
                'is_shift'      => $v->has_supper,
                'is_visitor'    => true,
                'pax_count'     => $v->pax_count,
                'breakfast'     => $formatVisitorCell($v->has_breakfast, $v->pax_count),
                'lunch'         => $formatVisitorCell($v->has_lunch, $v->pax_count),
                'dinner'        => $formatVisitorCell($v->has_dinner, $v->pax_count),
                'supper'        => $formatVisitorCell($v->has_supper, $v->pax_count),
            ];
        }

        return [
            'config'          => $config,
            'date'            => $date,
            'rows'            => $rows,
            'totals'          => $totals,
            'grand_total'     => array_sum($totals),
            'catering_vendor' => self::getCateringVendorName(),
            'gs_officer_name' => self::getGsOfficerName(),
            'gs_officer_title'=> self::getGsOfficerTitle(),
        ];
    }

    /**
     * Determine target Mess Hall key for a MealLocation.
     */
    public static function getLocationMessHallKey(?MealLocation $location): string
    {
        if (!$location) return 'ramba-staff';
        $regionSlug = strtolower($location->region?->slug ?? '');
        if ($regionSlug === 'bentayan') return 'bentayan';
        if ($regionSlug === 'mangunjaya') return 'mangunjaya';
        if ($regionSlug === 'kluang') return 'kluang';

        $locSlug = strtolower($location->slug ?? '');
        if (str_contains($locSlug, 'nonstaff')) return 'ramba-nonstaff';

        return 'ramba-staff';
    }

    /**
     * Get Catering Vendor / Company Name from Persistent Setting, with fallback to Catering user or default.
     */
    public static function getCateringVendorName(): string
    {
        $settingValue = \App\Models\AppSetting::get('catering_vendor_name');
        if (!empty($settingValue)) {
            return $settingValue;
        }

        if (auth()->check() && auth()->user()->isCatering()) {
            $company = auth()->user()->company_name ?: auth()->user()->company?->name;
            if (!empty($company)) return $company;
        }

        $cateringUser = User::whereHas('role', fn($q) => $q->where('slug', 'catering'))
            ->where(function($q) {
                $q->whereNotNull('company_name')->where('company_name', '!=', '')
                  ->orWhereNotNull('company_id');
            })
            ->first();

        if ($cateringUser) {
            $company = $cateringUser->company_name ?: $cateringUser->company?->name;
            if (!empty($company)) return $company;
        }

        return 'PT Brylian Indah';
    }

    /**
     * Get GS Officer / Approver Name from Persistent Setting.
     */
    public static function getGsOfficerName(): string
    {
        $settingValue = \App\Models\AppSetting::get('gs_officer_name');
        if (!empty($settingValue)) {
            return $settingValue;
        }

        if (auth()->check() && auth()->user()->isGS()) {
            return auth()->user()->name;
        }

        return 'General Services Field Ramba';
    }

    /**
     * Get GS Officer / Approver Title from Persistent Setting.
     */
    public static function getGsOfficerTitle(): string
    {
        $settingValue = \App\Models\AppSetting::get('gs_officer_title');
        if (!empty($settingValue)) {
            return $settingValue;
        }

        return 'General Services Field Ramba';
    }

    /**
     * Format status text for each meal cell on a specific Mess Hall sheet.
     */
    private function formatMealCellForMessHall(?MealPlan $plan, string $currentMessHallKey, User $user): array
    {
        if (!$plan) {
            return [
                'active'      => false,
                'type'        => 'none',
                'label'       => '-',
                'badge'       => 'badge-gray',
                'is_prepared' => false,
                'is_signable' => false,
            ];
        }

        // Batal Makan (Cancelled Meal Plan)
        if ($plan->status === 'cancelled' || $plan->cancelled) {
            return [
                'active'      => false,
                'type'        => 'cancelled',
                'label'       => 'Batal Makan',
                'badge'       => 'badge-red',
                'is_prepared' => false,
                'is_signable' => false,
            ];
        }

        // 1. Outside Meal (Makan di Luar / Bukan Dapur Katering - 0 pax katering)
        if ($plan->outside_meal || $plan->status === 'outside_meal') {
            return [
                'active'      => true,
                'type'        => 'outside_meal',
                'label'       => 'Outside Meal',
                'badge'       => 'badge-gold',
                'is_prepared' => false,
                'is_signable' => false,
            ];
        }

        // 2. Check Target Mess Hall / Kitchen for this meal
        $targetMessHallKey = self::getPlanMessHallKey($plan);

        if ($targetMessHallKey === $currentMessHallKey) {
            $loc = $plan->mealLocation ?? $user->mealLocation;
            $isMess = $loc ? $loc->isMessHall() : true;

            if ($isMess) {
                // Makan di Mess Hall INI -> Paraf box hijau untuk ditandatangani!
                return [
                    'active'      => true,
                    'type'        => 'messhall',
                    'label'       => 'Hadir di Mess Hall',
                    'badge'       => 'badge-green',
                    'is_prepared' => true,
                    'is_signable' => true,
                ];
            } else {
                // Dapur ini yang memasak/menyiapkan, diantar ke drop-point wilayah ini
                $locName = $loc?->name ?? 'Drop Point';
                return [
                    'active'      => true,
                    'type'        => 'delivery',
                    'label'       => 'Diantar: ' . $locName,
                    'badge'       => 'badge-blue',
                    'is_prepared' => true,
                    'is_signable' => false,
                ];
            }
        } else {
            // Dimasak / disiapkan oleh Dapur Mess Hall LAIN
            $shortNames = [
                'ramba-staff'    => 'Mess Hall Staff Ramba',
                'ramba-nonstaff' => 'Mess Hall Non Staff Ramba',
                'bentayan'       => 'Mess Hall Bentayan',
                'mangunjaya'     => 'Mess Hall Mangunjaya',
                'kluang'         => 'Mess Hall Kluang',
            ];
            $targetName = $shortNames[$targetMessHallKey] ?? 'Mess Lain';

            return [
                'active'      => true,
                'type'        => 'other_messhall',
                'label'       => "Req di {$targetName}",
                'badge'       => 'badge-purple',
                'is_prepared' => false,
                'is_signable' => false,
            ];
        }
    }

    /**
     * Generate printable PDF for a Mess Hall manifest & attendance sign sheet.
     */
    public function generateMessHallPdf(Carbon $date, string $key): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getMessHallManifestData($date, $key);

        return Pdf::loadView('pdf.manifest-messhall', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate or regenerate standard batch manifest.
     */
    public function generate(
        Carbon $date,
        int $regionId,
        int $mealTypeId,
        int $locationId,
        User $actor,
        bool $isOverride = false,
        ?string $overrideReason = null
    ): ManifestBatch {
        $mealPlans = MealPlan::finalActive()
            ->whereDate('meal_date', $date)
            ->where('region_id', $regionId)
            ->where('meal_type_id', $mealTypeId)
            ->where('meal_location_id', $locationId)
            ->with(['user.department', 'user.workerStatus', 'user.company'])
            ->orderBy(DB::raw('(SELECT name FROM users WHERE id = meal_plans.user_id)'))
            ->get();

        $latestVersion = ManifestBatch::where('manifest_date', $date)
            ->where('region_id', $regionId)
            ->where('meal_type_id', $mealTypeId)
            ->where('meal_location_id', $locationId)
            ->max('version') ?? 0;

        $version = $latestVersion + 1;
        $manifestNumber = "MNF-{$date->format('Ymd')}-R{$regionId}-MT{$mealTypeId}-L{$locationId}-V{$version}";

        return DB::transaction(function () use (
            $date, $regionId, $mealTypeId, $locationId,
            $actor, $isOverride, $overrideReason,
            $mealPlans, $version, $manifestNumber
        ) {
            $batch = ManifestBatch::create([
                'manifest_number'  => $manifestNumber,
                'manifest_date'    => $date,
                'region_id'        => $regionId,
                'meal_type_id'     => $mealTypeId,
                'meal_location_id' => $locationId,
                'version'          => $version,
                'status'           => 'final',
                'total_pax'        => $mealPlans->count(),
                'generated_at'     => now(),
                'generated_by'     => $actor->id,
                'is_override'      => $isOverride,
                'override_reason'  => $overrideReason,
            ]);

            foreach ($mealPlans as $idx => $plan) {
                ManifestPerson::create([
                    'manifest_batch_id' => $batch->id,
                    'user_id'           => $plan->user_id,
                    'meal_plan_id'      => $plan->id,
                    'sequence'          => $idx + 1,
                ]);
            }

            $this->audit->log('generate_manifest', 'manifest',
                "Manifest {$manifestNumber} dibuat oleh {$actor->name} ({$mealPlans->count()} pax)" . ($isOverride ? ' [OVERRIDE]' : ''),
                $batch, [], $batch->toArray(), $actor->id
            );

            return $batch->load('people.user', 'region', 'mealType', 'mealLocation');
        });
    }

    public function generatePdf(ManifestBatch $batch): \Barryvdh\DomPDF\PDF
    {
        $batch->load('people.user.department', 'people.user.workerStatus', 'region', 'mealType', 'mealLocation', 'generatedBy');

        return Pdf::loadView('pdf.manifest', [
            'batch'           => $batch,
            'catering_vendor' => self::getCateringVendorName(),
            'gs_officer_name' => self::getGsOfficerName(),
            'gs_officer_title'=> self::getGsOfficerTitle(),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Get collected data for all meals delivered to Office / SP / Pos Security / Non-Mess Hall on a date.
     * Filterable by specific Region.
     */
    public function getDeliveryManifestData(Carbon $date, int|string|null $regionId = null, ?array $selectedLocationIds = null): array
    {
        // 1. Fetch active regions. If regionId is specified, filter to that single region
        $regionsQuery = Region::where('is_active', true)->orderBy('id');
        if ($regionId) {
            if (is_numeric($regionId)) {
                $regionsQuery->where('id', $regionId);
            } else {
                $regionsQuery->where('slug', strtolower($regionId))->orWhere('name', $regionId);
            }
        }
        $regions = $regionsQuery->get();
        $targetRegion = $regions->first();

        // 2. Fetch all active meal plans for the date
        $allDayPlans = MealPlan::whereDate('meal_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('cancelled', false)
            ->with(['user.department', 'user.workerStatus', 'user.company', 'user.mealLocation', 'mealType', 'mealLocation.region', 'region'])
            ->get();

        // 3. Filter only delivery orders prepared by catering (delivered to non-Mess Hall locations)
        $allDeliveryPlans = $allDayPlans->filter(function ($plan) use ($targetRegion) {
            if ($plan->outside_meal || $plan->status === 'outside_meal') return false; // Non-catering outside meal
            $loc = $plan->mealLocation ?? $plan->user?->mealLocation;
            if (!$loc) return false;
            if ($loc->isMessHall()) return false;

            if ($targetRegion) {
                $planRegId = $loc->region_id ?? $plan->region_id;
                if ($planRegId != $targetRegion->id) return false;
            }
            return true;
        });

        // 4. Group available delivery sub-locations by Region
        $availableLocationsByRegion = [];
        $allAvailableLocations = [];
        $locGroups = $allDeliveryPlans->groupBy('meal_location_id');

        foreach ($locGroups as $locId => $plans) {
            $sample = $plans->first();
            $loc = $sample->mealLocation ?? $sample->user?->mealLocation;
            $regId = $loc?->region_id ?? $sample->region_id ?? 1;
            $regName = $sample->region?->name ?? $loc?->region?->name ?? 'Ramba';
            $locName = $loc?->name ?? 'Drop Point';

            $item = [
                'id'          => (int)$locId,
                'name'        => $locName,
                'region_id'   => $regId,
                'region_name' => $regName,
                'count'       => $plans->count(),
            ];

            $availableLocationsByRegion[$regName][] = $item;
            $allAvailableLocations[] = $item;
        }

        // 5. If selectedLocationIds is specified and non-empty, filter by those IDs
        if ($selectedLocationIds !== null && !empty($selectedLocationIds)) {
            $deliveryPlans = $allDeliveryPlans->filter(function ($plan) use ($selectedLocationIds) {
                return in_array($plan->meal_location_id, $selectedLocationIds);
            });
        } else {
            $deliveryPlans = $allDeliveryPlans;
        }

        // 6. Partition rows by Region
        $regionsData = [];
        $grandTotals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0];
        $allRows = [];
        $rowCounter = 1;

        $plansByRegion = $deliveryPlans->groupBy(function ($plan) {
            return $plan->region?->name ?? $plan->mealLocation?->region?->name ?? 'Ramba';
        });

        foreach ($regions as $reg) {
            $regName = $reg->name;
            $regPlans = $plansByRegion->get($regName, collect());

            $plansGroupedByUser = $regPlans->groupBy('user_id');
            $users = User::whereIn('id', $plansGroupedByUser->keys())
                ->with(['department', 'workerStatus', 'company', 'role'])
                ->orderBy('name', 'asc')
                ->get();

            $regRows = [];
            $regTotals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0];
            $locationBreakdown = [];

            foreach ($users as $u) {
                $userPlans = $plansGroupedByUser->get($u->id) ?? collect();

                $breakfastPlan = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'breakfast');
                $lunchPlan     = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'lunch');
                $dinnerPlan    = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'dinner');
                $supperPlan    = $userPlans->first(fn($p) => strtolower($p->mealType?->slug ?? '') === 'supper');

                $destinations = $userPlans->map(fn($p) => $p->mealLocation?->name)->filter()->unique()->values()->all();
                $destStr = !empty($destinations) ? implode(', ', $destinations) : 'Diantar ke Lokasi Kerja';

                $bfActive = $breakfastPlan !== null;
                $luActive = $lunchPlan !== null;
                $diActive = $dinnerPlan !== null;
                $suActive = $supperPlan !== null;

                if ($bfActive) { $regTotals['breakfast']++; $grandTotals['breakfast']++; }
                if ($luActive) { $regTotals['lunch']++; $grandTotals['lunch']++; }
                if ($diActive) { $regTotals['dinner']++; $grandTotals['dinner']++; }
                if ($suActive) { $regTotals['supper']++; $grandTotals['supper']++; }

                $userTotalPax = ($bfActive ? 1 : 0) + ($luActive ? 1 : 0) + ($diActive ? 1 : 0) + ($suActive ? 1 : 0);

                foreach ($userPlans as $p) {
                    $pLocName = $p->mealLocation?->name ?? $destStr;
                    $locationBreakdown[$pLocName] = ($locationBreakdown[$pLocName] ?? 0) + 1;
                }

                $rowData = [
                    'number'        => $rowCounter++,
                    'user'          => $u,
                    'name'          => $u->name,
                    'nomor_pegawai' => $u->nomor_pegawai ?? '-',
                    'jabatan'       => $u->jabatan ?? '',
                    'worker_status' => $u->workerStatus?->name ?? ($u->role?->name ?? 'Pekerja'),
                    'department'    => $u->department?->name ?? ($u->company?->name ?? 'Pertamina EP'),
                    'region'        => $regName,
                    'destination'   => $destStr,
                    'breakfast'     => $bfActive ? '1 pax' : '-',
                    'lunch'         => $luActive ? '1 pax' : '-',
                    'dinner'        => $diActive ? '1 pax' : '-',
                    'supper'        => $suActive ? '1 pax' : '-',
                    'breakfast_pax' => $bfActive ? 1 : 0,
                    'lunch_pax'     => $luActive ? 1 : 0,
                    'dinner_pax'    => $diActive ? 1 : 0,
                    'supper_pax'    => $suActive ? 1 : 0,
                    'total_pax'     => $userTotalPax,
                ];

                $regRows[] = $rowData;
                $allRows[] = $rowData;
            }

            $regionsData[$regName] = [
                'region'              => $reg,
                'rows'                => $regRows,
                'totals'              => $regTotals,
                'grand_total'         => array_sum($regTotals),
                'location_breakdown'  => $locationBreakdown,
                'available_locations' => $availableLocationsByRegion[$regName] ?? [],
            ];
        }

        // Collect overall location breakdown for summary card
        $overallLocationBreakdown = [];
        foreach ($regionsData as $rData) {
            foreach ($rData['location_breakdown'] as $locName => $paxCount) {
                $overallLocationBreakdown[$locName] = ($overallLocationBreakdown[$locName] ?? 0) + $paxCount;
            }
        }

        return [
            'date'                          => $date,
            'target_region'                 => $targetRegion,
            'target_region_name'            => $targetRegion?->name,
            'regions_data'                  => $regionsData,
            'available_locations_by_region' => $availableLocationsByRegion,
            'available_locations'           => $allAvailableLocations,
            'location_breakdown'            => $overallLocationBreakdown,
            'rows'                          => $allRows,
            'totals'                        => $grandTotals,
            'grand_total'                   => array_sum($grandTotals),
            'catering_vendor'               => self::getCateringVendorName(),
            'gs_officer_name'               => self::getGsOfficerName(),
            'gs_officer_title'              => self::getGsOfficerTitle(),
        ];
    }

    /**
     * Generate 1-page portrait PDF for Delivery & Outside Meal Manifest for a specific single Region.
     */
    public function generateDeliveryPdf(Carbon $date, int|string|null $regionId = null, ?array $selectedLocationIds = null): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getDeliveryManifestData($date, $regionId, $selectedLocationIds);

        return Pdf::loadView('pdf.manifest-delivery', $data)
            ->setPaper('a4', 'portrait');
    }
}
