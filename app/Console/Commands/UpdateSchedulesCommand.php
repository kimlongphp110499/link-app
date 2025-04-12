<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\LinkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UpdateSchedulesCommand extends Command
{
    protected $signature = 'schedules:update';
    protected $description = 'Update schedules and schedule the next update';

    public function handle()
    {
        $now = Carbon::now();
        Log::info("Running UpdateSchedulesCommand at: " . $now->toIso8601String());

        // Lấy schedule hiện tại (chỉ có 1 dòng dữ liệu)
        $currentSchedule = Schedule::first();

        if (!$currentSchedule) {
            return;
        }

        $startTime = Carbon::parse($currentSchedule->start_time);
        
        $elapsedSeconds = $now->diffInSeconds($startTime);
        $videoDuration = $currentSchedule->link->duration;

        // Nếu video hiện tại đã kết thúc (bao gồm 3 giây chờ)
        if ($elapsedSeconds*-1 >= $videoDuration + 3) {
            // Reset votes của video vừa phát xong về 0
            $currentLink = $currentSchedule->link;
            $currentLink->update(['total_votes' => 0]);
            Log::info("Reset votes to 0 for link: " . $currentLink->title);

            // Xóa schedule hiện tại
            $linkId = Schedule::value('link_id'); // Lấy giá trị link_id của dòng dữ liệu duy nhất

            // Xóa các bản ghi liên quan trong bảng vote_histories
            if ($linkId !== null) {
                DB::table('vote_histories')
                    ->where('link_id', $linkId)
                    ->delete();
            }
            Schedule::truncate();
            Log::info("Cleared current schedule");

            // Cập nhật schedules với video tiếp theo
            $this->updateSchedules();
        }
    }

    protected function updateSchedules()
    {
        // Lấy danh sách links, ưu tiên theo total_votes, nếu bằng nhau thì lấy theo id (mới nhất)
       
        DB::beginTransaction();

        try {
            // Lấy link tiếp theo dựa trên total_votes và id (sử dụng raw query để tối ưu)
            $link = Cache::remember('next_link', 2, function () {
                return DB::selectOne("
                    SELECT id, total_votes, is_played, duration
                    FROM links
                    WHERE total_votes > 0
                    OR (total_votes = 0 AND is_played = 0)
                    ORDER BY total_votes DESC, id DESC
                    LIMIT 1
                ");
            });
        
            if ($link) {
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
}
