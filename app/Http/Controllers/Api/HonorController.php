<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Honor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HonorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $params = $request->all();
            $now = Carbon::now();
            $honors = Honor::select('id', 'url_name', 'url', 'date', 'duration')
                ->where(function ($query) use ($now) {
                    $query->where('date', '>=', $now)
                          ->orWhere(function ($q) use ($now) {
                              $q->where('date', '<=', $now)
                                ->whereRaw('DATE_ADD(date, INTERVAL duration SECOND) >= ?', [$now]);
                  });
                })
                ->orderBy('date', 'asc')
                ->get();
            if ($honors->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No data',
                ]);
            }

            foreach ($honors as $honor) {
                $honor->date = Carbon::parse($honor->date);
                $honor->duration = $honor->duration * 1000;
            }
            if (!array_key_exists('offset', $params) || $params['offset'] === 'false') {
                return response()->json([
                    'status' => 'success',
                    'data' => $honors,
                ]);
            }

            $currentHonor = $honors->firstWhere('date', '<=', $now) ?? $honors->first();
            $startTime = Carbon::parse($currentHonor->date);
            $elapsedMilliseconds = $startTime->greaterThan($now) ? 0 : (int)
            $startTime->diffInMilliseconds($now);
            $durationMilliseconds = $currentHonor->duration;
            $offset = $elapsedMilliseconds;

            // Kiểm tra trạng thái video
            if ($startTime->greaterThan($now)) {
                // Video chưa bắt đầu
                Log::info("Video honor ID {$currentHonor->id} has not started yet");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Video has not started yet',
                    'data' => $honors,
                    'item' => $currentHonor,
                    'offset' => 0,
                    'start_time' => $startTime,
                    'duration' => $durationMilliseconds,
                    'timestamp' => $now->toIso8601String()
                ], 202);
            }
            if ($offset > $durationMilliseconds) {
            // Video đã kết thúc
                Log::info("Video honor ID {$currentHonor->id} has ended");
                return response()->json([
                    'status' => 'success',
                    'message' => 'Video has ended',
                    'data' => $honors,
                    'item' => $currentHonor,
                    'offset' => $durationMilliseconds,
                    'start_time' => $startTime,
                    'duration' => $durationMilliseconds,
                    'timestamp' => $now->toIso8601String()
                ], 200);
            }

            // Video đang chạy
            Log::info("Video honor ID {$currentHonor->id} is running at {$offset} ms");
                return response()->json([
                'status' => 'success',
                'data' => $honors,
                'item' => $currentHonor,
                'offset' => $offset,
                'start_time' => $startTime,
                'duration' => $durationMilliseconds,
                'timestamp' => $now->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error while fetching honors: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }
}
