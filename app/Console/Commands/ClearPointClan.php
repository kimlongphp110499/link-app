<?php

namespace App\Console\Commands;

use App\Models\Clan;
use App\Models\ClanPointHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearPointClan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clan:clear-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all points in clan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            ClanPointHistory::truncate();
            Clan::query()->update(['points' => 0]);
            Log::info('All records in clan_point_histories table have been deleted.');
            DB::commit();
            return ['success' => true, 'message' => 'All clan points and histories have been reset.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear clan point history error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error while clearing clan_point_histories', 'error' => $e->getMessage()];
        }
    }
}
