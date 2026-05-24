<?php

namespace App\Support;

class MailDelivery
{
    /**
     * Whether the app is set up to deliver mail to a real inbox (not log/array only).
     */
    public static function sendsToInbox(): bool
    {
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if ($mailer === 'smtp') {
            $host = (string) config('mail.mailers.smtp.host');
            $user = config('mail.mailers.smtp.username');
            $pass = config('mail.mailers.smtp.password');

            // Mailpit / local catcher — no credentials required
            if (in_array($host, ['127.0.0.1', 'localhost'], true) && (int) config('mail.mailers.smtp.port') === 1025) {
                return true;
            }

            return filled($user) && filled($pass);
        }

        return true;
    }

    public static function driverLabel(): string
    {
        return (string) config('mail.default');
    }

    public static function statusMessage(): string
    {
        if (self::sendsToInbox()) {
            return 'SMTP is configured. Anomaly alerts can be sent to your inbox.';
        }

        if (config('mail.default') === 'log') {
            return 'Mail is in log mode — messages are written to storage/logs, not sent to an inbox. Add SMTP settings in .env (see EMAIL_SETUP.md).';
        }

        return 'Complete MAIL_* settings in .env to send real emails.';
    }
}
