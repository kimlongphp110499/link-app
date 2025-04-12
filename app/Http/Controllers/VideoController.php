<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class VideoController extends Controller
{
    public function getCurrentVideo()
    {
        $now = Carbon::now();
        \Log::info("Current time: " . $now->toIso8601String());

        // Lấy schedule hiện tại
        $currentSchedule = Schedule::where('start_time', '<=', $now)
            ->orderBy('start_time', 'desc')
            ->first();
        
        if (!$currentSchedule) {
            return response()->json(['message' => 'No video scheduled'], 404);
        }

        $link = $currentSchedule->link;
        $userWithMaxVotes = $link->voteHistories()
            ->selectRaw('user_id, SUM(points_voted) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points') // Lấy điểm cao nhất
            ->first();

        // Gán thông tin user và điểm vote
        $link->user_with_max_votes = $userWithMaxVotes 
            ? User::find($userWithMaxVotes->user_id) 
            : null;
        $link->user_max_vote_points = $userWithMaxVotes 
            ? $userWithMaxVotes->total_points 
            : 0;

        $startTime = Carbon::parse($currentSchedule->start_time);
        $elapsedMilliseconds = (int)$now->diffInMilliseconds($startTime);

        // Giả sử duration là một trường trong model Link, chuyển sang mili giây
        $durationMilliseconds = $link->duration ? $link->duration * 1000 : 0;
        return response()->json([
            'link' =>$link,
            'offset' => $elapsedMilliseconds,
            'duration' => $durationMilliseconds,
            'timestamp' => $now->toIso8601String(),
        ]);
    }
}