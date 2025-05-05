<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClanPointHistory;
use App\Models\VoteHistory;
use Carbon\Carbon;
use App\Models\Clan;
use App\Models\User;

class ClanController extends Controller
{
    public function getClansWithTopVoter()
    {
        $clans = Clan::with('links')->orderByDesc('points') // Sắp xếp các clans theo tổng điểm
        ->take(10)
        ->get();

        // Mảng kết quả
        $result = [];

        foreach ($clans as $clan) {
            $totalVotes = $clan->points;

            // Lấy người vote nhiều nhất trong tháng
            // $clanPoints = ClanPointHistory::where('clan_id', $clan->id)->orderByDesc('created_at')->get();
            if($clan->links) {
                $topVoter = VoteHistory::withTrashed()->whereIn('link_id', $clan->links->pluck('id'))
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->selectRaw('user_id, SUM(points_voted) as total_votes')
                ->groupBy('user_id')
                ->orderByDesc('total_votes')
                ->first();
            }

           // Kiểm tra nếu $topVoter không null, lấy thông tin user
            $topVoterName = null;
            $topVoterUserId = null;
            $topVoterVotes = 0;

            if ($topVoter) {
                $topVoterUser = User::find($topVoter->user_id); // Lấy thông tin user từ bảng User
                if ($topVoterUser) {
                    $topVoterName = $topVoterUser->name;
                    $topVoterUserId = $topVoterUser->id;
                }
                $topVoterVotes = $topVoter->total_votes;
            }
            // Thêm thông tin vào mảng kết quả
            $result[] = [
                'clan_name' => $clan->name,
                'total_votes' => $totalVotes, // Tổng số lần vote cho clan
                'top_voter' => $topVoterName,
                'top_voter_user_id' => $topVoterUserId,
                'top_voter_votes' => (int) $topVoterVotes, // Số lần vote của người vote nhiều nhất
            ];
        }

        return response()->json($result, 200);
    }
}
