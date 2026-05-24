<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpendingAnomalyMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $monthLabel;

    public function __construct(
        public string $userName,
        public string $month,
        public float $monthTotal,
        public float $baselineAverage,
        public float $increasePercent,
        public string $currencySymbol,
    ) {
        $this->monthLabel = $this->formatMonthLabel($month);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Unusual spending — '.$this->monthLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.spending-anomaly',
        );
    }

    private function formatMonthLabel(string $month): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $month)->format('F Y');
        } catch (\Throwable) {
            return $month;
        }
    }
}
