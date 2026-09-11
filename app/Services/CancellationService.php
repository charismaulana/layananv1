<?php

namespace App\Services;

use App\Models\{MealPlan, MealCancellation, User};
use App\Services\{AuditService, PlanningCutoffService};
use Carbon\Carbon;

class CancellationService
{
    public function __construct(
        private AuditService $audit,
        private PlanningCutoffService $cutoff
    ) {}

    public function cancel(MealPlan $mealPlan, ?string $reason, User $actor): MealCancellation
    {
        $mealDate = Carbon::parse($mealPlan->meal_date);
        $isClosed = $this->cutoff->isClosed($mealDate);

        if ($isClosed && !$actor->isGS() && !$actor->isSysAdmin()) {
            throw new \Exception("Batas waktu pembatalan mandiri (Cutoff H-1 pukul 19:00 WIB) telah berakhir untuk tanggal {$mealDate->translatedFormat('d M Y')}. Silakan hubungi GS untuk pembatalan darurat.");
        }

        if ($mealPlan->cancelled || $mealPlan->status === 'cancelled') {
            throw new \Exception("Meal plan ini sudah dibatalkan sebelumnya.");
        }

        $oldValues = $mealPlan->toArray();
        $finalReason = !empty(trim($reason ?? '')) ? $reason : 'Dibatalkan oleh pengguna';

        $mealPlan->update([
            'status'     => 'cancelled',
            'cancelled'  => true,
            'updated_by' => $actor->id,
        ]);

        $cancellation = MealCancellation::create([
            'user_id'      => $mealPlan->user_id,
            'meal_plan_id' => $mealPlan->id,
            'meal_date'    => $mealPlan->meal_date,
            'meal_type_id' => $mealPlan->meal_type_id,
            'reason'       => $finalReason,
        ]);

        $this->audit->log('cancel_meal', 'cancellation',
            "Makan dibatalkan: {$mealPlan->mealType->name} tanggal {$mealDate->format('d/m/Y')} " . ($isClosed ? "[GS OVERRIDE] " : "") . "- Alasan: $finalReason",
            $mealPlan, $oldValues, $mealPlan->fresh()->toArray(), $actor->id
        );

        return $cancellation;
    }

    /**
     * Reactivate a cancelled meal plan if cutoff has not passed (or actor is GS/SysAdmin).
     */
    public function reactivate(MealPlan $mealPlan, User $actor): void
    {
        $mealDate = Carbon::parse($mealPlan->meal_date);
        $isClosed = $this->cutoff->isClosed($mealDate);

        if ($isClosed && !$actor->isGS() && !$actor->isSysAdmin()) {
            throw new \Exception("Batas waktu perubahan status makan (Cutoff H-1 pukul 19:00 WIB) telah berakhir untuk tanggal {$mealDate->translatedFormat('d M Y')}. Silakan hubungi GS untuk pengaktifan darurat.");
        }

        if (!$mealPlan->cancelled && $mealPlan->status === 'active') {
            throw new \Exception("Jatah makan ini sudah berstatus aktif.");
        }

        $oldValues = $mealPlan->toArray();

        $mealPlan->update([
            'status'     => 'active',
            'cancelled'  => false,
            'updated_by' => $actor->id,
        ]);

        // Hapus catatan pembatalan jika ada
        MealCancellation::where('meal_plan_id', $mealPlan->id)->delete();

        $this->audit->log('reactivate_meal', 'meal_plan',
            "Makan diaktifkan kembali: {$mealPlan->mealType?->name} tanggal {$mealDate->format('d/m/Y')} " . ($isClosed ? "[GS OVERRIDE]" : ""),
            $mealPlan, $oldValues, $mealPlan->fresh()->toArray(), $actor->id
        );
    }
}
