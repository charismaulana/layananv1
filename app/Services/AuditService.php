<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $activityType,
        string $module,
        string $description,
        ?Model $loggable = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null
    ): UserActivity {
        return UserActivity::create([
            'user_id'       => $userId ?? Auth::id(),
            'activity_type' => $activityType,
            'module'        => $module,
            'description'   => $description,
            'old_values'    => $oldValues ?: null,
            'new_values'    => $newValues ?: null,
            'ip_address'    => Request::ip(),
            'user_agent'    => Request::userAgent(),
            'loggable_type' => $loggable ? get_class($loggable) : null,
            'loggable_id'   => $loggable?->getKey(),
        ]);
    }
}
