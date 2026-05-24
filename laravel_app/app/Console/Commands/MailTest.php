<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
    protected $signature = 'fintrack:mail-test {email : Recipient address}';

    protected $description = 'Send a simple test email to verify SMTP / MAIL_* settings';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $mailer = config('mail.default');

        $this->line('Mail driver: <info>'.$mailer.'</info>');
        $this->line('From: '.config('mail.from.address').' ('.config('mail.from.name').')');

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER=log — no real email is sent. Set Resend SMTP in .env (see EMAIL_SETUP.md).');
        }

        if ($mailer === 'smtp' && ! config('mail.mailers.smtp.password')) {
            $this->warn('MAIL_PASSWORD is empty — add your Resend API key (re_...) in .env');
        }

        try {
            Mail::raw(
                "FinTrack mail test OK.\n\nIf you received this, SMTP settings in .env are correct.\n\n— ".config('app.name'),
                function ($message) use ($email) {
                    $message->to($email)->subject('FinTrack — test email');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Send failed: '.$e->getMessage());
            $this->line('See laravel_app/EMAIL_SETUP.md (Resend SMTP).');

            return self::FAILURE;
        }

        if ($mailer === 'log') {
            $this->info('Message written to storage/logs/laravel.log');
        } else {
            $this->info("Test email sent to {$email}. Check inbox and spam.");
        }

        return self::SUCCESS;
    }
}
