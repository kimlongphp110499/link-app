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
                ->where('date', '>=', $today)
                ->orderBy('date', 'asc')
                ->get();
            foreach ($honors as $honor) {
                $honor->date = $honor->date->format('Y-m-d\TH:i:s.u\Z');
            }
            $cacheKey = "video_progress_{$honors[0]->id}";
            $currentSecond = Cache::get($cacheKey);
            if ($params['offset'] == 'false') {
                return response()->json([
                    'status' => 'success',
                    'data' => $honors,
                ]);
            }

            if (is_null($currentSecond)) {
                Cache::put($cacheKey, 0, now()->addMinutes(10));
                UpdateOffSetVideoProgress::dispatch($honors[0]->id, $honors[0]->duration);

                return response()->json([
                    'status' => 'success',
                    'data' => $honors,
                    'offset' => 0,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $honors,
                'item' => $honors[0],
                'offset' => $currentSecond,
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
