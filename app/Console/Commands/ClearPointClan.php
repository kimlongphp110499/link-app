<?php

namespace App\Console\Commands;

use App\Models\ClanPointHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

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
            Log::infor('All records in clan_point_histories table have been deleted.');
            DB::commit();
            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear clan point history error');
            return response()->json([
                'message' => 'Error while clearing clan_point_histories',
            ], 500);
        }
    }
}
