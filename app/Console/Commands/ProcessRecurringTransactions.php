<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $recurrings = \App\Models\ReccuringTransaction::where('is_active', true)
            ->whereNotNull('next_occurence')
            ->where('next_occurence', '<=', $now)
            ->get();

        foreach ($recurrings as $recurring) {
            $next = $recurring->next_occurence;

            // Handle all missed occurrences (in case cron failed)
            while ($next->lte($now)) {
                // Insert missed occurrence as expense
                \App\Models\Expense::create([
                    'user_id' => $recurring->user_id,
                    'amount' => $recurring->amount,
                    'carry_forward' => 0,
                    'description' => $recurring->description,
                    'date' => $next->toDateString(),
                    'reason_id' => $recurring->reason_id,
                    'type' => $recurring->type,
                ]);

                // Set last_occurence = this one
                $recurring->last_occurence = $next;

                // Compute next occurrence
                $next = $this->getNextDate(
                    $recurring->frequency,
                    $recurring->frequency_value,
                    $recurring->day_of_week,
                    $recurring->day_of_month,
                    $recurring->month_of_year,
                    $next // pass base date as current next
                );
            }

            $recurring->next_occurence = $next;
            $recurring->save();
        }

        $this->info('Recurring transactions processed including any missed dates.');
    }

    private function getNextDate($frequency, $frequencyValue, $dayOfWeek = null, $dayOfMonth = null, $monthOfYear = null, $baseDate = null)
    {
        $base = $baseDate ? \Carbon\Carbon::parse($baseDate) : now();

        switch ($frequency) {
            case 'daily':
                return $base->copy()->addDays((int)($frequencyValue ?: 1))->startOfDay();

            case 'weekly':
                $targetDay = is_null($dayOfWeek) ? 0 : (int) $dayOfWeek;
                $daysUntil = ($targetDay - $base->dayOfWeek + 7) % 7;
                $daysUntil = $daysUntil === 0 ? 7 : $daysUntil;
                return $base->copy()->addDays($daysUntil)->startOfDay();

            case 'monthly':
                $targetDay = is_null($dayOfMonth) ? 1 : (int) $dayOfMonth;
                $nextMonth = $base->copy()->addMonthNoOverflow()->startOfMonth();
                $day = min($targetDay, $nextMonth->daysInMonth);
                return $nextMonth->day($day)->startOfDay();

            case 'yearly':
                $month = is_null($monthOfYear) ? 1 : (int) $monthOfYear;
                $day = is_null($dayOfMonth) ? 1 : (int) $dayOfMonth;

                $year = $base->month >= $month ? $base->year + 1 : $base->year;
                $maxDay = \Carbon\Carbon::create($year, $month)->daysInMonth;
                $day = min($day, $maxDay);

                return \Carbon\Carbon::create($year, $month, $day)->startOfDay();

            default:
                throw new \Exception('Invalid frequency type');
        }
    }

}
