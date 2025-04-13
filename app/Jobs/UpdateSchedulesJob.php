<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateSchedulesJob extends Job
{
    public function handle()
    {
        $now = Carbon::now();

        // Lấy schedule hiện tại
        $currentSchedule = Schedule::first();

        if (!$currentSchedule) {
            Log::info("No current schedule found. Exiting job.");
            return;
        }

        $startTime = Carbon::parse($currentSchedule->start_time);
        $videoDuration = $currentSchedule->link->duration;

        // Tính toán thời gian cho công việc tiếp theo
        $nextRunTime = $startTime->copy()->addSeconds($videoDuration - 3);

        if ($now->greaterThanOrEqualTo($nextRunTime)) {
            // Reset votes của video vừa phát xong về 0
            $currentLink = $currentSchedule->link;
            $currentLink->update(['total_votes' => 0]);
            Log::info("Reset votes to 0 for link: " . $currentLink->title);

            // Xóa schedule hiện tại và các bản ghi liên quan
            $linkId = Schedule::value('link_id');
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
        $link = DB::selectOne("
            SELECT id, total_votes, is_played, duration
            FROM links
            WHERE total_votes > 0
            OR (total_votes = 0 AND is_played = 0)
            ORDER BY total_votes DESC, id DESC
            LIMIT 1
        ");

        if ($link) {
            DB::insert("
                INSERT INTO schedules (link_id, start_time)
                VALUES (?, ?)
            ", [$link->id, Carbon::now()]);

            DB::update("
                UPDATE links
                SET is_played = 1
                WHERE id = ?
            ", [$link->id]);

            Log::info("Link ID {$link->id} đã được phát và đánh dấu.");
        } else {
            DB::update("
                UPDATE links
                SET is_played = 0
            ");

            $link = DB::selectOne("
                SELECT id, total_votes, is_played, duration
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