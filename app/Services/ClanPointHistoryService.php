<?php

namespace App\Services;

use App\Models\ClanPointHistory;
use Illuminate\Support\Facades\Log;
class ClanPointHistoryService
{
    /**
     * check exit user for clan
     */
    public function existingHistory($userId, $clanId)
    {
        try {
            return ClanPointHistory::where('user_id', $userId)
                ->where('clan_id', $clanId)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Unexpected error while fetching honors: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }
}
