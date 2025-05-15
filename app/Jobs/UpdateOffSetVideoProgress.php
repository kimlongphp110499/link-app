<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class UpdateOffSetVideoProgress implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $videoId;
    protected $duration;

    /**
     * Create a new job instance.
     */
    public function __construct($videoId, $duration)
    {
        $this->videoId = $videoId;
        $this->duration = $duration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = "video_progress_{$this->videoId}";
        $currentSecond = Cache::get($cacheKey, 0);
        if ($currentSecond < $this->duration) {
//            dd($currentSecond, $this->duration);
            $currentSecond += 1000;

            Cache::put($cacheKey, $currentSecond, ($this->duration + 300000) / 1000);
            UpdateOffSetVideoProgress::dispatch($this->videoId, $this->duration)
                ->delay(now()->addSecond());
        } else {
            Cache::put($cacheKey, 0, ($this->duration + 300000) / 1000);
        }
    }
}
