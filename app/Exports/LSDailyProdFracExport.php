<?php

namespace App\Exports;

use App\Models\LSDailyProductionFractionation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class LSDailyProdFracExport implements FromCollection, WithHeadings, WithEvents
{
    protected $tanggal;

    public function __construct($tanggal)
    {
        $this->tanggal = $tanggal;
    }

    public function collection()
    {
        return LSDailyProductionFractionation::whereDate('transaction_date', $this->tanggal)
            ->where('flag', 'T')
            ->orderBy('work_center', 'asc')
            ->orderBy('shift', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    $row->work_center,
                    $row->shift,
                    $row->oil_type_rm,
                    $row->oil_type_rm_from_tank,
                    $row->oil_type_rm_awal_flowmeter,
                    $row->oil_type_rm_akhir_flowmeter,
                    $row->oil_type_rm_total,

                    $row->oil_type_fgs,
                    $row->oil_type_fgs_to_tank,
                    $row->oil_type_fgs_awal_flowmeter,
                    $row->oil_type_fgs_akhir_flowmeter,
                    $row->oil_type_fgs_total,

                    $row->oil_type_fgh,
                    $row->oil_type_fgh_to_tank,
                    $row->oil_type_fgh_awal_flowmeter,
                    $row->oil_type_fgh_akhir_flowmeter,
                    $row->oil_type_fgh_total,

                    $row->uu_flowmeter_before,
                    $row->uu_flowmeter_after,
                    $row->uu_flowmeter_total,
                    $row->uu_listrik,
                    $row->uu_air,
                    
                    $row->remarks,
                ];
            });
    }

    public function headings(): array
    {
        $headerRows = 6;
        return [
            ['Form No.     : F/RFA-XXX'],
            ['Date Issued  : YYMMDD'],
            ['Revision     : 01'],
            ['Rev. Date    : YYMMDD'],
            [],
            [
                'Work Center',
                'Shift',
                'Oil Type RM',
                'Tank Asal',
                'RM Awal (Flowmeter)',
                'RM Akhir (Flowmeter)',
                'Total RM (KG)',

                'Oil Type FGS',
                'FGS Tank Tujuan',
                'FGS Awal (Flowmeter)',
                'FGS Akhir (Flowmeter)',
                'Total FGS (KG)',

                'Oil Type FGH',
                'FGH Tank Tujuan',
                'FGH Awal (Flowmeter)',
                'FGH Akhir (Flowmeter)',
                'Total FGH (KG)',

                'UU Flowmeter Before',
                'UU Flowmeter After',
                'UU Flowmeter Total',
                'UU Listrik',
                'UU Air',

                'Remarks'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'W'; // Based on 23 columns
                
                // Header Merging and Styling
                foreach ([1, 2, 3, 4] as $row) {
                    $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                }
                
                // Column AutoSize
                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Data Header Styling
                $sheet->getStyle("A6:{$lastColumn}6")->getFont()->setBold(true);
                $sheet->getStyle("A6:{$lastColumn}6")->getAlignment()->setHorizontal('center')->setVertical('center');
            }
        ];
    }
}