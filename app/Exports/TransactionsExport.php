<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionsExport implements FromView
{
    protected $period;
    protected $type;

    public function __construct($period, $type)
    {
        $this->period = $period;
        $this->type = $type;
    }

    public function view(): View
    {
        $month = Carbon::parse($this->period)->month;
        $year = Carbon::parse($this->period)->year;
        $userId = auth()->id();

        $creditExpenses = [];
        $debitExpenses = [];

        if ($this->type === 'both') {
            $creditExpenses = Expense::with('reason')
                ->where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('type', 'credit')
                ->get();

            $debitExpenses = Expense::with('reason')
                ->where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('type', 'debit')
                ->get();
        } else {
            $singleTypeExpenses = Expense::with('reason')
                ->where('user_id', $userId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('type', $this->type)
                ->get();
        }

        return view('exports.excel', [
            'period' => $this->period,
            'type' => $this->type,
            'creditExpenses' => $creditExpenses,
            'debitExpenses' => $debitExpenses,
            'expenses' => $this->type !== 'both' ? $singleTypeExpenses : null,
        ]);
    }
}
