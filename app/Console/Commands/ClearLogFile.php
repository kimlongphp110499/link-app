<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearLogFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the content of the laravel.log file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Path to the laravel.log file
        $logFilePath = storage_path('logs/laravel.log');
        $cronFilePath = storage_path('logs/cron.log');
        // Check if the file exists
        if (file_exists($logFilePath) && file_exists($cronFilePath)) {
            // Clear the file content
            file_put_contents($logFilePath, '');
            file_put_contents($cronFilePath, '');
            $this->info('Log file cleared successfully.');
        }
        else {
            $this->error('Log file does not exist.');
        }

        return Command::SUCCESS;
    }
}