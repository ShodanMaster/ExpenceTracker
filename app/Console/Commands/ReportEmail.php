<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ReportLog;
use App\Services\ReportGeneratorService;
use App\Mail\MonthlyReportMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ReportEmail extends Command
{
    protected $signature = 'app:report-email';
    protected $description = 'Send monthly expense reports to all users via email (type=both)';

    public function handle(ReportGeneratorService $reportGenerator)
    {
        $period = now()->subMonth()->format('Y-m');
        $display = now()->subMonth()->format('F Y');

        $users = User::all();

        foreach ($users as $user) {
            $userId = $user->id;
            $email = $user->email;
            $username = $user->name;

            $pdfPath = $reportGenerator->generateBoth($period, $username, $userId, 'pdf');
            $excelPath = $reportGenerator->generateBoth($period, $username, $userId, 'xlsx');

            if ($pdfPath && $excelPath) {

                $reportLog = ReportLog::create([
                    'user_id' => $userId,
                    'period' => $period,
                    'pdf_path' => $pdfPath,
                    'excel_path' => $excelPath,
                    'email_sent' => false,
                ]);

                Mail::to($email)->send(new MonthlyReportMail(
                    $user, $display, $pdfPath, $excelPath
                ));

                $reportLog->update([
                    'email_sent' => true,
                    'sent_at' => now(),
                ]);

                $this->info("✔ Report sent to {$email}");
            } else {
                $this->warn("⚠ No transactions for {$email} in {$period}");
            }
        }

        $this->info("✅ All reports dispatched.");
    }
}
