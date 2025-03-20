<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class SaveMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $userId;
    protected $messageContent;

    public function __construct($userId, $messageContent)
    {
        $this->userId = $userId;
        $this->messageContent = $messageContent;
    }

    public function handle()
    {
        Message::create([
            'user_id' => $this->userId,
            'message' => $this->messageContent,
        ]);
    }
}
