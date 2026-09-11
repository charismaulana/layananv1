<?php

namespace App\Services;

use App\Models\{OutsideMeal, OutsideMealPerson, User};
use App\Services\{MealPlanService, AuditService, PlanningCutoffService};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OutsideMealService
{
    public function __construct(
        private MealPlanService $mealPlanService,
        private AuditService $audit,
        private PlanningCutoffService $cutoff
    ) {}

    public function create(array $data, array $userIds, User $requester): OutsideMeal
    {
        $mealDate = Carbon::parse($data['meal_date']);

        if ($this->cutoff->isClosed($mealDate)) {
            throw new \Exception("Cutoff sudah lewat untuk tanggal {$mealDate->format('d/m/Y')}.");
        }

        return DB::transaction(function () use ($data, $userIds, $requester) {
            $outsideMeal = OutsideMeal::create(array_merge($data, ['requester_id' => $requester->id]));

            foreach ($userIds as $userId) {
                OutsideMealPerson::create(['outside_meal_id' => $outsideMeal->id, 'user_id' => $userId]);
            }

            $this->audit->log('create_outside_meal', 'outside_meal',
                "Permohonan outside meal tanggal {$outsideMeal->meal_date}",
                $outsideMeal, [], $outsideMeal->toArray(), $requester->id
            );

            return $outsideMeal->load('people.user', 'mealType');
        });
    }

    public function approve(OutsideMeal $outsideMeal, User $approver): void
    {
        $outsideMeal->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->mealPlanService->applyOutsideMeal($outsideMeal);

        $this->audit->log('approve_outside_meal', 'outside_meal',
            "Outside meal disetujui", $outsideMeal,
            ['status' => 'pending'], ['status' => 'approved'], $approver->id
        );
    }

    public function reject(OutsideMeal $outsideMeal, User $approver, string $reason): void
    {
        $outsideMeal->update([
            'status'           => 'rejected',
            'approved_by'      => $approver->id,
            'rejection_reason' => $reason,
        ]);

        $this->audit->log('reject_outside_meal', 'outside_meal',
            "Outside meal ditolak: $reason", $outsideMeal,
            ['status' => 'pending'], ['status' => 'rejected'], $approver->id
        );
    }
}
