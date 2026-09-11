<?php

namespace App\Services;

use App\Models\{MealCard, User};
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MealCardService
{
    public function __construct(private AuditService $audit) {}

    /**
     * Get or create a meal card for a user.
     */
    public function getOrCreate(User $user): MealCard
    {
        return MealCard::firstOrCreate(
            ['user_id' => $user->id],
            ['token' => MealCard::generateToken(), 'is_active' => true]
        );
    }

    /**
     * Generate QR code SVG for display.
     * Token is opaque — does NOT contain PII.
     */
    public function generateQrSvg(MealCard $card): string
    {
        // QR encodes only the token (not name/NIP/email)
        return QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($card->token);
    }

    /**
     * Regenerate token for a user (e.g., security reasons).
     */
    public function regenerateToken(User $user, User $actor): MealCard
    {
        $card = $this->getOrCreate($user);
        $oldToken = $card->token;
        $card->update(['token' => MealCard::generateToken()]);

        $this->audit->log('regenerate_meal_card', 'meal_card',
            "Token Meal Card di-regenerate untuk {$user->name}",
            $card, ['token' => '***'], ['token' => '***'], $actor->id
        );

        return $card->fresh();
    }
}
