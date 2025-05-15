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
            if ($clan->links && $clan->links->isNotEmpty() && $clan->points > 0) {
                $linkIds = $clan->links->pluck('id')->toArray();
                $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
                $endOfMonth = Carbon::now()->endOfMonth()->toDateTimeString();
            
                $topVoter = \DB::selectOne(
                    'SELECT user_id, SUM(points_voted) as total_votes 
                     FROM vote_histories 
                     WHERE link_id IN ('.implode(',', array_fill(0, count($linkIds), '?')).') 
                     AND created_at BETWEEN ? AND ? 
                     AND (vote_histories.deleted_at IS NOT NULL 
                          OR (deleted_at IS NULL 
                              AND EXISTS (SELECT 1 FROM schedules WHERE schedules.link_id = vote_histories.link_id))) 
                     GROUP BY user_id 
                     ORDER BY total_votes DESC 
                     LIMIT 1',
                    array_merge($linkIds, [$startOfMonth, $endOfMonth])
                );
            } else {
                $topVoter = null;
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
                    $topVoterAvatar = $topVoterUser->avatar ?? null;
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
                'top_voter_avatar' => $topVoterAvatar ?? null,
            ];
        }

        return response()->json($result, 200);
    }
}
