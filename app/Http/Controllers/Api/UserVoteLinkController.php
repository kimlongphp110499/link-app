<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\VoteHistory;
use App\Models\ClanPointHistory;
use App\Models\User;
use App\Models\Clan;
class UserVoteLinkController extends Controller
{
    // Phương thức vote cho link
    public function vote(Request $request, $linkId)
    {
        $auth =  auth()->user();
        $user = User::findOrFail($auth->id);

        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        // Kiểm tra nếu user tồn tại
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Kiểm tra nếu link tồn tại
        $link = Link::findOrFail($linkId);

        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        // Kiểm tra số điểm người dùng đủ để vote (ví dụ mỗi lần vote trừ đi 100 điểm)
        $pointsRequired = $request->points;
        if ($user->points < $pointsRequired) {
            return response()->json(['message' => 'Not enough points to vote'], 400);
        }

        // Trừ điểm của người dùng
        $user->points -= $pointsRequired;
        $user->save();

        $link->total_votes = $link->total_votes + $request->points;
        $link->save();
        // Lưu lịch sử vote vào bảng vote_histories
        $voteHistory = VoteHistory::create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'points_voted' => $pointsRequired,
        ]);
        // Cập nhật tổng điểm đã vote cho link

        // vote cho clan
        $addPointsToClan = false;
        if($link->clans) {
            foreach($link->clans as $clan) {
                $addPointsToClan = $this->addPointsToClan($request, $auth->id, $clan->id);
            }
        }

        return response()->json([
            'message' => 'Vote successful',
            'user_id' => $user->id,
            'link_id' => $link->id,
            'user_points' => $user->points,
            'total_votes_for_link' => $link->total_votes,
            'point_added_to_clan' => $addPointsToClan,
        ], 200);
    }

    // Lịch sử vote của người dùng
    public function voteHistory()
    {
        $auth =  auth()->user();
        // Kiểm tra nếu user tồn tại, nếu không trả về lỗi 404
        $user = User::find($auth->id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Truy vấn vote history của người dùng và chỉ lấy những thông tin cần thiết
        $voteHistories = $user->voteHistories()
            ->with(['link' => function($query) {
                $query->select('id', 'title', 'url'); // Lấy chỉ các trường cần thiết từ bảng links
            }])
            ->select('id', 'user_id', 'link_id', 'points_voted', 'created_at') // Chỉ lấy các trường cần thiết từ bảng vote_histories
            ->get();

        // Trả về danh sách vote histories của người dùng
        return response()->json($voteHistories, 200);
    }


     // Cộng điểm cho clan
     public function addPointsToClan(Request $request, $userId, $clanId)
     {
         $request->validate([
             'points' => 'required|integer|min:1',
         ]);
 
         $clan = Clan::findOrFail($clanId);
         $user = User::findOrFail($userId);
         $pointsAdded = $request->points;
 
         // Kiểm tra xem người dùng đã từng cộng điểm cho clan này chưa
        $existingHistory = ClanPointHistory::where('user_id', $user->id)
            ->where('clan_id', $clan->id)
            ->exists();

        // Nếu đã có lịch sử cộng điểm thì không cho phép cộng thêm
        if ($existingHistory) {
            return false;
        }
         // Cộng điểm cho clan
         $clan->points += $pointsAdded;
         $clan->save();
 
         // Lưu lịch sử cộng điểm
         ClanPointHistory::create([
             'user_id' => $user->id,
             'clan_id' => $clan->id,
             'points_added' => $pointsAdded,
         ]);
 
         return true;
     }

    public function rankLinks()
    {
        // Lấy tất cả các link với tổng điểm vote
        $links = Link::orderByDesc('total_votes') // Sắp xếp theo tổng số điểm được vote (giảm dần)
            ->get()
            ->map(function ($link) {
                // Lấy user đã vote nhiều điểm nhất cho mỗi link
                $userWithMaxVotes = $link->voteHistories()
                    ->selectRaw('user_id, SUM(points_voted) as total_points')
                    ->groupBy('user_id')
                    ->orderBy('total_points', 'asc')
                    ->first(); // Lấy user có tổng điểm vote nhiều nhất
                
                // Thêm thông tin user và số điểm vote vào mỗi link
                $link->user_with_max_votes = $userWithMaxVotes ? User::find($userWithMaxVotes->user_id) : null;
                $link->user_max_vote_points = $userWithMaxVotes ? $userWithMaxVotes->total_points : 0;
    
                return $link;
            });
    
        return response()->json($links, 200);
    }

    public function searchLinks(Request $request)
    {
        $query = $request->get('query'); // Lấy query từ tham số tìm kiếm

        // Kiểm tra nếu query tồn tại
        if ($query) {
            // Tìm kiếm các link theo tiêu đề hoặc URL
            $links = Link::where('title', 'like', '%' . $query . '%')
                ->orWhere('video_id', 'like', '%' . $query . '%')
                ->get();
        } else {
            // Nếu không có query, trả về tất cả các link
            $links = Link::all();
        }

        return response()->json($links, 200);
    }

}
