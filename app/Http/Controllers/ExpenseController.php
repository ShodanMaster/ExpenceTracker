<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Expense;
use App\Models\Reason;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reasons = Reason::all();
        return view('expenses', compact('reasons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'date' => 'required|date',
                'type' => ['required', Rule::in(['credit', 'debit'])],
                'reason' => 'required|string',
                'amount' => 'required|integer|min:0',
                'description' => 'nullable|string',
            ]);

            $reason = Reason::firstOrCreate([
                    'name' => $request->reason,
                    'user_id' => auth()->id(),
                ]);

            $expense = Expense::create([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'carry_forward' => 0,
            ]);

            $expenses = Expense::where('user_id', auth()->id())
                ->where(function ($query) use ($expense) {
                    $query->where('date', '>', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '>=', $expense->id);
                        });
                })
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $previousExpense = Expense::where('user_id', auth()->id())
                ->where(function ($query) use ($expense) {
                    $query->where('date', '<', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '<', $expense->id);
                        });
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $carryForward = $previousExpense ? $previousExpense->carry_forward : 0;

            $allToUpdate = collect([$expense])->merge($expenses);

            foreach ($allToUpdate as $exp) {
                $carryForward += ($exp->type === 'credit') ? $exp->amount : -$exp->amount;
                $exp->update(['carry_forward' => $carryForward]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Stored Successfully',
                'data' => $expense
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Storing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Storing failed. Please try again later.',
            ], 500);
        }
    }

    public function getReasons(Request $request)
    {
        $userId = auth()->id();

        $reasons = Reason::where('user_id', $userId)->select('id', 'name')
                        ->where('name', 'like', '%'.$request->input('query').'%')
                        ->latest()
                        ->take(5)
                        ->get();

        return response()->json([
            'status' => 200,
            'data' => $reasons
        ]);
    }

    public function getExpenses($date)
    {
        try {
            $userId = auth()->id();
            $formattedDate = date('d-m-Y', strtotime($date));

            $expenses = Expense::where('user_id', $userId)
                ->whereDate('date', $date)
                ->with('reason')
                ->get();

            $debitExpenses = $expenses->where('type', 'debit')->values()->map(function ($exp) {
                return $this->transformExpense($exp);
            });

            $creditExpenses = $expenses->where('type', 'credit')->values()->map(function ($exp) {
                return $this->transformExpense($exp);
            });

            $creditSum = Expense::where('user_id', $userId)
                ->whereDate('date', '<=', $date)
                ->where('type', 'credit')
                ->sum('amount');

            $debitSum = Expense::where('user_id', $userId)
                ->whereDate('date', '<=', $date)
                ->where('type', 'debit')
                ->sum('amount');

            $balance = $creditSum - $debitSum;

            $creditBefore = Expense::where('user_id', $userId)
                ->whereDate('date', '<', $date)
                ->where('type', 'credit')
                ->sum('amount');

            $debitBefore = Expense::where('user_id', $userId)
                ->whereDate('date', '<', $date)
                ->where('type', 'debit')
                ->sum('amount');

            $carryForwardBalance = $creditBefore - $debitBefore;

            $data = [
                'debit' => $debitExpenses,
                'credit' => $creditExpenses,
                'balance' => $this->formatIndianNumber($balance),
                'carry_forward' => $this->formatIndianNumber($carryForwardBalance),
            ];

            return response()->json([
                'status' => 200,
                'date' => $formattedDate,
                'debit_count' => $debitExpenses->count(),
                'credit_count' => $creditExpenses->count(),
                'total_transactions' => $debitExpenses->count() + $creditExpenses->count(),
                'data' => $data,
            ]);

        } catch (Exception $e) {
            Log::error('Expense fetch failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving expenses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMonthlySummary($month)
    {
        try {
            $userId = auth()->id();
            $formattedMonth = date('Y-m', strtotime($month));

            $expenses = Expense::where('user_id', $userId)
                ->whereMonth('date', date('m', strtotime($month)))
                ->whereYear('date', date('Y', strtotime($month)))
                ->with('reason')
                ->orderBy('date')
                ->get();

            $groupedByDate = $expenses->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

            $grouped = [];
            $dailyBalance = 0;
            foreach ($groupedByDate as $date => $items) {
                $creditList = [];
                $debitList = [];
                $dailyCredit = 0;
                $dailyDebit = 0;

                foreach ($items as $txn) {
                    $formattedTxn = [
                        'reason' => optional($txn->reason)->name,
                        'amount' => (float) $txn->amount
                    ];

                    if ($txn->type === 'credit') {
                        $creditList[] = $formattedTxn;
                        $dailyCredit += $txn->amount;
                    } else {
                        $debitList[] = $formattedTxn;
                        $dailyDebit += $txn->amount;
                    }
                }

                $dailyBalance += $dailyCredit - $dailyDebit;

                $grouped[] = [
                    'date_formatted' => date('d M Y', strtotime($date)),
                    'credit' => $creditList,
                    'debit' => $debitList,
                    'balance' => $dailyBalance
                ];
            }

            $creditSum = $expenses->where('type', 'credit')->sum('amount');
            $debitSum = $expenses->where('type', 'debit')->sum('amount');

            $carryForward = Expense::where('user_id', $userId)
                ->whereDate('date', '<', date('Y-m-01', strtotime($month)))
                ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
                ->value('balance') ?? 0;

            $finalBalance = $carryForward + $creditSum - $debitSum;

            $grouped = array_reverse($grouped);

            return response()->json([
                'status' => 200,
                'month' => $formattedMonth,
                'debit_count' => $expenses->where('type', 'debit')->count(),
                'credit_count' => $expenses->where('type', 'credit')->count(),
                'total_transactions' => $expenses->count(),
                'data' => [
                    'total_credit' => round($creditSum, 2),
                    'total_debit' => round($debitSum, 2),
                    'carry_forward' => round($carryForward, 2),
                    'balance' => round($finalBalance, 2),
                    'grouped_by_date' => $grouped
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Monthly expenses fetch failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving monthly expenses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getYearlySummary($year)
    {
        try {
            $userId = auth()->id();
            $formattedYear = (int) $year;

            $expenses = Expense::where('user_id', $userId)
                ->whereYear('date', $formattedYear)
                ->with('reason')
                ->orderBy('date')
                ->get();

            $groupedByMonth = $expenses->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m');
            });

            $grouped = [];
            foreach ($groupedByMonth as $month => $items) {
                $monthlyCredit = [];
                $monthlyDebit = [];
                $totalCredit = 0;
                $totalDebit = 0;

                foreach ($items as $txn) {
                    $reason = optional($txn->reason)->name ?? 'Unknown';
                    $amount = (float) $txn->amount;

                    if ($txn->type === 'credit') {
                        $monthlyCredit[$reason] = ($monthlyCredit[$reason] ?? 0) + $amount;
                        $totalCredit += $amount;
                    } else {
                        $monthlyDebit[$reason] = ($monthlyDebit[$reason] ?? 0) + $amount;
                        $totalDebit += $amount;
                    }
                }

                $grouped[] = [
                    'month' => date('F', strtotime($month)),
                    'credit' => collect($monthlyCredit)->map(fn($amount, $reason) => [
                        'reason' => $reason,
                        'amount' => round($amount, 2),
                    ])->values(),

                    'debit' => collect($monthlyDebit)->map(fn($amount, $reason) => [
                        'reason' => $reason,
                        'amount' => round($amount, 2),
                    ])->values(),

                    'total_credit' => round($totalCredit, 2),
                    'total_debit' => round($totalDebit, 2),
                    'balance' => round($totalCredit - $totalDebit, 2)
                ];
            }

            return response()->json([
                'status' => 200,
                'year' => $formattedYear,
                'data' => [
                    'grouped_by_month' => array_reverse($grouped)
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error('Yearly expenses fetch failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving yearly expenses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'date' => 'required|string',
                'type' => ['required', Rule::in(['credit', 'debit'])],
                'reason' => 'required|string',
                'amount' => 'required|integer|min:0',
                'description' => 'nullable|string',
            ]);

            $existing = Reason::where('name', $request->reason)
                            ->where('user_id', auth()->id())
                            ->first();

            $reason = $existing ?: Reason::create([
                'name' => $request->reason,
                'user_id' => auth()->id(),
            ]);

            $expense = Expense::where('id', $id)->first();

            if (!$expense) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Expense not found.',
                ], 404);
            }

            $previousExpense = Expense::where('user_id', auth()->id())
                ->where(function ($query) use ($expense) {
                    $query->where('date', '<', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '<', $expense->id);
                        });
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $carryForwardBalance = $previousExpense ? $previousExpense->carry_forward : 0;

            if ($request->type === 'credit') {
                $newBalance = $carryForwardBalance + $request->amount;
            } else {
                $newBalance = $carryForwardBalance - $request->amount;
            }

            $expense->update([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'carry_forward' => $newBalance,
            ]);

            $futureExpenses = Expense::where('user_id', auth()->id())
                ->where(function ($query) use ($expense) {
                    $query->where('date', '>', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '>', $expense->id);
                        });
                })
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $runningBalance = $newBalance;

            foreach ($futureExpenses as $future) {
                if ($future->type === 'credit') {
                    $runningBalance += $future->amount;
                } else {
                    $runningBalance -= $future->amount;
                }

                $future->update([
                    'carry_forward' => $runningBalance,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Updated Successfully',
                'data' => $expense,
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Updating failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Updating failed. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::findOrFail($id);
            $userId = $expense->user_id;

            $previousExpense = Expense::where('user_id', $userId)
                ->where(function ($query) use ($expense) {
                    $query->where('date', '<', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '<', $expense->id);
                        });
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $carryForwardBalance = $previousExpense ? $previousExpense->carry_forward : 0;

            $expense->delete();

            $futureExpenses = Expense::where('user_id', $userId)
                ->where(function ($query) use ($expense) {
                    $query->where('date', '>', $expense->date)
                        ->orWhere(function ($q) use ($expense) {
                            $q->where('date', $expense->date)
                                ->where('id', '>', $expense->id);
                        });
                })
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            foreach ($futureExpenses as $future) {
                if ($future->type === 'credit') {
                    $carryForwardBalance += $future->amount;
                } else {
                    $carryForwardBalance -= $future->amount;
                }

                $future->update([
                    'carry_forward' => $carryForwardBalance,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Expense deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'status' => 404,
                'message' => 'Expense not found',
            ], 404);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete expense: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete expense',
            ], 500);
        }
    }

    public function generateReport(Request $request)
    {
        try {

            $request->validate([
                'period' => ['required', 'regex:/^\d{4}(-\d{2})?$/'],
                'transaction_type' => ['required', Rule::in(['credit', 'debit', 'both'])],
                'format' => ['required', Rule::in(['pdf', 'xlsx'])],
            ]);

            $inputPeriod = $request->period;
            $type = $request->transaction_type;
            $format = $request->format;

            $isMonthly = preg_match('/^\d{4}-\d{2}$/', $inputPeriod);

            if ($isMonthly) {
                $date = Carbon::createFromFormat('Y-m', $inputPeriod);
                $displayPeriod = $date->format('F Y');
                $filenamePeriod = $date->format('Y-m');
            } else {
                $date = Carbon::createFromFormat('Y', $inputPeriod);
                $displayPeriod = $date->format('Y');
                $filenamePeriod = $displayPeriod;
            }

            $userId = auth()->id();

            $query = Expense::with('reason')->where('user_id', $userId);

            if ($isMonthly) {
                $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
            } else {
                $query->whereYear('date', $date->year);
            }

            if ($type !== 'both') {
                $query->where('type', $type);
            }

            $expenses = $query->get();

            if ($format === 'pdf') {
                $fileName = "{$type}_report_{$filenamePeriod}.pdf";

                $creditExpenses = collect();
                $debitExpenses = collect();
                $singleExpenses = collect();

                if ($type === 'both') {

                    $creditQuery = Expense::with('reason')->where('user_id', $userId)->where('type', 'credit');
                    $debitQuery = Expense::with('reason')->where('user_id', $userId)->where('type', 'debit');

                    if ($isMonthly) {
                        $creditQuery->whereYear('date', $date->year)->whereMonth('date', $date->month);
                        $debitQuery->whereYear('date', $date->year)->whereMonth('date', $date->month);
                    } else {
                        $creditQuery->whereYear('date', $date->year);
                        $debitQuery->whereYear('date', $date->year);
                    }

                    $creditExpenses = $creditQuery->get();
                    $debitExpenses = $debitQuery->get();
                } else {

                    $singleExpenses = $expenses;
                }

                $pdf = Pdf::loadView('exports.pdf', [
                    'period' => $displayPeriod,
                    'type' => $type,
                    'creditExpenses' => $creditExpenses,
                    'debitExpenses' => $debitExpenses,
                    'expenses' => $singleExpenses,
                    'isMonthly' => $isMonthly,
                ]);

                return $pdf->download($fileName);
            }

            $fileName = "{$type}_report_{$filenamePeriod}.xlsx";

            return Excel::download(
            new TransactionsExport($inputPeriod, $type, $userId),
            $fileName,
            ExcelFormat::XLSX
            );


        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Report generation failed. Please try again later.',
            ], 500);
        }
    }

    private function transformExpense($expense)
    {
        return [
            'id' => $expense->id,
            'reason' => $expense->reason->name,
            'amount' => $expense->amount,
            'type' => $expense->type,
            'date' => $expense->date,
            'description' => $expense->description,
        ];
    }

    private function formatIndianNumber($num) {
        $num = (string)$num;
        $decimal = '';
        if (strpos($num, '.') !== false) {
            [$num, $decimal] = explode('.', $num);
            $decimal = '.' . $decimal;
        }

        $last3 = substr($num, -3);
        $restUnits = substr($num, 0, -3);
        if ($restUnits != '') {
            $last3 = ',' . $last3;
        }
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        return $restUnits . $last3 . $decimal;
    }
}
