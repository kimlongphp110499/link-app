<?php

namespace App\Http\Controllers;

use App\Models\ClanPointHistory;
use App\Models\ClanTempMember;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{

    public function getCurrentVideo()
    {
        $now = Carbon::now();
        Log::info("Current time: " . $now->toIso8601String());
        $currentSchedule = Schedule::first();
        if (!$currentSchedule) {
            return response()->json(['message' => 'No video scheduled'], 404);
        }

        $link = $currentSchedule->link;
        $userWithMaxVotes = $link->voteHistories()
            ->selectRaw('user_id, SUM(points_voted) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->first();

        $link->user_with_max_votes = $userWithMaxVotes
            ? User::find($userWithMaxVotes->user_id)
            : null;
        $link->user_max_vote_points = $userWithMaxVotes
            ? (int) $userWithMaxVotes->total_points
            : 0;
        $startTime = Carbon::parse($currentSchedule->start_time);
        $elapsedMilliseconds = (int)$now->diffInMilliseconds($startTime);
        
        $durationMilliseconds = $link->duration ? $link->duration * 1000 : 0;
        $wait = $elapsedMilliseconds + $durationMilliseconds;
         // Kiểm tra nếu start_time lớn hơn thời gian hiện tại
         if ($elapsedMilliseconds > 0) {
             return response()->json(['message' => 'Please wait'], 202); // Trả về thông báo "Hãy chờ"
         }

        try {
            DB::beginTransaction();
            $memberClan = ClanTempMember::select('user_id', 'clan_id')
                    ->where('link_id', $link->id)
                    ->get();
            if (!$memberClan->isEmpty()) {
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
                ClanTempMember::where('link_id', $link->id)
                         ->delete();
            }
            DB::commit();

            return response()->json([
                'link' =>$link,
                'offset' => $elapsedMilliseconds,
                'start_time' => $startTime,
                'duration' => $durationMilliseconds,
                'timestamp' => $now->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
