<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DailyBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE', 'database_name');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPassword = env('DB_PASSWORD', '');
        $backupPath = storage_path('backups'); // Thư mục lưu backup
        $backupFile = $backupPath . '/backup_' . date('Y-m-d_H-i-s') . '.sql'; // Tên file backup

        // Tạo thư mục backup nếu chưa tồn tại
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0777, true);
        }

        // Lệnh backup bằng mysqldump
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($backupFile)
        );

        // Thực thi lệnh
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Backup completed successfully! File saved at: $backupFile");
            $this->deleteOldBackups($backupPath);
        } else {
            $this->error("Backup failed! Please check your configuration.");
            $this->error("Command executed: $command");
        }
    }

    private function deleteOldBackups($backupPath)
    {
        $files = File::files($backupPath);

        foreach ($files as $file) {
            $fileModifiedTime = Carbon::createFromTimestamp($file->getMTime());
            if (now()->diffInDays($fileModifiedTime) > 7) {
                File::delete($file);
                $this->info("Deleted old backup: " . $file->getFilename());
            }
        }
    }
}
