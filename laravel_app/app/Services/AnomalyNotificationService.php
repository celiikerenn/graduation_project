<?php

namespace App\Services;

use App\Mail\SpendingAnomalyMail;
use App\Models\User;
use App\Support\Currency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AnomalyNotificationService
{
    public function __construct(
        protected FastApiService $api
    ) {}

    /**
     * @return array<string, mixed>|null  API payload, or null on failure
     */
    public function fetchAnomalyStatus(int $userId): ?array
    {
        try {
            return $this->api->checkAnomalies($userId, false);
        } catch (\Throwable $e) {
            Log::warning('Anomaly check failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Check anomalies for a user and send email if needed.
     *
     * @return bool true if an email was sent
     */
    public function notifyIfNeeded(int $userId, string $email, string $name, bool $force = false): bool
    {
        $result = $this->fetchAnomalyStatus($userId);
        if ($result === null) {
            return false;
        }

        if (! $force && empty($result['should_notify'])) {
            return false;
        }

        return $this->sendAnomalyEmail($userId, $email, $name, $result, markNotified: ! $force);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function sendAnomalyEmail(int $userId, string $email, string $name, array $result, bool $markNotified = true): bool
    {
        $local = User::where('email', $email)->first();
        $currencySymbol = Currency::symbol($local?->currency);

        try {
            Mail::to($email)->send(new SpendingAnomalyMail(
                userName: $name,
                month: (string) ($result['month'] ?? ''),
                monthTotal: (float) ($result['current_month_total'] ?? 0),
                baselineAverage: (float) ($result['baseline_average'] ?? 0),
                increasePercent: (float) ($result['increase_percent'] ?? 0),
                currencySymbol: $currencySymbol,
            ));

            if ($markNotified) {
                $this->api->checkAnomalies($userId, true);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Anomaly email failed', [
                'user_id' => $userId,
                'email'   => $email,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }
}
