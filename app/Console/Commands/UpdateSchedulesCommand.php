<?php

namespace App\Console\Commands;

use App\Jobs\UpdateNextScheduleJob;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateSchedulesCommand extends Command
{
    protected $signature = 'schedules:update';
    protected $description = 'Update schedules and schedule the next update';

    public function handle()
    {
        // Kiểm tra trong cache thời gian chạy tiếp theo
        $nextRunTime = Cache::get('next_run_time');
        $now = Carbon::now();

        if ($nextRunTime) {
            if ($now->lessThan($nextRunTime)) {
                Log::info("Chưa đến thời điểm chạy job: " . $nextRunTime);
                return;
            }
        }

        Log::info("Dispatching UpdateSchedulesJob at: " . $now->toIso8601String());

        // Dispatch job để xử lý logic cập nhật schedule
        UpdateNextScheduleJob::dispatch();
    }
}