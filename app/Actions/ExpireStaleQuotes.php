<?php

namespace App\Actions;

use App\Enums\QuoteStatus;
use App\Models\Quote;

/**
 * Marks open quotes (pending / under review) older than the configured age
 * as expired. Single implementation shared by the `nexora:expire-quotes`
 * command (servers, Docker) and the Vercel Cron endpoint (serverless).
 */
final class ExpireStaleQuotes
{
    /**
     * @return int Number of quotes expired.
     */
    public function __invoke(): int
    {
        $days = (int) config('nexora.quote_expiry_days');

        return Quote::query()
            ->whereIn('status', [QuoteStatus::Pending->value, QuoteStatus::UnderReview->value])
            ->where('created_at', '<', now()->subDays($days))
            ->update(['status' => QuoteStatus::Expired->value, 'updated_at' => now()]);
    }
}
