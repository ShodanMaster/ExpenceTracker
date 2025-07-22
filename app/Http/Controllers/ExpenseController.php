<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Reason;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

            $reason = Reason::firstOrCreate(['name' => $request->reason]);

            $expense = Expense::create([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'carry_forward' => 0, // Temporary
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
            // dd($creditSum);
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

            // dd($data['balance']);

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

            $existing = Reason::where('name', $request->reason)->first();
            $reason = $existing ?: Reason::create(['name' => $request->reason]);

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
