<?php

namespace App\Services;

use App\Models\CutoffSetting;
use Carbon\Carbon;

class PlanningCutoffService
{
    private CutoffSetting $setting;

    public function __construct()
    {
        $this->setting = CutoffSetting::where('is_active', true)->first()
            ?? new CutoffSetting(['cutoff_days_before' => 1, 'cutoff_time' => '19:00:00', 'timezone' => 'Asia/Jakarta']);
    }

    /**
     * Check if changes are still allowed for a given meal date.
     * Cutoff is H-1 at 19:00 WIB.
     */
    public function isOpen(Carbon $mealDate): bool
    {
        $cutoff = $this->getCutoffDateTime($mealDate);
        return Carbon::now($this->setting->timezone)->lt($cutoff);
    }

    public function isClosed(Carbon $mealDate): bool
    {
        return !$this->isOpen($mealDate);
    }

    /**
     * Get the exact cutoff datetime for a meal date.
     */
    public function getCutoffDateTime(Carbon $mealDate): Carbon
    {
        $tz = $this->setting->timezone;
        $cutoffDate = $mealDate->copy()->subDays($this->setting->cutoff_days_before);
        [$hour, $minute] = explode(':', $this->setting->cutoff_time);

        return Carbon::create(
            $cutoffDate->year, $cutoffDate->month, $cutoffDate->day,
            (int)$hour, (int)$minute, 0,
            $tz
        );
    }

    /**
     * Returns human-readable cutoff info.
     */
    public function getCutoffDescription(Carbon $mealDate): string
    {
        $dt = $this->getCutoffDateTime($mealDate);
        return $dt->translatedFormat('d M Y H:i') . ' WIB';
    }

    public function getSetting(): CutoffSetting
    {
        return $this->setting;
    }
}
