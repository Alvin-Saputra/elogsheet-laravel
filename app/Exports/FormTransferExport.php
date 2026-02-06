<?php

namespace App\Exports;

use App\Models\LSFormTransferHeader;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class FormTransferExport implements FromView
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        $records = LSFormTransferHeader::with('details')
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->where('flag', 'T')
            ->orderBy('transaction_date', 'asc')
            ->get();

        return view('exports.form_transfer_excel', [
            'records' => $records,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
