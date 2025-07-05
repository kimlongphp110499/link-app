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
                $pointInsertClan = [];

                foreach ($memberClan as $member) {
                    $dataToInsert[] = [
                        'user_id' => $member->user_id,
                        'clan_id' => $member->clan_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $clanId = $member->clan_id;
                    $pointInsertClan[$clanId] = ($pointInsertClan[$clanId] ?? 0) + 1;
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