<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClanLink;
use App\Models\ClanTempMember;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Clan;
use App\Models\VoteHistory;
use App\Models\ClanPointHistory;
use App\Models\User;
use App\Services\LinkService;
use App\Services\ClanPointHistoryService;
use Illuminate\Support\Facades\Log;
use App\Models\Schedule;

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

    public function vote(Request $request, $linkId): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->user()->id;
        $user = User::findOrFail($userId);
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $link = Link::where('video_id', $linkId)->first();
        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        $schedule = Schedule::where('link_id', $linkId)->first();
        if ($schedule) {
            return response()->json(['message' => 'Link in schedule'], 404);
        }

        $pointsRequired = $request->points;
        if ($user->points < $pointsRequired) {
            return response()->json(['message' => 'Not enough points to vote'], 400);
        }

        $user->points -= $pointsRequired;
        $user->save();
        $link->total_votes = $link->total_votes + $request->points;
        $link->save();
        $voteHistory = VoteHistory::create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'points_voted' => $pointsRequired,
        ]);
        if ($voteHistory) {
            $exitUserClanTempMember = ClanTempMember::where('user_id', $userId)
                ->where('link_id', $link->id)
                ->exists();
            if (!$exitUserClanTempMember) {
                $clans =  ClanLink::select('clan_id')->where('link_id', $link->id)->get();
                foreach ($clans as $clan) {
                    try {
                        $this->addPointsToClan($request, $user->id, $clan->clan_id);

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
    public function addPointsToClan(Request $request, $userId, $clanId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $clan = Clan::findOrFail($clanId);
        $user = User::findOrFail($userId);
        $pointsAdded = 1;
        // Kiểm tra xem người dùng đã từng cộng điểm cho clan này chưa
        $existingHistory = ClanPointHistory::where('user_id', $user->id)
            ->where('clan_id', $clan->id)
            ->exists();
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
    public function voteHistory(): \Illuminate\Http\JsonResponse
    {
        $auth = auth()->user();
        $user = User::find($auth->id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $voteHistories = $user->voteHistories()
            ->with(['link' => function ($query) {
                $query->select('id', 'title', 'url');
            }])
            ->select('id', 'user_id', 'link_id', 'points_voted', 'created_at')
            ->get();

        return response()->json($voteHistories, 200);
    }

    public function rankLinks(): \Illuminate\Http\JsonResponse
    {

        return response()->json($this->linkService->videoRank(), 200);
    }

    public function searchLinks(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('query');
        if ($query) {
            $normalizedQuery = str_replace(' ', '', $query);
            $links = Link::whereRaw("REPLACE(title, ' ', '') LIKE ?", ["%{$normalizedQuery}%"])
                ->orWhereRaw("REPLACE(video_id, ' ', '') LIKE ?", ["%{$normalizedQuery}%"])
                ->get();
        } else {
            $links = Link::all();
        }

        return response()->json($links, 200);
    }
}
