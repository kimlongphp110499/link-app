<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateNextScheduleJob
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels, \Illuminate\Foundation\Bus\Dispatchable;

    public function __construct()
    {
        // Nếu cần truyền tham số vào job, khai báo ở đây.
    }

    public function handle()
    {
        $nextRunTime = Cache::get('next_run_time');
        $now = Carbon::now();

        if ($nextRunTime && $now->lessThan($nextRunTime)) {
            Log::info("Chưa đến thời điểm chạy job: " . $nextRunTime);
            return;
        }

        Log::info("Running UpdateSchedulesJob at: " . $now->toIso8601String());

        // Lấy schedule hiện tại
        $currentSchedule = Schedule::first();

        if (!$currentSchedule) {
            Log::info("No current schedule found. Exiting job.");
            return;
        }

        $startTime = Carbon::parse($currentSchedule->start_time);
        $videoDuration = $currentSchedule->link->duration;

        // Tính toán thời gian chạy tiếp theo
        $nextRunTime = $startTime->copy()->addSeconds($videoDuration - 3);
        Log::info("Next run time calculated as: " . $nextRunTime->toIso8601String());

        if ($now->greaterThanOrEqualTo($nextRunTime)) {
            // Reset votes của video vừa phát xong
            $currentLink = $currentSchedule->link;
            $currentLink->update(['total_votes' => 0]);
            Log::info("Reset votes to 0 for link: " . $currentLink->title);

            // Xóa schedule hiện tại và các bản ghi liên quan
            $linkId = Schedule::value('link_id');
            if ($linkId !== null) {
                DB::table('vote_histories')->where('link_id', $linkId)->delete();
            }
            Schedule::truncate();
            Log::info("Cleared current schedule");

            // Cập nhật thời gian chạy tiếp theo
            $this->updateSchedules();

            // Lưu thời gian chạy tiếp theo vào cache
            $nextRunTime = Carbon::now()->addSeconds($videoDuration - 3);
            Cache::put('next_run_time', $nextRunTime, 3600);
            Log::info("Next run time for cron job set to: " . $nextRunTime->toIso8601String());
        }
    }

    protected function updateSchedules()
    {
        $link = DB::selectOne("
            SELECT id, total_votes, is_played, duration
            FROM links
            WHERE total_votes > 0
            OR (total_votes = 0 AND is_played = 0)
            ORDER BY total_votes DESC, id DESC
            LIMIT 1
        ");

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
    }
}