<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LinkService
{
    public function videoSchedule()
    {
        DB::beginTransaction();

        try {
            // Lấy link tiếp theo dựa trên total_votes và id (sử dụng raw query để tối ưu)
            $link = DB::selectOne("
                SELECT id, title, url, total_votes, clan_id, video_id, duration
                FROM links
                WHERE total_votes > 0
                OR (total_votes = 0 AND is_played = 0)
                ORDER BY total_votes DESC, id DESC
                LIMIT 1
            ");

            if ($link) {
                // Xóa tất cả các dòng trong bảng schedules (truncate nhanh hơn delete)
                DB::statement("TRUNCATE TABLE schedules");

                // Insert link vào bảng schedules
                DB::insert("
                    INSERT INTO schedules (link_id, start_time)
                    VALUES (?, ?)
                ", [$link->id, Carbon::now()]);

                // Cập nhật trạng thái is_played = true
                DB::update("
                    UPDATE links
                    SET is_played = 1
                    WHERE id = ?
                ", [$link->id]);
                $nextRunTime = Carbon::now()->addSeconds($link->duration - 3);
                Cache::put('next_run_time', $nextRunTime, 3600);
                Log::info("Link ID {$link->id} controller đã được phát và đánh dấu.");
            } else {
                // Nếu tất cả các link đã được phát, reset trạng thái
                DB::update("
                    UPDATE links
                    SET is_played = 0
                ");

                $link = DB::selectOne("
                    SELECT id, title, url, total_votes, clan_id, video_id, duration
                    FROM links
                    WHERE total_votes > 0
                    OR (total_votes = 0 AND is_played = 0)
                    ORDER BY total_votes DESC, id DESC
                    LIMIT 1
                ");

                DB::insert("
                INSERT INTO schedules (link_id, start_time)
                VALUES (?, ?)
            ", [$link->id, Carbon::now()]);
                Log::info("Tất cả các link đã được phát, reset trạng thái is_played.");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Đã xảy ra lỗi: " . $e->getMessage());
        }
    }

    public function videoRank()
    {
        $ranks = DB::table('links')
            ->leftJoin('schedules', 'links.id', '=', 'schedules.link_id')
            ->whereNull('schedules.link_id')
            ->where(function($query) {
                $query->where('total_votes', '>', 0)
                    ->orWhere(function($query) {
                        $query->where('total_votes', 0)->where('is_played', 0);
                    });
            })
            ->select('links.*')
            ->orderBy('total_votes', 'desc')
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();

        if ($ranks->count() < 3) {
            $existingIds = $ranks->pluck('id')->toArray();

            $additionalRanks = DB::table('links')
                ->leftJoin('schedules', 'links.id', '=', 'schedules.link_id')
                ->whereNull('schedules.link_id')
                ->whereNotIn('links.id', $existingIds)
                ->select('links.*')
                ->orderBy('total_votes', 'desc')
                ->orderBy('links.id', 'desc')
                ->limit(3 - $ranks->count())
            ->get();

            $ranks = $ranks->merge($additionalRanks);
        }

        $linkIds = $ranks->pluck('id')->toArray();
        $counts = DB::table('clan_temp_members')
                ->select('link_id', DB::raw('COUNT(DISTINCT user_id) as user_count'))
                ->whereIn('link_id', $linkIds)
                ->groupBy('link_id')
                ->get()
                ->pluck('user_count', 'link_id');
        $ranks->each(function ($item) use ($counts) {
            $item->clan_temp_point = $counts[$item->id] ?? 0;
        });

        return $ranks;
    }
}
