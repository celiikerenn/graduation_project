<?php

namespace App\Console\Commands;

use App\Mail\SpendingAnomalyMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MailPreview extends Command
{
    protected $signature = 'fintrack:mail-preview {--open : Open the HTML file in the default browser (Windows)}';

    protected $description = 'Render the spending anomaly email to storage/app/mail-preview.html (no SMTP needed)';

    public function handle(): int
    {
        $mailable = new SpendingAnomalyMail(
            userName: 'Demo User',
            month: now()->format('Y-m'),
            monthTotal: 4250.75,
            baselineAverage: 2100.00,
            increasePercent: 102.4,
            currencySymbol: '₺',
        );

        $html = $mailable->render();
        $dir = storage_path('app/mail-preview');
        File::ensureDirectoryExists($dir);
        $path = $dir.DIRECTORY_SEPARATOR.'spending-anomaly.html';
        File::put($path, $html);

        $this->info('Preview saved: '.$path);

        if ($this->option('open') && PHP_OS_FAMILY === 'Windows') {
            exec('start "" '.escapeshellarg($path));
        }

        return self::SUCCESS;
    }
}
