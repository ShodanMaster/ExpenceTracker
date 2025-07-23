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

use function PHPSTORM_META\type;

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
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
                'frequency_value' => 'nullable|string',
            ]);

            $reason = Reason::firstOrCreate(['name' => $request->reason]);

            $frequencyFields = $this->extractFrequencyFields($request->frequency, $request->frequency_value);

            //dd($request->all());
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

    public function show($id)
    {
        try {
            $transaction = ReccuringTransaction::with('reason')->findOrFail($id);
            // dd($transaction);
            return response()->json([
                'status' => 200,
                'data' => $transaction,
            ]);
        } catch (Exception $e) {
            Log::error("Error fetching transaction: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
{
            // dd($request->all());
    $request->validate([
        'type' => 'required|in:credit,debit',
        'reason' => 'required|string',
        'amount' => 'required|numeric',
        'frequency' => 'required|in:daily,weekly,monthly,yearly',
        'description' => 'nullable|string',
        'frequency_value' => 'nullable|integer',
    ]);

    $transaction = ReccuringTransaction::findOrFail($id);

    $transaction->type = $request->type;
    $transaction->amount = $request->amount;
    $transaction->description = $request->description;
    $transaction->frequency = $request->frequency;
    $transaction->frequency_value = $request->frequencyValue;

    // Reset all frequency fields
    $transaction->day_of_week = null;
    $transaction->day_of_month = null;
    $transaction->month_of_year = null;

    // Set based on frequency
    switch ($request->frequency) {
        case 'weekly':
            $transaction->day_of_week = $request->frequencyValue;
            break;
        case 'monthly':
            $transaction->day_of_month = $request->frequencyValue;
            break;
        case 'yearly':
            $transaction->month_of_year = $request->frequencyValue;
            break;
        // 'daily' requires no extra value
    }

    // Lookup reason
    $reason = Reason::where('name', $request->reason)->first();
    if (!$reason) {
        return response()->json(['message' => 'Reason not found'], 404);
    }

    $transaction->reason_id = $reason->id;
    $transaction->next_occurence = $this->getNextDate($request->frequency, $request->frequencyValue);
    $transaction->save();

    return response()->json(['status' => 200, 'message' => 'Transaction updated successfully']);
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
            $dayOfWeek = is_numeric($frequencyValue) ? (int)$frequencyValue : null;
            break;

        case 'monthly':
            $dayOfMonth = is_numeric($frequencyValue) ? (int)$frequencyValue : null;
            break;

        case 'yearly':
            $monthOfYear = is_numeric($frequencyValue) ? (int)$frequencyValue : null;
            break;

        // daily doesn't need additional fields
    }

    return [
        'day_of_week' => $dayOfWeek,
        'day_of_month' => $dayOfMonth,
        'month_of_year' => $monthOfYear,
    ];
}
/////////////////////////////////////////////////////////////////
//////////////BUG IN NEXT OCCURENCE DATE/////////////////////////
/////////////////////////////////////////////////////////////////

private function getNextDate($frequency, $frequencyValue)
{
    $today = now();

    switch ($frequency) {
        case 'daily':
            return $today->addDay()->startOfDay();

        case 'weekly':
            $targetDay = (int) $frequencyValue;
            $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
            $daysUntil = $daysUntil === 0 ? 7 : $daysUntil;
            return $today->addDays($daysUntil)->startOfDay();

        case 'monthly':
            $targetDay = (int) $frequencyValue;

            // Try to return the date this month, if it's still upcoming
            $thisMonthTarget = $today->copy()->day(min($targetDay, $today->daysInMonth));
            if ($thisMonthTarget->isFuture()) {
                return $thisMonthTarget->startOfDay();
            }

            // Otherwise, go to next month
            $nextMonth = $today->copy()->addMonthNoOverflow()->startOfMonth();
            $day = min($targetDay, $nextMonth->daysInMonth);
            return $nextMonth->day($day)->startOfDay();

        case 'yearly':
            $targetMonth = (int) $frequencyValue;
            $targetDay = 1;

            $year = $today->month >= $targetMonth ? $today->year + 1 : $today->year;
            $day = min($targetDay, Carbon::create($year, $targetMonth)->daysInMonth);
            return Carbon::create($year, $targetMonth, $day)->startOfDay();

        default:
            throw new Exception('Invalid frequency type');
    }
}

}
