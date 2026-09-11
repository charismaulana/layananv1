<?php

namespace App\Services;

use App\Models\{Roster, User, Region};
use App\Services\{MealPlanService, AuditService};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterService
{
    public function __construct(
        private MealPlanService $mealPlanService,
        private AuditService $audit
    ) {}

    /**
     * Upsert a single roster entry and regenerate meal plans.
     */
    public function upsert(array $data, User $actor): Roster
    {
        $existing = Roster::withTrashed()
            ->where('user_id', $data['user_id'])
            ->whereDate('roster_date', $data['roster_date'])
            ->first();

        $oldValues = $existing?->toArray() ?? [];

        $roster = DB::transaction(function () use ($data, $actor, $existing) {
            if ($existing && $existing->trashed()) {
                $existing->restore();
                $existing->update(array_merge($data, ['updated_by' => $actor->id]));
                return $existing->fresh();
            }

            return Roster::updateOrCreate(
                ['user_id' => $data['user_id'], 'roster_date' => $data['roster_date']],
                array_merge($data, ['created_by' => $actor->id, 'updated_by' => $actor->id])
            );
        });

        // Regenerate meal plans
        $roster->load('user');
        $this->mealPlanService->generateFromRoster($roster, $actor);

        $this->audit->log(
            $existing ? 'update_roster' : 'create_roster',
            'roster',
            "Roster {$roster->roster_date->format('d/m/Y')} untuk {$roster->user->name} → {$roster->status}",
            $roster, $oldValues, $roster->fresh()->toArray(), $actor->id
        );

        return $roster;
    }

    /**
     * Bulk copy roster pattern from source dates to target dates.
     */
    public function copyRoster(User $user, array $sourceDates, array $targetDates, User $actor): int
    {
        $count = 0;

        $sourceRosters = Roster::where('user_id', $user->id)
            ->whereIn('roster_date', $sourceDates)
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->roster_date)->format('N')); // day of week

        foreach ($targetDates as $targetDate) {
            $dayOfWeek = Carbon::parse($targetDate)->format('N');
            $source = $sourceRosters->get($dayOfWeek);

            if ($source) {
                $this->upsert([
                    'user_id'     => $user->id,
                    'roster_date' => $targetDate,
                    'status'      => $source->status,
                    'region_id'   => $source->region_id,
                    'notes'       => $source->notes,
                ], $actor);
                $count++;
            }
        }

        return $count;
    }
}
