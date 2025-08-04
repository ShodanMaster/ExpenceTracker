<?php

namespace App\Console\Commands;

use App\Models\ReportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-reports';

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
        $logs = ReportLog::where('email_sent', true)
            ->where('sent_at', '<=', now()->subDays(7))
            ->get();

        foreach ($logs as $log) {
            if ($log->pdf_path && Storage::exists($log->pdf_path)) {
                Storage::delete($log->pdf_path);
            }
            if ($log->excel_path && Storage::exists($log->excel_path)) {
                Storage::delete($log->excel_path);
            }

            $log->delete();
        }

        $this->info('Old report files cleaned up.');
    }

}
