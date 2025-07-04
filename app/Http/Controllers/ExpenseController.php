<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Reason;
use Carbon\Carbon;
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

            // $date = Carbon::parse($request->date)->setTimezone('America/New_York');

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
            dd($e);
            Log::error('Registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Registration failed. Please try again later.',
                // 'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function getExpenses($date){
        // dd($date);
        try{

            $debitExpenses = Expense::whereDate('date', $date)->where('type', 'debit')->get()->map(function($de){
                return [
                    'id' => $de->id,
                    'reason' => $de->reason->name,
                    'amount' => $de->amount,
                ];
            });
            $creditExpenses = Expense::whereDate('date', $date)->where('type', 'credit')->get()->map(function($ce){
                return [
                    'id' => $ce->id,
                    'reason' => $ce->reason->name,
                    'amount' => $ce->amount,
                ];
            });

            $data = [
                'debit' => $debitExpenses,
                'credit' => $creditExpenses,
            ];

            return response()->json([
                'status' => 200,
                'date' => date('d-m-Y', strtotime($date)),
                // 'date' => $date,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
