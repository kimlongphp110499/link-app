<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateOffSetVideoProgress;
use App\Models\Honor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HonorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $params = $request->all();
//            $today = "2025-04-22 06:40:52";
            $today = Carbon::now()->format('Y-m-d H:i:s');
            $honors = Honor::select('id', 'url_name', 'url', 'date', 'duration')
                ->where(function ($query) use ($today) {
                    $query->where('date', '>=', $today)
                          ->orWhere(function ($q) use ($today) {
                              $q->where('date', '<=', $today)
                                ->whereRaw('DATE_ADD(date, INTERVAL duration SECOND) >= ?', [$today]);
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
                $honor->date = $honor->date->format('Y-m-d\TH:i:s.u');
                $honor->duration = $honor->duration * 1000;
            }
            if (!array_key_exists('offset', $params) || $params['offset'] === 'false') {
                return response()->json([
                    'status' => 'success',
                    'data' => $honors,
                ]);
            }

            $currentHonor = $honors->firstWhere('date', '<=', $today) ?? $honors->first();
            $cacheKey = "video_progress_{$currentHonor->id}";
            $currentSecond = Cache::get($cacheKey);
//            dd($currentSecond);
            if (is_null($currentSecond)) {
                Log::info("Starting video honor ID {$currentHonor->id} playback");
                $ttl = $currentHonor->duration + 300000; // TTL = duration + 5 phút
                Cache::put($cacheKey, 0, $ttl / 1000);
                UpdateOffSetVideoProgress::dispatch($currentHonor->id, $currentHonor->duration);

                return response()->json([
                    'status' => 'success',
                    'data' => $honors,
                    'offset' => 0,
                ]);
            }

            Log::info("Retrieved currentSecond for honor ID {$currentHonor->id}: {$currentSecond}");
            return response()->json([
                'status' => 'success',
                'data' => $honors,
                'item' => $currentHonor,
                'offset' => $currentSecond
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
