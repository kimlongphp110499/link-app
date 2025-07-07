<?php

namespace App\Jobs;

use App\Models\Clan;
use App\Models\ClanPointHistory;
use App\Models\ClanTempMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessClanPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $linkId;

    public function __construct($linkId)
    {
        $this->linkId = $linkId;
    }

    public function handle()
    {
        try {
            DB::beginTransaction();

            $memberClan = ClanTempMember::select('user_id', 'link_id', 'clan_id')
                ->where('link_id', $this->linkId)
                ->lockForUpdate() // Thêm lock để tránh race condition
                ->get();

            if ($memberClan->isNotEmpty()) {
                $dataToInsert = [];
                $uniquePairs = []; // Track unique user_id and clan_id pairs
                $pointInsertClan = [];
        
                foreach ($memberClan as $member) {
                    $pairKey = $member->user_id . '_' . $member->clan_id; // Unique key for each pair
                    if (!isset($uniquePairs[$pairKey])) {
                        $dataToInsert[] = [
                            'user_id' => $member->user_id,
                            'clan_id' => $member->clan_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $uniquePairs[$pairKey] = true;
                        $clanId = $member->clan_id;
                        if (!isset($pointInsertClan[$clanId])) {
                            $pointInsertClan[$clanId] = 0;
                        }
                        $pointInsertClan[$clanId]++;
                    }
                }

                // Xóa ClanTempMember trước để tránh lặp lại xử lý
                ClanTempMember::where('link_id', $this->linkId)->delete();

                if (!empty($dataToInsert)) {
                    ClanPointHistory::insert($dataToInsert);
                }

                // Cập nhật điểm cho Clan với lock
                foreach ($pointInsertClan as $clanId => $count) {
                    Clan::where('id', $clanId)->lockForUpdate()->increment('points', $count);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing clan points job', [
                'link_id' => $this->linkId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
