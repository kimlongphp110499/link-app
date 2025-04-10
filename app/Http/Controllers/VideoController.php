<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Schedule;
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

        $startTime = Carbon::parse($currentSchedule->start_time);
        $elapsedSeconds = $now->diffInSeconds($startTime);

        return response()->json([
            'link' => [
                'url' => $currentSchedule->link->url,
                'title' => $currentSchedule->link->title,
                'duration' => $currentSchedule->link->duration,
            ],
            'offset' => $elapsedSeconds,
            'timestamp' => $now->toIso8601String(),
        ]);
    }
}