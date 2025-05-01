<?php

namespace App\Jobs;

use App\Exports\reportPointClanAndStar;
use App\Mail\SendPointReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as BaseExcel;
use Illuminate\Support\Facades\Storage;

class SendMonthlyPointReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fileName = 'point_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        Excel::store(new reportPointClanAndStar(), 'public/' . $fileName);
        $filePath = storage::disk('public')->path($fileName);
        Event::listen(MessageSent::class, function (MessageSent $event) use ($fileName) {
                if (isset($fileName) && Storage::disk('public')->exists($fileName)) {
                    Storage::disk('public')->delete($fileName);
                    \Log::info('Deleted Excel file: ' . $fileName);
                }
        });
        Mail::to(env('MAIL_TO', 'doquang.jim@gmail.com'))->queue(new SendPointReport($filePath));
    }
}
