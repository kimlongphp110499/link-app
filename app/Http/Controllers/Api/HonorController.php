<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Honor;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class HonorController extends Controller
{
    public function index()
    {
        try {
            $today = Carbon::today();
            $honors = Honor::select('url_name', 'url', 'date')
                ->whereDate('date', $today)
                ->get();
            foreach ($honors as $honor) {
                $honor->date = Carbon::parse($honor->date);
            }

            return response()->json([
                'status' => 'success',
                'data' => $honors
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching honors: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection error.',
            ], 500);
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
