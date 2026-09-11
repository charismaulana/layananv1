<?php

namespace App\Services;

use App\Models\{Movement, MovementPerson, User};
use App\Services\{MealPlanService, AuditService, PlanningCutoffService};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MovementService
{
    public function __construct(
        private MealPlanService $mealPlanService,
        private AuditService $audit,
        private PlanningCutoffService $cutoff
    ) {}

    public function create(array $data, array $userIds, array $mealTypeIds, User $requester): Movement
    {
        $mealDate = Carbon::parse($data['movement_date']);

        if ($this->cutoff->isClosed($mealDate)) {
            throw new \Exception("Cutoff sudah lewat untuk tanggal {$mealDate->format('d/m/Y')}. Hubungi GS untuk emergency override.");
        }

        return DB::transaction(function () use ($data, $userIds, $mealTypeIds, $requester) {
            $movement = Movement::create(array_merge($data, ['requester_id' => $requester->id]));

            foreach ($userIds as $userId) {
                MovementPerson::create([
                    'movement_id'  => $movement->id,
                    'user_id'      => $userId,
                    'meal_type_ids'=> $mealTypeIds ?: null,
                ]);
            }

            $this->audit->log('create_movement', 'movement',
                "Permohonan movement {$movement->movement_date} dari {$movement->fromRegion->name} ke {$movement->toRegion->name}",
                $movement, [], $movement->toArray(), $requester->id
            );

            return $movement->load('people.user', 'fromRegion', 'toRegion');
        });
    }

    public function approve(Movement $movement, User $approver, bool $isGsOverride = false): void
    {
        $movement->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->mealPlanService->applyMovement($movement);

        $this->audit->log('approve_movement', 'movement',
            "Movement disetujui oleh {$approver->name}",
            $movement, ['status' => 'pending'], ['status' => 'approved'], $approver->id
        );
    }

    public function reject(Movement $movement, User $approver, string $reason): void
    {
        $movement->update([
            'status'           => 'rejected',
            'approved_by'      => $approver->id,
            'rejection_reason' => $reason,
        ]);

        $this->audit->log('reject_movement', 'movement',
            "Movement ditolak: $reason",
            $movement, ['status' => 'pending'], ['status' => 'rejected'], $approver->id
        );
    }
}
