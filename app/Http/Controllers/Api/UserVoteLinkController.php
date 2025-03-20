<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\User;
use App\Models\VoteHistory;

class UserVoteLinkController extends Controller
{
    // Phương thức vote cho link
    public function vote(Request $request, $userId, $linkId)
    {
        // Kiểm tra nếu user tồn tại
        $user = User::findOrFail($userId);

        // Kiểm tra nếu link tồn tại
        $link = Link::findOrFail($linkId);

        // Kiểm tra số điểm người dùng đủ để vote (ví dụ mỗi lần vote trừ đi 100 điểm)
        $pointsRequired = 100;
        if ($user->points < $pointsRequired) {
            return response()->json(['message' => 'Not enough points to vote'], 400);
        }

        // Trừ điểm của người dùng
        $user->points -= $pointsRequired;
        $user->save();

        // Lưu lịch sử vote vào bảng vote_histories
        $voteHistory = VoteHistory::create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'points_voted' => $pointsRequired,
        ]);

        // Cập nhật tổng điểm đã vote cho link
        $link->total_votes += $pointsRequired;
        $link->save();

        return response()->json([
            'message' => 'Vote successful',
            'user_points' => $user->points,
            'total_votes_for_link' => $link->total_votes,
        ], 200);
    }

    // Lịch sử vote của người dùng
    public function voteHistory($userId)
    {
        $user = User::findOrFail($userId);
        $voteHistories = $user->voteHistories()->with('link')->get();

        return response()->json($voteHistories, 200);
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
                    ->orderByDesc('total_points')
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
                ->orWhere('url', 'like', '%' . $query . '%')
                ->get();
        } else {
            // Nếu không có query, trả về tất cả các link
            $links = Link::all();
        }

        return response()->json($links, 200);
    }

}
