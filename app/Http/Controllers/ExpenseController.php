<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Reason;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
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
        return view('expenses.index', compact('reasons'));
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
        // dd($request->all());
        try{
            $request->validate([
                'date' => 'required|string',
                'type' => ['required', Rule::in(['credit', 'debit'])],
                'reason' => 'required|string',
                'amount' => 'required|integer|min:0',
                'description' => 'nullable|string',
            ]);

            $existing = Reason::where('name', $request->reason)->first();
            if (!$existing) {
                $reason = Reason::create(['name' => $request->reason]);
            } else {
                $reason = $existing;
            }

            $expense  = Expense::create([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            return response()->json([
                'status' => 200,
                'messsage' => 'Stored Successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            // dd($e);
            Log::error('Storing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Storing failed. Please try again later.',
                // 'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function getExpenses($date){
        // dd($date);
        try{

            $debitExpenses = Expense::where('user_id', auth()->id())
                                ->whereDate('date', $date)
                                ->where('type', 'debit')
                                ->get()->map(function($de){
                                    return [
                                        'id' => $de->id,
                                        'reason' => $de->reason->name,
                                        'amount' => $de->amount,
                                        'type' => $de->type,
                                        'date' => $de->date,
                                        'description' => $de->description,
                                    ];
                                });

            $creditExpenses = Expense::where('user_id', auth()->id())
                                ->whereDate('date', $date)
                                ->where('type', 'credit')
                                ->get()->map(function($ce){
                                    return [
                                        'id' => $ce->id,
                                        'reason' => $ce->reason->name,
                                        'amount' => $ce->amount,
                                        'type' => $ce->type,
                                        'date' => $ce->date,
                                        'description' => $ce->description,
                                    ];
                                });

            $credit = Expense::where('user_id', auth()->id())
                                ->whereDate('date', '<=',$date)
                                ->where('type', 'credit')->get();

            $debit = Expense::where('user_id', auth()->id())
                                ->whereDate('date', '<=',$date)
                                ->where('type', 'debit')->get();

            $credit_sum = $credit->sum('amount');
            $debit_sum = $debit->sum('amount');

            $balance = $this->formatIndianNumber($credit_sum - $debit_sum);

            $data = [
                'debit' => $debitExpenses,
                'credit' => $creditExpenses,
                'balance' => $balance,
            ];

            return response()->json([
                'status' => 200,
                'date' => date('d-m-Y', strtotime($date)),
                'debit_count' => $debitExpenses->count(),
                'credit_count' => $creditExpenses->count(),
                'data' => $data,
            ]);

        }catch(Exception $e){
            dd($e);
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
        // dd($request->all());
        try{
            $request->validate([
                'id' => 'required',
                'date' => 'required|string',
                'type' => ['required', Rule::in(['credit', 'debit'])],
                'reason' => 'required|string',
                'amount' => 'required|integer|min:0',
                'description' => 'nullable|string',
            ]);

            $existing = Reason::where('name', $request->reason)->first();
            if (!$existing) {
                $reason = Reason::create(['name' => $request->reason]);
            } else {
                $reason = $existing;
            }

            $expense  = Expense::whereId($request->id)->update([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            return response()->json([
                'status' => 200,
                'messsage' => 'Stored Successfully'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            // dd($e);
            Log::error('Updating failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Updating failed. Please try again later.',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Expense deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete expense',
            ]);
        }
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
