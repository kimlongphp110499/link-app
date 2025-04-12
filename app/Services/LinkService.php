<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        
                Log::info("Link ID {$link->id} đã được phát và đánh dấu.");
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
        // Truy vấn chính
        $ranks = collect(DB::select("
            SELECT *
            FROM links
            WHERE id NOT IN (
                SELECT link_id FROM schedules
            )
            AND (
                total_votes > 0 OR (total_votes = 0 AND is_played = 0)
            )
            ORDER BY total_votes DESC, id DESC
            LIMIT 3
        "));

        // Nếu kết quả trả về < 3, bổ sung thêm các hàng dựa trên id
        if ($ranks->count() < 3) {
            $additionalRanks = collect(DB::select("
                SELECT *
                FROM links
                WHERE id NOT IN (
                    SELECT link_id FROM schedules
                )
                ORDER BY id DESC
                LIMIT " . (3 - $ranks->count())
            ));

            $ranks = $ranks->merge($additionalRanks);
        }

        return $ranks;
    }
}