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
        $link = Link::updateOrCreate(
            ['title' => $row['title']],
            [
                'url' => $row['url'],
                'video_id' => $row['video_id'],
                'total_votes' => $row['total_votes'],
                'duration' => $row['duration'],
            ]
        );

        if (!empty($row['clans'])) {
            $clanNames = explode(',', $row['clans']);
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