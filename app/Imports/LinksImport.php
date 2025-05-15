<?php

namespace App\Imports;

use App\Models\Link;
use App\Models\Clan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LinksImport implements ToModel, WithHeadingRow
{
    /**
     * Map từng hàng trong file Excel vào model Link.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if($row['name']) {
            $link = Link::updateOrCreate(
                ['title' => $row['name']],
                [
                    'url' => $row['link'],
                    'duration' => $row['time'] ?? 0,
                    'video_id' => $row['id'] ?? 0,
                    'total_votes' => $row['total_votes'] ?? 0,
                ]
            );

            if (!empty($row['performer'])) {
                $clanNames = explode(',', $row['performer']);
                $clanIds = [];
    
                foreach ($clanNames as $clanName) {
                    $clan = Clan::firstOrCreate(['name' => trim($clanName)]);
                    $clanIds[] = $clan->id;
                }
    
                $link->clans()->sync($clanIds);
            }
    
            return $link;
        }
    }
}