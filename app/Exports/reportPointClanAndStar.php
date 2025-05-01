<?php

namespace App\Exports;

use App\Models\Clan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class reportPointClanAndStar implements FromCollection, WithHeadings, WithTitle, WithStyles, WithCustomStartCell
{
    public function collection()
    {
        return DB::table('clans')
            ->select(
                'clans.id',
                'clans.name',
                DB::raw('COALESCE((SELECT COUNT(*) FROM clan_point_histories WHERE clan_point_histories.clan_id = clans.id), 0) as total_clan_points'),
                DB::raw('COALESCE(SUM(vote_histories.points_voted), 0) as total_vote_points')
            )
            ->leftJoin('clan_link', 'clans.id', '=', 'clan_link.clan_id')
            ->leftJoin('links', 'clan_link.link_id', '=', 'links.id')
            ->leftJoin('vote_histories', 'links.id', '=', 'vote_histories.link_id')
            ->groupBy('clans.id', 'clans.name')
            ->get()
            ->map(function ($item) {
                // Làm sạch dữ liệu UTF-8
                $item->name = mb_convert_encoding($item->name, 'UTF-8', 'UTF-8');
                if (!mb_check_encoding($item->name, 'UTF-8')) {
                    \Log::warning('Invalid UTF-8 in clan_name: ' . $item->name);
                }
                return $item;
            });
    }

    public function headings(): array
    {
        return [
            'Clan ID',
            'Clan Name',
            'Total Clan Points',
            'Total Vote Points',
        ];
    }

    public function title(): string
    {
        return 'Monthly Clan Point Report';
    }

    public function startCell(): string
    {
        return 'A3'; // Headings bắt đầu từ A3, dữ liệu từ A4
    }

    public function styles(Worksheet $sheet)
    {
        // Title (hàng 1)
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', $this->title());
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tiêu đề cột (hàng 3)
        $sheet->getStyle('A3:D3')->getFont()->setBold(true);
        $sheet->getStyle('A3:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:D3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D3D3D3');

        // Viền cho table
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A3:D{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Tự động điều chỉnh chiều rộng cột
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            // Hàng 2 là khoảng trống
            2 => ['font' => ['size' => 12]],
        ];
    }
}
