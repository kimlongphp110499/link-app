<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClanPointHistory;
use Carbon\Carbon;
use App\Models\Clan;
class ClanController extends Controller
{
    public function getClansWithTopVoter()
    {
        // Lấy tất cả clans
        $clans = Clan::orderByDesc('points') // Sắp xếp các clans theo tổng điểm
                    ->take(10)
                    ->get();

        // Mảng kết quả
        $result = [];

        foreach ($clans as $clan) {
            // Lấy tổng điểm của clan
            $totalPoints = $clan->points;

            // Lấy người vote nhiều nhất trong tháng
            $topVoter = ClanPointHistory::where('clan_id', $clan->id)
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->selectRaw('user_id, SUM(points_added) as total_points')
                ->groupBy('user_id')
                ->orderByDesc('total_points')
                ->first();

            // Nếu có người vote, lấy thông tin người đó
            $topVoterName = $topVoter ? $topVoter->user->name : null;
            $topVoterPoints = $topVoter ? $topVoter->total_points : 0;

            // Thêm thông tin vào mảng kết quả
            $result[] = [
                'clan_name' => $clan->name,
                'total_points' => $totalPoints,
                'top_voter' => $topVoterName,
                'top_voter_points' => $topVoterPoints,
            ];
        }

        return response()->json($result, 200);
    }
}
