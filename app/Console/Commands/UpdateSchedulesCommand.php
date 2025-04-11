<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule as LaravelSchedule;

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
            Schedule::truncate();
            Log::info("Cleared current schedule");

            // Cập nhật schedules với video tiếp theo
            $this->updateSchedules($startTime->addSeconds($videoDuration + 3));
        }
    }

    protected function updateSchedules($startTime)
    {
        // Lấy danh sách links, ưu tiên theo total_votes, nếu bằng nhau thì lấy theo id (mới nhất)
        $video = Link::where('total_votes', '>', 0)
            ->orderBy('total_votes', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$video) {
            $video = Link::inRandomOrder()->first();
        }
    
        // Nếu vẫn không có video, ghi log và dừng xử lý
        if (!$video) {
            Log::warning("No videos found to schedule");
            return;
        }

        // Chỉ thêm 1 video (video có votes cao nhất, hoặc mới nhất nếu votes bằng nhau)
        Schedule::create([
            'link_id' => $video->id,
            'start_time' => $startTime,
        ]);
        Log::info("Added new schedule for video: " . $video->title . " with start time: " . $startTime->toIso8601String());
    }
}
