<?php

namespace App\Console\Commands;

use App\Models\ClanTempMember;
use App\Models\Link;
use App\Models\Schedule;
use App\Models\VoteHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessClanPoints;

class UpdateSchedulesCommand extends Command
{
    protected $signature = 'schedules:update';
    protected $description = 'Update schedules and schedule the next update';

    public function handle()
    {
        // $nextRunTime = Cache::get('next_run_time');
        // if ($nextRunTime) {
        //     $now = Carbon::now()->format('Y-m-d H:i:s');
        //     if($now < $nextRunTime) {
        //         Log::info("chưa đến time run: " . $nextRunTime);
        //         return;
        //     }
        // }

        $now = Carbon::now();
        Log::info("Running UpdateSchedulesCommand at: " . $now->toIso8601String());

        // Lấy schedule hiện tại (chỉ có 1 dòng dữ liệu)
        $currentSchedule = Schedule::first();

        if (!$currentSchedule) {
            Log::info("No current schedule found. Exiting command.");
            return;
        }

        $startTime = Carbon::parse($currentSchedule->start_time);
        $videoDuration = $currentSchedule->link->duration;

        // Tính toán thời gian cron job tiếp theo
        $nextRunTime = $startTime->copy()->addSeconds($videoDuration);
        Log::info("Next run time calculated as: " . $nextRunTime->toIso8601String());

        // Nếu đã đến thời điểm chạy tiếp theo
        if ($now->greaterThanOrEqualTo($nextRunTime)) {

            // Reset votes của video vừa phát xong về 0
            $currentLink = $currentSchedule->link;
            $currentLink->update(['total_votes' => 0]);
            Log::info("Reset votes to 0 for link: " . $currentLink->title);

            // Xóa schedule hiện tại
            $linkId = Schedule::value('link_id'); // Lấy giá trị link_id của dòng dữ liệu duy nhất

            // Xóa các bản ghi liên quan trong bảng vote_histories
            if ($linkId !== null) {
                VoteHistory::where('link_id', $linkId)
                    ->delete();
                ClanTempMember::where('link_id', $linkId)->delete();
                ProcessClanPoints::dispatch($linkId)
                ->onQueue('default');
            }
            Schedule::truncate();
            Log::info("Cleared current schedule");

            // Cập nhật schedules với video tiếp theo
            $this->updateSchedules();

            // Lưu thời gian chạy tiếp theo vào cache
            // $nextRunTime = Carbon::now()->addSeconds($videoDuration - 3);
            // Cache::put('next_run_time', $nextRunTime, 3600);
            // Log::info("Next run time for cron job set to: " . $nextRunTime->toIso8601String());

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
