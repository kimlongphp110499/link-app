<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClanPointHistory;
use App\Models\ClanTempMember;
use Illuminate\Support\Facades\DB;

class VideoPlaybackController extends Controller
{
    public function store($linkId)
    {
        try {
            DB::beginTransaction();
            $memberClan = ClanTempMember::select('user_id', 'clan_id')
                    ->where('link_id', $linkId)
                    ->get();
            if ($memberClan->isEmpty()) {
                return response()->json([
                    'message' => 'No members found for the given link_id',
                ], 404);
            }

            $dataToInsert = [];
            foreach ($memberClan as $member) {
                $dataToInsert[] = [
                    'user_id' => $member->user_id,
                    'clan_id' => $member->clan_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ClanPointHistory::insert($dataToInsert);
            ClanTempMember::where('link_id', $linkId)
                     ->delete();
            DB::commit();

            return response()->json([
                'message' => 'successfully',
                'delete_temporary_data' => true,
                'add_points_to_clan' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
