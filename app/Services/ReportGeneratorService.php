<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ReportLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Carbon\Carbon;

class ReportGeneratorService
{
    public function generateBoth(string $period, string $username, int $userId, string $format): ?string
    {
        $isMonthly = preg_match('/^\d{4}-\d{2}$/', $period);
        $date = $isMonthly
            ? Carbon::createFromFormat('Y-m', $period)
            : Carbon::createFromFormat('Y', $period);

        $filenamePeriod = $isMonthly ? $date->format('Y-m') : $date->format('Y');
        $filename = "both_report_{$filenamePeriod}_{$username}.{$format}";
        $path = "reports/{$filename}";

        if ($format === 'xlsx') {

            $expenses = Expense::where('user_id', $userId)
                ->whereYear('date', $date->year)
                ->when($isMonthly, fn($q) => $q->whereMonth('date', $date->month))
                ->get();

            if ($expenses->isEmpty()) {
                return null;
            }

            Excel::store(new TransactionsExport($period, 'both', $userId), $path, 'local');
            return $path;
        }

        $creditExpenses = Expense::with('reason')
            ->where('user_id', $userId)
            ->where('type', 'credit')
            ->whereYear('date', $date->year)
            ->when($isMonthly, fn($q) => $q->whereMonth('date', $date->month))
            ->get();

        $debitExpenses = Expense::with('reason')
            ->where('user_id', $userId)
            ->where('type', 'debit')
            ->whereYear('date', $date->year)
            ->when($isMonthly, fn($q) => $q->whereMonth('date', $date->month))
            ->get();

        if ($creditExpenses->isEmpty() && $debitExpenses->isEmpty()) {
            return null;
        }

        $pdf = Pdf::loadView('exports.pdf', [
            'period' => $filenamePeriod,
            'type' => 'both',
            'creditExpenses' => $creditExpenses,
            'debitExpenses' => $debitExpenses,
            'expenses' => collect(),
            'isMonthly' => $isMonthly,
        ]);

        Storage::put($path, $pdf->output());
        return $path;
    }

    public function generateAndLog(string $period, string $username, int $userId): ?ReportLog
    {
        $pdfPath = $this->generateBoth($period, $username, $userId, 'pdf');
        $excelPath = $this->generateBoth($period, $username, $userId, 'xlsx');

        if (!$pdfPath && !$excelPath) {
            return null;
        }

        return ReportLog::create([
            'user_id' => $userId,
            'period' => $period,
            'pdf_path' => $pdfPath,
            'excel_path' => $excelPath,
            'email_sent' => false,
        ]);
    }

}
