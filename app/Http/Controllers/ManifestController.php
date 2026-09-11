<?php

namespace App\Http\Controllers;

use App\Services\{ManifestService, AuditService, PlanningCutoffService};
use App\Models\{ManifestBatch, Region, MealType, MealLocation};
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function __construct(
        private ManifestService $manifestService,
        private AuditService $audit,
        private PlanningCutoffService $cutoffService
    ) {}

    public function index(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        // 1. Get Summary for the 5 Main Mess Halls
        $messHallsSummary = $this->manifestService->getAllMessHallsSummary($date);
        $deliverySummary  = $this->manifestService->getDeliveryManifestData($date);

        // 2. Query individual generated batches if any
        $regionId = $request->integer('region_id');
        $locId    = $request->integer('meal_location_id');
        $typeId   = $request->integer('meal_type_id');

        $batches = ManifestBatch::with(['region', 'mealType', 'mealLocation', 'generatedBy'])
            ->whereDate('manifest_date', $date)
            ->when($regionId, fn($q) => $q->where('region_id', $regionId))
            ->when($locId, fn($q) => $q->where('meal_location_id', $locId))
            ->when($typeId, fn($q) => $q->where('meal_type_id', $typeId))
            ->orderBy('version', 'desc')
            ->paginate(15)
            ->withQueryString();

        $regions   = Region::where('is_active', true)->orderBy('name')->get();
        $mealTypes = MealType::active()->get();
        $locations = MealLocation::active()->get();

        return view('manifest.index', compact('messHallsSummary', 'deliverySummary', 'batches', 'regions', 'mealTypes', 'locations', 'date'));
    }

    /**
     * Show detailed manifest table for one of the 5 Mess Halls sorted A-Z.
     */
    public function showMessHall(Request $request, string $date, string $key)
    {
        $parsedDate = Carbon::parse($date);
        $data = $this->manifestService->getMessHallManifestData($parsedDate, $key);

        return view('manifest.messhall', $data);
    }

    /**
     * Download or stream PDF for a Mess Hall manifest.
     */
    public function pdfMessHall(Request $request, string $date, string $key)
    {
        $parsedDate = Carbon::parse($date);
        $pdf = $this->manifestService->generateMessHallPdf($parsedDate, $key);

        $filename = "manifest-{$key}-{$parsedDate->format('Ymd')}.pdf";
        return $pdf->stream($filename);
    }

    /**
     * Show detailed manifest table for delivered meals (Office / SP / Pos Security / Outside Meals).
     */
    public function showDelivery(Request $request, string $date)
    {
        $parsedDate = Carbon::parse($date);
        $data = $this->manifestService->getDeliveryManifestData($parsedDate);

        return view('manifest.delivery', $data);
    }

    /**
     * Download or stream PDF for Delivery Manifest (Single Region per PDF).
     */
    public function pdfDelivery(Request $request, string $date)
    {
        $parsedDate = Carbon::parse($date);
        
        $regionId = $request->input('region_id') ?? $request->input('region');

        $locationIds = $request->input('location_ids');
        if (is_string($locationIds)) {
            $locationIds = array_filter(array_map('intval', explode(',', $locationIds)));
        } elseif (is_array($locationIds)) {
            $locationIds = array_filter(array_map('intval', $locationIds));
        } else {
            $locationIds = null;
        }

        $pdf = $this->manifestService->generateDeliveryPdf($parsedDate, $regionId, $locationIds);

        $regSuffix = $regionId ? "-wilayah-{$regionId}" : "";
        $filename = "manifest-pengantaran-{$parsedDate->format('Ymd')}{$regSuffix}.pdf";
        return $pdf->stream($filename);
    }

    public function generate(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isGS() || $user->isCatering() || $user->isSysAdmin(), 403);

        $validated = $request->validate([
            'manifest_date'    => 'required|date',
            'region_id'        => 'required|exists:regions,id',
            'meal_type_id'     => 'required|exists:meal_types,id',
            'meal_location_id' => 'nullable|exists:meal_locations,id',
            'is_override'      => 'nullable',
            'override_reason'  => 'nullable|string|max:1000',
        ]);

        $date = Carbon::parse($validated['manifest_date']);
        $isOverride = $request->boolean('is_override');

        if ($this->cutoffService->isClosed($date) && !$isOverride && !$user->isGS()) {
            return back()->with('warning', 'Batas waktu cut-off telah lewat. Hubungi GS untuk override manifest.');
        }

        $locationId = $validated['meal_location_id'] ?? null;
        if (!$locationId) {
            $location = MealLocation::where('region_id', $validated['region_id'])->where('is_active', true)->first()
                     ?? MealLocation::first();
            $locationId = $location?->id;
        }

        if (!$locationId) {
            return back()->with('error', 'Lokasi makan untuk wilayah yang dipilih belum tersedia di sistem master data.');
        }

        $batch = $this->manifestService->generate(
            $date,
            (int)$validated['region_id'],
            (int)$validated['meal_type_id'],
            (int)$locationId,
            $user,
            $isOverride,
            $validated['override_reason'] ?? null
        );

        $regionName = $batch->region?->name ?? 'Wilayah';
        $mealName   = $batch->mealType?->name ?? 'Makan';
        return redirect()->route('manifest.show', $batch)
            ->with('success', "Manifest {$batch->manifest_number} berhasil di-generate untuk {$regionName} ({$mealName}) dengan total {$batch->total_pax} porsi (pax).");
    }

    public function show(ManifestBatch $manifest)
    {
        $manifest->load(['people.user.department', 'people.user.workerStatus', 'people.user.company', 'region', 'mealType', 'mealLocation', 'generatedBy']);
        return view('manifest.show', compact('manifest'));
    }

    public function pdf(ManifestBatch $manifest)
    {
        return $this->manifestService->generatePdf($manifest)
            ->stream("manifest-{$manifest->manifest_number}.pdf");
    }

    public function updateCateringVendor(Request $request)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isCatering() || auth()->user()->isSysAdmin(), 403);

        $request->validate([
            'catering_vendor_name' => 'nullable|string|max:200',
            'gs_officer_name'      => 'nullable|string|max:200',
            'gs_officer_title'     => 'nullable|string|max:200',
        ]);

        if ($request->filled('catering_vendor_name')) {
            \App\Models\AppSetting::set('catering_vendor_name', trim($request->catering_vendor_name), 'Nama Perusahaan Mitra Penyedia Katering Aktif');
        }
        if ($request->filled('gs_officer_name')) {
            \App\Models\AppSetting::set('gs_officer_name', trim($request->gs_officer_name), 'Nama Penanggung Jawab General Services Ramba');
        }
        if ($request->filled('gs_officer_title')) {
            \App\Models\AppSetting::set('gs_officer_title', trim($request->gs_officer_title), 'Jabatan Penanggung Jawab General Services Ramba');
        }

        return back()->with('success', 'Pengaturan penandatangan & penyedia katering untuk cetak PDF berhasil disimpan.');
    }
}
