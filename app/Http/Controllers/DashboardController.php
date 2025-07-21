<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard');
    }

public function chartData(Request $request)
{
    $user = auth()->user();
    $type = $request->get('type', 'daily');

    $expenses = Expense::with('reason')
        ->where('user_id', $user->id)
        ->when($type === 'daily', fn($q) => $q->whereDate('date', today()))
        ->when($type === 'monthly', fn($q) => $q->whereMonth('date', today()->month)->whereYear('date', today()->year))
        ->when($type === 'yearly', fn($q) => $q->whereYear('date', today()->year))
        ->get();

    $groupKey = match ($type) {
        'daily' => 'g A',   // readable hour: 1 AM, 2 PM
        'monthly' => 'd',
        'yearly' => 'M',
    };

    $labels = match ($type) {
        'daily' => collect(range(0, 23))->map(fn($hour) => Carbon::createFromTime($hour)->format('g A')),
        'monthly' => collect(range(1, now()->daysInMonth))->map(fn($day) => str_pad($day, 2, '0', STR_PAD_LEFT)),
        'yearly' => collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('F')),
    };

    $grouped = $expenses->groupBy(fn($e) => Carbon::parse($e->date)->format($groupKey));

    $credits = $labels->map(fn($label) => (float) ($grouped->get($label)?->where('type', 'credit')->sum('amount') ?? 0));
    $debits  = $labels->map(fn($label) => (float) ($grouped->get($label)?->where('type', 'debit')->sum('amount') ?? 0));

    // Category-wise breakdown
    $categoryData = $expenses->where('type', 'debit')
        ->groupBy(fn($e) => $e->reason->name ?? 'Other')
        ->map(fn($group) => $group->sum('amount'));

    $categoryChart = [
        'labels' => $categoryData->keys()->values(),
        'datasets' => [[
            'label' => 'Expenses by Category',
            'data' => $categoryData->values(),
            'backgroundColor' => collect($categoryData->keys())->map(fn() => 'rgba('.rand(0,255).','.rand(0,255).','.rand(0,255).',0.6)')
        ]]
    ];

    $totalIncome = $expenses->where('type', 'credit')->sum('amount');
    $totalExpense = $expenses->where('type', 'debit')->sum('amount');
    $savings = $totalIncome - $totalExpense;

    $topCategoryLabels = $categoryData->sortDesc()->keys()->take(5)->values();
    $topCategoryValues = $categoryData->sortDesc()->values()->take(5)->map(fn($v) => floatval($v))->values();

    $topCategories = [
        'labels' => $topCategoryLabels,
        'datasets' => [[
            'label' => 'Top Spending Categories',
            'data' => $topCategoryValues,
            'backgroundColor' => ['#f44336', '#e91e63', '#9c27b0', '#3f51b5', '#2196f3']
        ]]
    ];

    /////////////////////////////////////////////////////////
//     $topCategoryLabels = $categoryData->sortDesc()->keys()->take(5)->values();
        // $topCategoryValues = $categoryData->sortDesc()->values()->take(5)->map(fn($v) => floatval($v))->values();

        // $topCategories = [
        //     'labels' => $topCategoryLabels,
        //     'datasets' => [
        //         'label' => 'Top Spending Categories',
        //         'data' => $topCategoryValues,
        //         'backgroundColor' => ['#f44336', '#e91e63', '#9c27b0', '#3f51b5', '#2196f3']
        //     ]
// ];
    /////////////////////////////////////////////////////////


    // Prepare Income vs Expense ratio pie chart data
    $incomeVsExpense = [
        'labels' => ['Income', 'Expense'],
        'datasets' => [[
            'data' => [(float) $totalIncome, (float) $totalExpense],
            'backgroundColor' => [
                'rgba(75, 192, 192, 0.6)',
                'rgba(255, 99, 132, 0.6)'
            ]
        ]]
    ];

    $averages = [
        'daily' => Expense::where('user_id', $user->id)->where('type', 'debit')->whereDate('date', '>=', now()->subDays(30))->avg('amount') ?? 0,
        'monthly' => Expense::where('user_id', $user->id)->where('type', 'debit')->whereDate('date', '>=', now()->subMonths(1))->avg('amount') ?? 0,
        'yearly' => Expense::where('user_id', $user->id)->where('type', 'debit')->whereDate('date', '>=', now()->subYears(1))->avg('amount') ?? 0,
    ];

    return response()->json([
        'total_income' => $totalIncome,
        'total_expense' => $totalExpense,
        'savings' => $savings,
        'labels' => $labels,
        'credits' => $credits->map(fn($v) => floatval($v)),
        'debits' => $debits->map(fn($v) => floatval($v)),
        'categories' => $categoryChart,
        'summary' => [
            'total_expense' => $totalExpense,
            'total_income' => $totalIncome,
            'savings' => $savings
        ],
        'topCategories' => $topCategories,
        'incomeVsExpense' => $incomeVsExpense,
        'averages' => $averages,
    ]);
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
        //
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
