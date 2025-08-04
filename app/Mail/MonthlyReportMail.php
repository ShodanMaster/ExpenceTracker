<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $month;

    protected $pdfPath;
    protected $excelPath;

    /**
     * Create a new message instance.
     */
    public function __construct($user, string $month, string $pdfPath, string $excelPath)
    {
        $this->user = $user;
        $this->month = $month;
        $this->pdfPath = $pdfPath;
        $this->excelPath = $excelPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Monthly Report - ' . $this->month,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly_report', // Create this Blade file
            with: [
                'user' => $this->user,
                'month' => $this->month,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(Storage::path($this->pdfPath))->as('Monthly_Expense_Report.pdf')->withMime('application/pdf'),
            Attachment::fromPath(Storage::path($this->excelPath))->as('Monthly_Expense_Report.xlsx')->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
