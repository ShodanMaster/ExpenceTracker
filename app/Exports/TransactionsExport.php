<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionsExport implements FromView
{
    protected $period, $type, $userId;

    public function __construct($period, $type, $userId)
    {
        $this->period = $period;
        $this->type = $type;
        $this->userId = $userId;
    }

    public function view(): View
    {
        $date = strlen($this->period) === 7
            ? Carbon::createFromFormat('Y-m', $this->period)
            : Carbon::createFromFormat('Y', $this->period);

        $baseQuery = Expense::with('reason')
            ->where('user_id', $this->userId)
            ->whereYear('date', $date->year);

        if (strlen($this->period) === 7) {
            $baseQuery->whereMonth('date', $date->month);
        }

        if ($this->type === 'both') {
            $creditExpenses = (clone $baseQuery)->where('type', 'credit')->get();
            $debitExpenses = (clone $baseQuery)->where('type', 'debit')->get();

            return view('exports.excel', [
                'type' => $this->type,
                'period' => $this->period,
                'creditExpenses' => $creditExpenses,
                'debitExpenses' => $debitExpenses,
                'expenses' => collect(), // avoid error in blade
            ]);
        }

        $expenses = (clone $baseQuery)->where('type', $this->type)->get();

        return view('exports.excel', [
            'type' => $this->type,
            'period' => $this->period,
            'expenses' => $expenses,
            'creditExpenses' => collect(),
            'debitExpenses' => collect(),
        ]);
    }
}
