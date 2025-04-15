<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClanLink;
use App\Models\ClanTempMember;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\VoteHistory;
use App\Models\ClanPointHistory;
use App\Models\User;
use App\Models\Clan;
use App\Services\LinkService;
use App\Services\ClanPointHistoryService;
use Illuminate\Support\Facades\Log;

class UserVoteLinkController extends Controller
{
    protected $linkService;
    protected $clanPointHistoryService;

    /**
     * Inject LinkService in Controller.
     *
     * @param linkService $linkService
     */
    public function __construct(
        LinkService             $linkService,
        ClanPointHistoryService $clanPointHistoryService
    )
    {
        $this->linkService = $linkService;
        $this->clanPointHistoryService = $clanPointHistoryService;
    }

    // Phương thức vote cho link
    public function vote(Request $request, $linkId)
    {
        $userId = auth()->user()->id;
        $user = User::findOrFail($userId);

        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        // Kiểm tra nếu user tồn tại
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Kiểm tra nếu link tồn tại
        $link = Link::where('video_id', $linkId)->first();

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

        if ($voteHistory) {
            $clans =  ClanLink::select('clan_id')->where('link_id', $link->id)->get();
            foreach ($clans as $clan) {
                $exitUserClan = $this->clanPointHistoryService->existingHistory($userId, $clan->clan_id);
                if (!$exitUserClan) {
                    try {
                        ClanTempMember::create([
                            'user_id' => $user->id,
                            'link_id' => $link->id,
                            'clan_id' => $clan->clan_id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Unexpected error while fetching honors: ' . $e->getMessage(), [
                            'exception' => $e->getTraceAsString(),
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Vote successful',
            'user_id' => $user->id,
            'link_id' => $link->id,
            'user_points' => $user->points,
            'total_votes_for_link' => $link->total_votes,
        ], 200);
    }

    // Lịch sử vote của người dùng
    public function voteHistory()
    {
        $auth = auth()->user();
        // Kiểm tra nếu user tồn tại, nếu không trả về lỗi 404
        $user = User::find($auth->id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Truy vấn vote history của người dùng và chỉ lấy những thông tin cần thiết
        $voteHistories = $user->voteHistories()
            ->with(['link' => function ($query) {
                $query->select('id', 'title', 'url'); // Lấy chỉ các trường cần thiết từ bảng links
            }])
            ->select('id', 'user_id', 'link_id', 'points_voted', 'created_at') // Chỉ lấy các trường cần thiết từ bảng vote_histories
            ->get();

        // Trả về danh sách vote histories của người dùng
        return response()->json($voteHistories, 200);
    }

    public function rankLinks()
    {
        $links = $this->linkService->videoRank();

        return response()->json($links, 200);
    }

    public function searchLinks(Request $request)
    {
        $query = $request->get('query'); // Lấy query từ tham số tìm kiếm

        // Kiểm tra nếu query tồn tại
        if ($query) {
            // Loại bỏ khoảng trắng trong query
            $normalizedQuery = str_replace(' ', '', $query);

            // Tìm kiếm các link theo tiêu đề hoặc ID video
            $links = Link::whereRaw("REPLACE(title, ' ', '') LIKE ?", ["%{$normalizedQuery}%"])
                ->orWhereRaw("REPLACE(video_id, ' ', '') LIKE ?", ["%{$normalizedQuery}%"])
                ->get();
        } else {
            // Nếu không có query, trả về tất cả các link
            $links = Link::all();
        }

        return response()->json($links, 200);
    }
}
