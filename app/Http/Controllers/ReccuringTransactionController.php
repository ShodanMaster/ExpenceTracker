<?php

namespace App\Http\Controllers;

use App\Models\Reason;
use App\Models\ReccuringTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReccuringTransactionController extends Controller
{
    public function index(){
        $reasons = Reason::all();
        return view('reccuringTransactions', compact('reasons'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'type' => ['required', Rule::in(['credit', 'debit'])],
                'reason' => 'required|string',
                'amount' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
                'frequency_value' => 'nullable|string',
            ]);

            $reason = Reason::firstOrCreate(['name' => $request->reason]);

            $frequencyFields = $this->extractFrequencyFields($request->frequency, $request->frequency_value);

            $transaction = array_merge([
                'user_id' => auth()->id(),
                'type' => $request->type,
                'reason_id' => $reason->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'frequency' => $request->frequency,
                'frequency_value' => $request->frequency_value,
                'next_occurence' => $this->getNextDate($request->frequency, $request->frequency_value),
            ], $frequencyFields);


            ReccuringTransaction::create($transaction);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Recurring transaction created successfully.',
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

    public function getTransactions(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            $userId = auth()->id();

            $query = ReccuringTransaction::with('reason')
                ->where('user_id', $userId)
                ->orderBy('next_occurence', 'asc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%$search%")
                    ->orWhere('frequency', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhereHas('reason', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%$search%");
                    });
                });
            }

            $transactions = $query->paginate($perPage);

            return response()->json([
                'status' => 200,
                'data' => $transactions->items(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]);

        } catch (Exception $e) {
            Log::error("Error fetching transactions: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching transactions'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = ReccuringTransaction::findOrFail($id);
            $transaction->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Recurring transaction deleted successfully.',
            ], 200);

        } catch (Exception $e) {
            Log::error("Error deleting transaction: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Error deleting transaction'
            ], 500);
        }
    }

    private function extractFrequencyFields(string $frequency, ?string $frequencyValue): array
    {
        $dayOfWeek = null;
        $dayOfMonth = null;
        $monthOfYear = null;

        switch ($frequency) {
            case 'weekly':
                $dayOfWeek = strtolower($frequencyValue);
                break;

            case 'monthly':
                $dayOfMonth = (int) $frequencyValue;
                break;

            case 'yearly':
                [$month, $day] = explode('-', $frequencyValue);
                $dayOfMonth = (int) $day;
                $monthOfYear = (int) $month;
                break;

            // daily does not set additional fields
        }

        return [
            'day_of_week' => $dayOfWeek,
            'day_of_month' => $dayOfMonth,
            'month_of_year' => $monthOfYear,
        ];
    }

    private function getNextDate($frequency, $frequencyValue)
    {
        $date = now();

        switch ($frequency) {
            case 'daily':
                return $date->addDay();

            case 'weekly':
                $targetDay = (int) $frequencyValue;
                $daysUntil = ($targetDay - $date->dayOfWeek + 7) % 7;
                $daysUntil = $daysUntil === 0 ? 7 : $daysUntil;
                return $date->addDays($daysUntil);

            case 'monthly':
                $targetDay = (int) $frequencyValue;
                $nextMonth = $date->copy()->addMonthNoOverflow()->startOfMonth();
                $day = min($targetDay, $nextMonth->daysInMonth);
                return $nextMonth->day($day);

            case 'yearly':
                $targetMonth = (int) $frequencyValue;
                $year = $date->month >= $targetMonth ? $date->year + 1 : $date->year;
                return Carbon::create($year, $targetMonth, 1);

            default:
                throw new Exception('Invalid frequency type');
        }
    }

}
