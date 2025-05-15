<?php

namespace App\Console;

use App\Jobs\SendMonthlyPointReport;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('schedules:update')->everyTwoSeconds();
        $schedule->command('log:clear')->everyFourHours();
        $schedule->command('clan:clear-points')->dailyAt('23:59');
        $schedule->job(new SendMonthlyPointReport())->dailyAt('23:55');
//        $schedule->command('clan:clear-points')->monthlyOn(1, '00:00');
//        $schedule->job(new SendMonthlyPointReport())
//               ->lastDayOfMonth('23:59')
//               ->when(function () {
//                   return now()->isLastOfMonth();
//               });

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
